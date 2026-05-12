<?php

namespace Tests\Feature;

use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Part;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\SosRequest;
use App\Models\MaterialRequest;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffCoreFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff']);
        Role::firstOrCreate(['name' => 'admin', 'slug' => 'admin']);
        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
    }

    public function test_staff_intake_reuses_customer_and_vehicle_and_creates_order_with_initial_tasks(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0901234567',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-12345',
            'model' => 'Camry',
            'type' => 'sedan',
            'year' => 2023,
            'color' => 'Black',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.vehicle.store'), [
            'license_plate' => '51A 12345',
            'model' => 'Camry 2.5Q',
            'type' => 'sedan',
            'owner_name' => $customer->name,
            'owner_phone' => '0901234567',
            'inspection_options' => [
                'general' => true,
                'use_3d' => true,
                'cabin' => true,
                'engine' => false,
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, User::where('phone', '0901234567')->count());
        $this->assertSame(1, Vehicle::where('user_id', $customer->id)->count());

        $order = RepairOrder::latest()->first();
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame($vehicle->id, $order->vehicle_id);
        $this->assertSame($staff->id, $order->advisor_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame(2, $order->tasks()->count());
        $this->assertTrue($order->tasks()->where('type', 'vhc')->exists());
    }

    public function test_staff_cannot_complete_order_until_non_rejected_tasks_are_completed(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '30A-99999',
            'model' => 'Civic',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'White',
            'owner_name' => 'Guest',
            'owner_phone' => '0909999999',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-TEST-001',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
        ]);
        $pendingTask = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Oil change',
            'status' => 'pending',
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.update-status', $order->id), ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $pendingTask->update(['status' => 'completed']);

        $this->actingAs($staff)
            ->postJson(route('staff.order.update-status', $order->id), ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('repair_orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_staff_cannot_start_work_while_quote_is_waiting_for_customer(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '30A-88888',
            'model' => 'Accent',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'White',
            'owner_name' => 'Guest',
            'owner_phone' => '0908888888',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-TEST-003',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.update-status', $order->id), ['status' => 'in_progress'])
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('repair_orders', [
            'id' => $order->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_staff_vhc_save_syncs_report_defects_and_child_tasks_before_quote(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '29A-22222',
            'model' => 'Ranger',
            'type' => 'pickup',
            'year' => 2024,
            'color' => 'Blue',
            'owner_name' => 'Guest',
            'owner_phone' => '0912222222',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-TEST-002',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
        ]);

        $payload = [
            'status' => 'draft',
            'defects' => [
                ['part' => 'Front bumper', 'severity' => 'medium', 'description' => 'Scratch', 'pos' => ['x' => 1, 'y' => 2, 'z' => 3]],
                ['part' => 'Left door', 'severity' => 'critical', 'description' => 'Dent', 'pos' => ['x' => 4, 'y' => 5, 'z' => 6]],
            ],
        ];

        $this->actingAs($staff)
            ->postJson(route('staff.vhc.save', ['id' => $vehicle->id, 'order_id' => $order->id]), $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $report = $order->fresh()->vhcReport;
        $this->assertNotNull($report);
        $this->assertSame('draft', $report->status);
        $this->assertSame(2, $report->defects()->count());

        $parentTask = $order->tasks()->where('type', 'vhc')->first();
        $this->assertNotNull($parentTask);
        $this->assertSame(2, $parentTask->children()->where('type', 'defect')->count());

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'STAFF_VHC_SAVED',
            'details' => "Order #{$order->id}: Lưu VHC draft với 2 defect.",
        ]);
    }

    public function test_staff_activity_log_decodes_legacy_vietnamese_text(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '29A-33333',
            'model' => 'Civic',
            'type' => 'sedan',
            'year' => 2023,
            'color' => 'Black',
            'owner_name' => 'Guest',
            'owner_phone' => '0913333333',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-TEST-003',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Cọp-Xe',
            'status' => 'pending',
            'type' => 'general',
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.task.toggle', $task->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $log = ActivityLog::where('action', 'STAFF_TASK_TOGGLED')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame("Order #{$order->id}: Chuyển task Cọp-Xe sang completed.", $log->details);
        $this->assertStringNotContainsString('Chuyá', $log->details);
        $this->assertStringNotContainsString('Æ', $log->details);
    }

    public function test_staff_payment_can_apply_coupon_code(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-12345',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0909123456',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-PAY-001',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Thay nhớt',
            'status' => 'completed',
            'customer_approval_status' => 'approved',
            'labor_cost' => 100000,
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'name' => 'Nhớt máy',
            'quantity' => 1,
            'unit_price' => 400000,
            'subtotal' => 400000,
        ]);
        $promotion = Promotion::create([
            'code' => 'TEST50',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.pay', $order->id), [
                'payment_method' => 'cash',
                'coupon_code' => 'test50',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('discount_amount', 50000)
            ->assertJsonPath('total_amount', 450000);

        $this->assertDatabaseHas('repair_orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'promotion_id' => $promotion->id,
            'discount_amount' => 50000,
            'total_amount' => 450000,
        ]);
        $this->assertSame(1, $promotion->fresh()->used_count);
    }

    public function test_staff_payment_invalid_coupon_message_is_utf8(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-54321',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0909543210',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-PAY-INVALID',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Thay nhớt',
            'status' => 'completed',
            'customer_approval_status' => 'approved',
            'labor_cost' => 100000,
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'name' => 'Nhớt máy',
            'quantity' => 1,
            'unit_price' => 400000,
            'subtotal' => 400000,
        ]);
        Promotion::create([
            'code' => 'OFF50',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => false,
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.pay', $order->id), [
                'payment_method' => 'cash',
                'coupon_code' => 'OFF50',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Mã giảm giá đã hết hạn hoặc không khả dụng.');

        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_staff_payment_preview_returns_discount_and_payable_amount(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-24680',
            'model' => 'City',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'White',
            'owner_name' => 'Guest',
            'owner_phone' => '0909246800',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-PAY-PREVIEW',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Thay lọc gió',
            'status' => 'completed',
            'customer_approval_status' => 'approved',
            'labor_cost' => 100000,
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'name' => 'Lọc gió',
            'quantity' => 1,
            'unit_price' => 400000,
            'subtotal' => 400000,
        ]);
        Promotion::create([
            'code' => 'PREVIEW50',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->getJson(route('staff.order.payment-preview', [
                'id' => $order->id,
                'coupon_code' => 'preview50',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('base_amount', 500000)
            ->assertJsonPath('discount_amount', 50000)
            ->assertJsonPath('total_amount', 450000)
            ->assertJsonPath('promotion_code', 'PREVIEW50');

        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_staff_payment_preview_uses_final_order_total_when_coupon_is_entered(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-19000',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'White',
            'owner_name' => 'Guest',
            'owner_phone' => '0909190000',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-PAY-FINAL',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'subtotal' => 190000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 190000,
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Công việc đã cộng vào hóa đơn',
            'status' => 'completed',
            'customer_approval_status' => 'approved',
            'labor_cost' => 150000,
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'name' => 'Phụ tùng',
            'quantity' => 1,
            'unit_price' => 190000,
            'subtotal' => 190000,
        ]);
        Promotion::create([
            'code' => 'NEWYEAR',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->getJson(route('staff.order.payment-preview', [
                'id' => $order->id,
                'coupon_code' => 'NEWYEAR',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('base_amount', 190000)
            ->assertJsonPath('discount_amount', 50000)
            ->assertJsonPath('total_amount', 140000)
            ->assertJsonPath('promotion_code', 'NEWYEAR');
    }

    public function test_staff_can_handover_paid_completed_order_once(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-77777',
            'model' => 'Altis',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'White',
            'owner_name' => 'Guest',
            'owner_phone' => '0909777777',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-HANDOVER-001',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.handover', $order->id))
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $order->update(['payment_status' => 'paid']);

        $this->actingAs($staff)
            ->postJson(route('staff.order.handover', $order->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($order->fresh()->delivered_at);

        $this->actingAs($staff)
            ->postJson(route('staff.order.handover', $order->id))
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_staff_can_convert_appointment_without_vehicle_into_repair_order(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0907000001',
        ]);
        $service = Service::create([
            'code' => 'SV001',
            'name' => 'Kiểm tra tổng quát',
            'category' => 'diagnosis',
            'base_price' => 250000,
            'estimated_duration' => 60,
        ]);
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_name' => 'Toyota Vios',
            'license_plate' => '51G-12345',
            'service_id' => $service->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'confirmed',
            'reason' => 'Kiểm tra xe',
        ]);

        $this->actingAs($staff)
            ->post(route('staff.appointments.convert', $appointment))
            ->assertRedirect();

        $order = RepairOrder::latest()->first();
        $this->assertNotNull($order);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertNotNull($order->vehicle_id);
        $this->assertNotEmpty($order->track_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame('completed', $appointment->fresh()->status);
        $this->assertDatabaseHas('repair_order_items', [
            'repair_order_id' => $order->id,
            'name' => 'Kiểm tra tổng quát',
            'subtotal' => 250000,
        ]);
    }

    public function test_staff_can_update_appointment_response_without_completing_it_manually(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
        ]);
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_name' => 'Toyota Vios',
            'license_plate' => '51G-12345',
            'scheduled_at' => now()->addDay(),
            'status' => 'pending',
            'reason' => 'Kiểm tra xe',
        ]);

        $this->actingAs($staff)
            ->put(route('staff.appointments.update', $appointment), [
                'status' => 'confirmed',
                'admin_notes' => 'Garage đã xác nhận lịch hẹn.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
            'admin_notes' => 'Garage đã xác nhận lịch hẹn.',
        ]);

        $this->actingAs($staff)
            ->put(route('staff.appointments.update', $appointment), [
                'status' => 'completed',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_staff_cannot_convert_no_show_appointment_to_repair_order(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
        ]);
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_name' => 'Toyota Vios',
            'license_plate' => '51G-12345',
            'scheduled_at' => now()->addDay(),
            'status' => 'no_show',
        ]);

        $this->actingAs($staff)
            ->post(route('staff.appointments.convert', $appointment))
            ->assertSessionHasErrors('error');

        $this->assertSame(0, RepairOrder::count());
    }

    public function test_staff_appointments_index_renders_with_services_schema(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        Service::create([
            'code' => 'SV002',
            'name' => 'Bảo dưỡng định kỳ',
            'category' => 'maintenance',
            'base_price' => 500000,
            'estimated_duration' => 90,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.appointments.index'))
            ->assertOk()
            ->assertSee('Bảo dưỡng định kỳ');
    }

    public function test_staff_can_create_customer_without_email(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);

        $this->actingAs($staff)
            ->post(route('staff.customers.store'), [
                'name' => 'Khách Test',
                'phone' => '0907000004',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Khách Test',
            'phone' => '0907000004',
            'email' => null,
            'role' => 'customer',
        ]);
    }

    public function test_staff_cannot_delete_vehicle_with_repair_history(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0907000002',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51G-22222',
            'model' => 'City',
            'type' => 'sedan',
            'year' => 2020,
            'color' => 'White',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        RepairOrder::create([
            'track_id' => 'RO-HISTORY-001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'completed',
        ]);

        $this->actingAs($staff)
            ->delete(route('staff.vehicles.destroy', $vehicle->id))
            ->assertRedirect(route('staff.customers.show', $customer->id));

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_staff_customer_update_flash_message_is_utf8(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'name' => 'Khách cũ',
            'phone' => '0907000003',
        ]);

        $this->actingAs($staff)
            ->followingRedirects()
            ->put(route('staff.customers.update', $customer->id), [
                'name' => 'Nguyễn Văn Khách',
                'phone' => '0907000004',
                'email' => 'khach@example.com',
                'address' => 'Quận 1',
            ])
            ->assertOk()
            ->assertSee('Th\u00f4ng tin kh\u00e1ch h\u00e0ng \u0111\u00e3 \u0111\u01b0\u1ee3c c\u1eadp nh\u1eadt.', false)
            ->assertDontSee('ThÃ´ng tin', false);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nguyễn Văn Khách',
            'phone' => '0907000004',
        ]);
    }

    public function test_staff_cannot_add_order_item_after_quote_sent(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '51G-33333',
            'model' => 'Morning',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0907000003',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-QUOTE-LOCK',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
        ]);
        Part::create([
            'sku' => 'P001',
            'name' => 'Lọc dầu',
            'category' => 'Engine',
            'purchase_price' => 50000,
            'selling_price' => 100000,
            'stock_quantity' => 10,
            'min_stock' => 2,
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.items.store', $order->id), [
                'sku' => 'P001',
                'qty' => 1,
            ])
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_staff_part_search_returns_price_for_legacy_modals(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        Part::create([
            'sku' => 'P-SEARCH-001',
            'name' => 'Lọc gió động cơ',
            'category' => 'Engine',
            'purchase_price' => 80000,
            'selling_price' => 150000,
            'stock_quantity' => 5,
            'min_stock' => 1,
        ]);

        $this->actingAs($staff)
            ->getJson(route('staff.inventory.search', ['q' => 'lọc gió']))
            ->assertOk()
            ->assertJsonPath('0.sku', 'P-SEARCH-001')
            ->assertJsonPath('0.price', 150000)
            ->assertJsonPath('0.selling_price', 150000);
    }

    public function test_staff_can_create_material_request_without_reason(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);

        $this->actingAs($staff)
            ->post(route('staff.requests.store'), [
                'part_name' => 'Cảm biến áp suất lốp',
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('material_requests', [
            'staff_id' => $staff->id,
            'part_name' => 'Cảm biến áp suất lốp',
            'quantity' => 2,
            'reason' => null,
            'status' => 'pending',
        ]);
    }

    public function test_admin_must_price_material_request_before_approving_into_repair_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => Role::where('slug', 'admin')->value('id')]);
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-REQ01',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0909000001',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-MAT-001',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'total_amount' => 0,
        ]);
        $materialRequest = MaterialRequest::create([
            'staff_id' => $staff->id,
            'repair_order_id' => $order->id,
            'part_name' => 'Cảm biến áp suất lốp',
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.requests.update', $materialRequest), [
                'status' => 'approved',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Vui lòng nhập giá bán trước khi duyệt vật tư vào phiếu sửa chữa');

        $this->assertSame('pending', $materialRequest->fresh()->status);
        $this->assertSame(0, $order->items()->count());
    }

    public function test_admin_approval_adds_priced_material_to_repair_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => Role::where('slug', 'admin')->value('id')]);
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-REQ02',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0909000002',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-MAT-002',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'total_amount' => 0,
        ]);
        $materialRequest = MaterialRequest::create([
            'staff_id' => $staff->id,
            'repair_order_id' => $order->id,
            'part_name' => 'Cảm biến áp suất lốp',
            'quantity' => 2,
            'reason' => 'Kho chưa có',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.requests.update', $materialRequest), [
                'status' => 'approved',
                'unit_price' => 190000,
                'admin_note' => 'Đã duyệt mua ngoài',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Đã cập nhật yêu cầu vật tư');

        $this->assertDatabaseHas('material_requests', [
            'id' => $materialRequest->id,
            'status' => 'approved',
            'unit_price' => 190000,
            'admin_note' => 'Đã duyệt mua ngoài',
        ]);
        $this->assertDatabaseHas('repair_order_items', [
            'repair_order_id' => $order->id,
            'name' => 'Vật tư ngoài: Cảm biến áp suất lốp',
            'quantity' => 2,
            'unit_price' => 190000,
            'subtotal' => 380000,
        ]);
        $this->assertSame(380000.0, (float) $order->fresh()->total_amount);
    }

    public function test_admin_can_route_approved_material_request_into_inventory(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => Role::where('slug', 'admin')->value('id')]);
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $materialRequest = MaterialRequest::create([
            'staff_id' => $staff->id,
            'part_name' => 'Bóng đèn pha LED',
            'quantity' => 3,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.requests.update', $materialRequest), [
                'status' => 'approved',
                'destination' => 'inventory',
                'sku' => 'LED-HEAD-001',
                'category' => 'Đèn',
                'unit_price' => 180000,
                'admin_note' => 'Nhập kho để dùng sau',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Đã cập nhật yêu cầu vật tư');

        $part = Part::where('sku', 'LED-HEAD-001')->first();
        $this->assertNotNull($part);
        $this->assertSame(3, $part->stock_quantity);
        $this->assertSame(180000.0, (float) $part->selling_price);
        $this->assertDatabaseHas('inventory_transactions', [
            'part_id' => $part->id,
            'type' => 'in',
            'quantity' => 3,
            'reference' => 'MR-'.$materialRequest->id,
        ]);
    }

    public function test_staff_adds_inventory_part_to_order_and_stock_is_exported(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $vehicle = Vehicle::create([
            'license_plate' => '59A-STOCK',
            'model' => 'Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Silver',
            'owner_name' => 'Guest',
            'owner_phone' => '0909000003',
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-STOCK-001',
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
            'payment_status' => 'unpaid',
        ]);
        $part = Part::create([
            'sku' => 'OIL-001',
            'name' => 'Nhớt máy',
            'category' => 'Dầu nhớt',
            'purchase_price' => 80000,
            'selling_price' => 150000,
            'stock_quantity' => 5,
            'min_stock' => 1,
            'safety_stock' => 2,
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.order.items.store', $order->id), [
                'is_custom' => 'false',
                'sku' => 'OIL-001',
                'qty' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(3, $part->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'part_id' => $part->id,
            'type' => 'out',
            'quantity' => 2,
            'reference' => 'RO-'.$order->id,
        ]);
        $this->assertDatabaseHas('repair_order_items', [
            'repair_order_id' => $order->id,
            'itemable_type' => Part::class,
            'itemable_id' => $part->id,
            'quantity' => 2,
            'subtotal' => 300000,
        ]);
    }

    public function test_staff_cannot_change_completed_sos_status(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('slug', 'customer')->value('id')]);
        $sos = SosRequest::create([
            'customer_id' => $customer->id,
            'latitude' => 10.762622,
            'longitude' => 106.660172,
            'description' => 'Xe không nổ máy',
            'status' => 'completed',
            'assigned_staff_id' => $staff->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.sos.status', $sos->id), ['status' => 'cancelled'])
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertSame('completed', $sos->fresh()->status);
    }

    public function test_staff_can_cancel_sos_with_reason_and_admin_log(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('slug', 'customer')->value('id')]);
        $sos = SosRequest::create([
            'customer_id' => $customer->id,
            'latitude' => 10.762622,
            'longitude' => 106.660172,
            'description' => 'Xe chết máy',
            'status' => 'assigned',
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->postJson(route('staff.sos.cancel', $sos->id), [
                'cancel_reason' => 'unable_to_contact',
                'cancel_note' => 'Gọi nhiều lần nhưng khách không nghe máy.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('sos_requests', [
            'id' => $sos->id,
            'status' => 'cancelled',
            'cancel_reason' => 'unable_to_contact',
            'cancel_note' => 'Gọi nhiều lần nhưng khách không nghe máy.',
            'cancelled_by' => $staff->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staff->id,
            'action' => 'STAFF_SOS_CANCELLED',
        ]);
    }

    public function test_staff_location_list_only_returns_recent_staff_with_coordinates(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $otherStaff = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
            'is_sharing_location' => true,
            'latitude' => 10.762622,
            'longitude' => 106.660172,
            'last_location_update' => now(),
        ]);
        User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'is_sharing_location' => true,
            'latitude' => 10.77,
            'longitude' => 106.67,
            'last_location_update' => now(),
        ]);

        $this->actingAs($staff)
            ->getJson(route('staff.sos.location.staff-members'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $otherStaff->id);
    }

    public function test_staff_sos_pending_alert_only_returns_pending_requests(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('slug', 'customer')->value('id')]);

        $pending = SosRequest::create([
            'customer_id' => $customer->id,
            'latitude' => 10.762622,
            'longitude' => 106.660172,
            'description' => 'Xe dừng giữa đường',
            'status' => 'pending',
        ]);

        SosRequest::create([
            'customer_id' => $customer->id,
            'latitude' => 10.77,
            'longitude' => 106.67,
            'description' => 'Đã có người nhận',
            'status' => 'assigned',
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->getJson(route('staff.sos.pending-alert'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.id', $pending->id)
            ->assertJsonPath('items.0.display_name', $customer->name)
            ->assertJsonPath('items.0.url', route('staff.sos.show', $pending->id));
    }
}
