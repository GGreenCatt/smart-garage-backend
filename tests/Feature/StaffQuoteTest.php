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

class StaffQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial roles if needed or just mock it.
        Role::firstOrCreate(['name' => 'admin', 'slug' => 'admin']);
        Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff']);
        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
        Role::firstOrCreate(['name' => 'technician', 'slug' => 'technician']);
    }

    public function test_staff_can_send_quote()
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('name', 'staff')->first()->id]);
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
            'status' => 'pending',
            'track_id' => 'TRK-' . uniqid()
        ]);
        
        // This makes sure tasks() isn't empty, avoiding the 400 error in sendQuote
        $task = RepairTask::factory()->create([
            'repair_order_id' => $repairOrder->id,
            'title' => 'Replace Brake Pads',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.order.send-quote', $repairOrder->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('repair_orders', [
            'id' => $repairOrder->id,
            'status' => 'pending_approval',
            'quote_status' => 'sent',
        ]);

        $this->assertDatabaseHas('repair_tasks', [
            'id' => $task->id,
            'customer_approval_status' => 'pending'
        ]);
        
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'type' => 'quote_ready'
        ]);
    }

    public function test_cannot_send_quote_without_tasks_or_vhc()
    {
        $staff = User::factory()->create(['role' => 'staff', 'role_id' => Role::where('name', 'staff')->first()->id]);
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
            'vehicle_id' => $vehicle->id,
            'status' => 'pending',
            'track_id' => 'TRK-' . uniqid()
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.order.send-quote', $repairOrder->id));

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }
}
