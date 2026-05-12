<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Vehicle;
use App\Models\VhcReport;

class CustomerQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'admin', 'slug' => 'admin']);
        Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff']);
        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
        Role::firstOrCreate(['name' => 'technician', 'slug' => 'technician']);
    }

    public function test_customer_can_view_quote()
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1234567890']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id, 
            'owner_phone' => $customer->phone, 
            'license_plate' => '29A-12345',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2020,
            'color' => 'Black',
            'type' => 'sedan'
        ]);
        
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'track_id' => 'TRK-' . uniqid()
        ]);

        $response = $this->actingAs($customer)->get(route('customer.order.quote.show', $repairOrder->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.quote');
        $response->assertViewHas('order', fn ($order) => $order->id === $repairOrder->id);
    }

    public function test_customer_cannot_view_others_quote()
    {
        $customer1 = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id]);
        $customer2 = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '0987654321']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer2->id, 
            'owner_phone' => $customer2->phone, 
            'license_plate' => '30A-98765',
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'color' => 'White',
            'type' => 'sedan'
        ]);
        
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer2->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'track_id' => 'TRK-' . uniqid()
        ]);

        $response = $this->actingAs($customer1)->getJson(route('customer.order.quote.show', $repairOrder->id));

        $response->assertStatus(403);
    }

    public function test_customer_can_approve_and_reject_tasks()
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1122334455']);
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('name', 'staff')->first()->id]);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id, 
            'owner_phone' => $customer->phone, 
            'license_plate' => '31A-54321',
            'make' => 'Ford',
            'model' => 'Ranger',
            'year' => 2022,
            'color' => 'Blue',
            'type' => 'truck'
        ]);
        
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'advisor_id' => $staff->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'track_id' => 'TRK-' . uniqid()
        ]);

        $parentTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Inspection',
        ]);

        $task1 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Replace Brake Pads',
            'labor_cost' => 300000,
            'customer_approval_status' => 'pending'
        ]);
        
        $task2 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Oil Change',
            'labor_cost' => 200000,
            'customer_approval_status' => 'pending'
        ]);

        $payload = [
            'tasks' => [
                ['id' => $task1->id, 'status' => 'approved'],
                ['id' => $task2->id, 'status' => 'rejected'],
            ]
        ];

        $response = $this->actingAs($customer)->postJson(route('customer.order.quote.tasks', $repairOrder->id), $payload);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('repair_tasks', [
            'id' => $task1->id,
            'customer_approval_status' => 'approved'
        ]);
        
        $this->assertDatabaseHas('repair_tasks', [
            'id' => $task2->id,
            'customer_approval_status' => 'rejected'
        ]);

        $this->assertDatabaseHas('repair_orders', [
            'id' => $repairOrder->id,
            'status' => 'approved', // Overall status becomes approved if at least one task is approved and all are answered
            'quote_status' => 'approved',
            'total_amount' => 300000,
        ]);
        
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'type' => 'quote_reviewed'
        ]);
    }

    public function test_customer_rejecting_all_tasks_marks_quote_rejected_and_order_cancelled()
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1133557799']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'license_plate' => '32A-11111',
            'make' => 'Mazda',
            'model' => 'CX5',
            'year' => 2023,
            'color' => 'Red',
            'type' => 'suv',
        ]);

        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'track_id' => 'TRK-' . uniqid(),
        ]);

        $parentTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Kiá»ƒm tra tá»•ng quÃ¡t',
        ]);

        $task = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Thay ma phanh',
            'labor_cost' => 250000,
            'customer_approval_status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->postJson(route('customer.order.quote.tasks', $repairOrder->id), [
            'tasks' => [
                ['id' => $task->id, 'status' => 'rejected'],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('repair_orders', [
            'id' => $repairOrder->id,
            'status' => 'cancelled',
            'quote_status' => 'rejected',
            'total_amount' => 0,
        ]);
    }

    public function test_customer_must_review_all_quote_tasks(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1133557700']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'license_plate' => '32A-22222',
            'make' => 'Mazda',
            'model' => 'CX5',
            'year' => 2023,
            'color' => 'Red',
            'type' => 'suv',
        ]);
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'track_id' => 'TRK-' . uniqid(),
        ]);
        $parentTask = RepairTask::factory()->create(['repair_order_id' => $repairOrder->id, 'title' => 'Inspection']);
        $task1 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Task 1',
            'labor_cost' => 100000,
            'customer_approval_status' => 'pending',
        ]);
        $task2 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Task 2',
            'labor_cost' => 200000,
            'customer_approval_status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->postJson(route('customer.order.quote.tasks', $repairOrder->id), [
                'tasks' => [
                    ['id' => $task1->id, 'status' => 'approved'],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Vui lòng phản hồi đầy đủ tất cả hạng mục báo giá.');

        $this->assertSame('pending_approval', $repairOrder->fresh()->status);
        $this->assertSame('pending', $task2->fresh()->customer_approval_status);
    }

    public function test_customer_cannot_review_task_outside_quote(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1133557711']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'license_plate' => '32A-33333',
            'make' => 'Mazda',
            'model' => 'CX5',
            'year' => 2023,
            'color' => 'Red',
            'type' => 'suv',
        ]);
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'track_id' => 'TRK-' . uniqid(),
        ]);
        $parentTask = RepairTask::factory()->create(['repair_order_id' => $repairOrder->id, 'title' => 'Inspection']);
        $quoteTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Quoted task',
            'labor_cost' => 100000,
            'customer_approval_status' => 'pending',
        ]);
        $otherTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Non quoted task',
            'labor_cost' => 0,
            'customer_approval_status' => null,
        ]);

        $this->actingAs($customer)
            ->postJson(route('customer.order.quote.tasks', $repairOrder->id), [
                'tasks' => [
                    ['id' => $quoteTask->id, 'status' => 'approved'],
                    ['id' => $otherTask->id, 'status' => 'approved'],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Có hạng mục không thuộc phiếu báo giá này.');
    }

    public function test_customer_approval_total_only_counts_approved_items(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '1133557722']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'license_plate' => '32A-44444',
            'make' => 'Mazda',
            'model' => 'CX5',
            'year' => 2023,
            'color' => 'Red',
            'type' => 'suv',
        ]);
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'total_amount' => 600000,
            'track_id' => 'TRK-' . uniqid(),
        ]);
        $parentTask = RepairTask::factory()->create(['repair_order_id' => $repairOrder->id, 'title' => 'Inspection']);
        $approvedTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Approved task',
            'labor_cost' => 100000,
            'customer_approval_status' => 'pending',
        ]);
        RepairOrderItem::create([
            'repair_order_id' => $repairOrder->id,
            'repair_task_id' => $approvedTask->id,
            'name' => 'Oil',
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);
        $rejectedTask = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTask->id,
            'title' => 'Rejected task',
            'labor_cost' => 450000,
            'customer_approval_status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->postJson(route('customer.order.quote.tasks', $repairOrder->id), [
                'tasks' => [
                    ['id' => $approvedTask->id, 'status' => 'approved'],
                    ['id' => $rejectedTask->id, 'status' => 'rejected'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('repair_orders', [
            'id' => $repairOrder->id,
            'status' => 'approved',
            'quote_status' => 'approved',
            'total_amount' => 150000,
        ]);
    }

    public function test_legacy_customer_quote_actions_are_removed()
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('customer.order.approve'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('customer.order.reject'));
    }

    public function test_signed_guest_3d_link_works_for_staff_quote_view(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('name', 'staff')->first()->id]);
        $customer = User::factory()->create(['role' => 'customer', 'role_id' => Role::where('name', 'customer')->first()->id, 'phone' => '0909000001']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'license_plate' => '17B-123.45',
            'make' => 'Honda',
            'model' => 'City',
            'year' => 2022,
            'color' => 'White',
            'type' => 'sedan',
        ]);
        $repairOrder = RepairOrder::factory()->create([
            'customer_id' => $customer->id,
            'advisor_id' => $staff->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'include_vhc' => true,
            'track_id' => 'TRK-' . uniqid(),
        ]);
        $report = VhcReport::create([
            'repair_order_id' => $repairOrder->id,
            'status' => 'published',
        ]);
        $report->defects()->create([
            'title' => 'Front bumper',
            'description' => 'Scratch',
            'type' => 'general',
            'severity' => 'medium',
            'pos_x' => 1,
            'pos_y' => 1,
            'pos_z' => 1,
        ]);

        $threeDUrl = \Illuminate\Support\Facades\URL::signedRoute('guest.vehicle.3d', [
            'id' => $vehicle->id,
            'readonly' => 1,
            'order_id' => $repairOrder->id,
        ]);
        $inspectionUrl = \Illuminate\Support\Facades\URL::signedRoute('guest.vehicle.inspection.data', [
            'id' => $vehicle->id,
            'order_id' => $repairOrder->id,
        ]);

        $this->actingAs($staff)
            ->get($threeDUrl)
            ->assertOk()
            ->assertViewIs('customer.vehicle.3d_view');

        $this->actingAs($staff)
            ->getJson($inspectionUrl)
            ->assertOk()
            ->assertJsonCount(1, 'defects')
            ->assertJsonPath('defects.0.title', 'Front bumper');
    }
}
