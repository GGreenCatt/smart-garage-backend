<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\RepairOrder;
use App\Models\RepairTask;
use App\Models\Vehicle;

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

        $response = $this->actingAs($customer)->getJson(route('customer.order.quote.show', $repairOrder->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'repair_order' => [
                'id',
                'customer_id',
                'status',
                'vehicle',
                'advisor',
            ]
        ]);
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
            'track_id' => 'TRK-' . uniqid()
        ]);

        $task1 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Replace Brake Pads',
            'customer_approval_status' => 'pending'
        ]);
        
        $task2 = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Oil Change',
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
            'status' => 'approved' // Overall status becomes approved if at least one task is approved and all are answered
        ]);
        
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'type' => 'quote_reviewed'
        ]);
    }
}
