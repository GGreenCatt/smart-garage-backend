<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
    }

    public function test_customer_can_book_appointment_with_saved_vehicle(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909000001',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-12345',
            'model' => 'Toyota Vios',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'White',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        $service = Service::create([
            'code' => 'SVC-BOOK-001',
            'name' => 'Bảo dưỡng định kỳ',
            'category' => 'maintenance',
            'base_price' => 500000,
            'estimated_duration' => 60,
        ]);

        $this->actingAs($customer)
            ->post(route('customer.appointments.store'), [
                'vehicle_choice' => 'existing',
                'vehicle_id' => $vehicle->id,
                'service_id' => $service->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'reason' => 'Bảo dưỡng xe',
            ])
            ->assertRedirect(route('customer.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'reason' => 'Bảo dưỡng xe',
        ]);
    }

    public function test_customer_can_book_appointment_with_manual_vehicle(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
        ]);

        $this->actingAs($customer)
            ->post(route('customer.appointments.store'), [
                'vehicle_choice' => 'new',
                'vehicle_name' => 'Honda City',
                'license_plate' => '51g-67890',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'reason' => 'Xe có tiếng kêu lạ',
            ])
            ->assertRedirect(route('customer.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'vehicle_id' => null,
            'vehicle_name' => 'Honda City',
            'license_plate' => '51G-67890',
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_book_with_another_customers_vehicle(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
        ]);
        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909000002',
        ]);
        $otherVehicle = Vehicle::create([
            'user_id' => $otherCustomer->id,
            'license_plate' => '51A-99999',
            'model' => 'Mazda 3',
            'type' => 'sedan',
            'year' => 2022,
            'color' => 'Red',
            'owner_name' => $otherCustomer->name,
            'owner_phone' => $otherCustomer->phone,
        ]);

        $this->actingAs($customer)
            ->from(route('customer.appointments.create'))
            ->post(route('customer.appointments.store'), [
                'vehicle_choice' => 'existing',
                'vehicle_id' => $otherVehicle->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('customer.appointments.create'))
            ->assertSessionHasErrors('vehicle_id');

        $this->assertSame(0, Appointment::count());
    }
}
