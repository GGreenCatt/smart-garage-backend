<?php

namespace Tests\Feature;

use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Appointment;
use App\Models\Part;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\SosRequest;
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
}
