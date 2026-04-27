<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
    }

    public function test_customer_dashboard_renders_core_sections(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909555001',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-55555',
            'model' => 'Toyota Vios',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'White',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-CUS-DASH',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'quote_sent_at' => now(),
            'service_type' => 'Kiểm tra tổng quát',
        ]);
        $parentTask = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Kiểm tra tổng quát',
            'status' => 'pending',
        ]);
        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'parent_id' => $parentTask->id,
            'title' => 'Thay lọc dầu',
            'status' => 'pending',
            'labor_cost' => 100000,
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'name' => 'Lọc dầu',
            'quantity' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
        ]);
        $service = Service::create([
            'code' => 'SVC-DASH',
            'name' => 'Bảo dưỡng định kỳ',
            'category' => 'maintenance',
            'base_price' => 500000,
            'estimated_duration' => 60,
        ]);
        Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Cần bạn duyệt báo giá')
            ->assertSee('Toyota Vios')
            ->assertSee('51A-55555')
            ->assertSee('Lịch hẹn sắp tới')
            ->assertSee(route('customer.quote.show', $order->id), false);
    }

    public function test_customer_cannot_view_other_customers_vehicle_3d(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909555002',
        ]);
        $other = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909555003',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $other->id,
            'license_plate' => '51A-77777',
            'model' => 'Mazda 3',
            'type' => 'sedan',
            'year' => 2020,
            'color' => 'Red',
            'owner_name' => $other->name,
            'owner_phone' => $other->phone,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.vehicle.3d', $vehicle->id))
            ->assertNotFound();
    }

    public function test_customer_order_detail_guides_counter_payment_without_coupon_form(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909555004',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-88888',
            'model' => 'Honda City',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Black',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-CUS-COUPON',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'subtotal' => 500000,
            'total_amount' => 500000,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.orders.show', $order->id))
            ->assertOk()
            ->assertSee('Thanh toán tại quầy')
            ->assertSee('Nhân viên sẽ áp mã')
            ->assertDontSee('orders.coupon');
    }
}
