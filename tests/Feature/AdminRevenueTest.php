<?php

namespace Tests\Feature;

use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::unguard();
        Role::updateOrCreate(['id' => 1], ['slug' => 'admin', 'name' => 'Admin']);
        Role::updateOrCreate(['id' => 6], ['slug' => 'customer', 'name' => 'Customer']);
        Role::reguard();
    }

    public function test_admin_can_view_revenue_page_with_paid_repair_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $order = $this->createPaidRepairOrder();

        $response = $this->actingAs($admin)
            ->withSession(['admin_view_mode' => 'manager'])
            ->get(route('admin.revenue.index'));

        $response->assertOk();
        $response->assertSee('Doanh thu theo đợt sửa');
        $response->assertSee($order->track_id);
        $response->assertSee('12.500.000đ');
    }

    public function test_admin_can_open_revenue_detail_for_repair_order_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $order = $this->createPaidRepairOrder();

        $response = $this->actingAs($admin)
            ->get(route('admin.revenue.show', $order));

        $response->assertOk();
        $response->assertSee('Chi tiết các khoản');
        $response->assertSee('Thay dầu động cơ');
        $response->assertSee('Công kiểm tra gầm');
    }

    private function createPaidRepairOrder(): RepairOrder
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => 6,
            'phone' => '0900000001',
        ]);
        $advisor = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $vehicle = Vehicle::create([
            'license_plate' => '30A-12345',
            'make' => 'Toyota',
            'model' => 'Camry',
            'type' => 'sedan',
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'owner_name' => $customer->name,
        ]);

        $order = RepairOrder::create([
            'track_id' => 'RO-20260512-001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $advisor->id,
            'status' => RepairOrder::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'subtotal' => 13000000,
            'discount_amount' => 500000,
            'tax_amount' => 0,
            'total_amount' => 12500000,
        ]);

        $task = RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Công kiểm tra gầm',
            'status' => 'completed',
            'customer_approval_status' => 'approved',
            'labor_cost' => 500000,
        ]);

        RepairTask::create([
            'repair_order_id' => $order->id,
            'title' => 'Rejected labor',
            'status' => 'pending',
            'customer_approval_status' => 'rejected',
            'labor_cost' => 99000000,
        ]);

        RepairOrderItem::create([
            'repair_order_id' => $order->id,
            'repair_task_id' => $task->id,
            'itemable_type' => \App\Models\Service::class,
            'itemable_id' => 1,
            'name' => 'Thay dầu động cơ',
            'quantity' => 1,
            'unit_price' => 12000000,
            'cost_price' => 0,
            'subtotal' => 12000000,
        ]);

        return $order;
    }
}
