<?php

namespace Tests\Feature;

use App\Models\RepairOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VhcDefect;
use App\Models\VhcReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRepairOrderVhcTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::unguard();
        Role::updateOrCreate(['id' => 1], ['slug' => 'admin', 'name' => 'Admin', 'permissions' => ['*']]);
        Role::updateOrCreate(['id' => 6], ['slug' => 'customer', 'name' => 'Customer']);
        Role::reguard();
    }

    public function test_admin_repair_order_3d_button_keeps_order_context(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $order = $this->createRepairOrder();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair_orders.show', $order));

        $response->assertOk();
        $response->assertSee(
            route('admin.vehicles.3d', ['vehicle' => $order->vehicle_id, 'order_id' => $order->id]),
            false
        );
    }

    public function test_admin_vhc_endpoint_returns_defects_for_requested_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $order = $this->createRepairOrder();
        $otherOrder = $this->createRepairOrder($order->vehicle);

        $targetReport = VhcReport::create([
            'repair_order_id' => $order->id,
            'status' => 'published',
        ]);
        VhcDefect::create([
            'vhc_report_id' => $targetReport->id,
            'title' => 'Target dent',
            'description' => 'Visible on this repair order',
            'type' => 'exterior',
            'severity' => 'high',
            'pos_x' => 1.25,
            'pos_y' => 2.5,
            'pos_z' => -0.5,
        ]);

        $otherReport = VhcReport::create([
            'repair_order_id' => $otherOrder->id,
            'status' => 'published',
        ]);
        VhcDefect::create([
            'vhc_report_id' => $otherReport->id,
            'title' => 'Other order scratch',
            'description' => 'Should not be returned',
            'type' => 'exterior',
            'severity' => 'low',
            'pos_x' => 9,
            'pos_y' => 9,
            'pos_z' => 9,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.vehicles.vhc.data', [
                'vehicle' => $order->vehicle_id,
                'order_id' => $order->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('status', 'published');
        $response->assertJsonPath('defects.0.title', 'Target dent');
        $response->assertJsonMissing(['title' => 'Other order scratch']);
    }

    private function createRepairOrder(?Vehicle $vehicle = null): RepairOrder
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => 6,
            'phone' => fake()->unique()->numerify('09########'),
        ]);

        $vehicle ??= Vehicle::create([
            'license_plate' => fake()->unique()->bothify('??-#####'),
            'make' => 'Toyota',
            'model' => 'Camry',
            'type' => 'sedan',
            'user_id' => $customer->id,
            'owner_phone' => $customer->phone,
            'owner_name' => $customer->name,
        ]);

        return RepairOrder::create([
            'track_id' => fake()->unique()->bothify('RO-########'),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => 1,
            'status' => RepairOrder::STATUS_COMPLETED,
            'quote_status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);
    }
}
