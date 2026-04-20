<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles with specific IDs to match strict Controller logic
        // Admin = 1
        Role::unguard();
        Role::updateOrCreate(['id' => 1], ['slug' => 'admin', 'name' => 'Admin']);
        Role::updateOrCreate(['id' => 2], ['slug' => 'manager', 'name' => 'Manager']);
        Role::updateOrCreate(['id' => 3], ['slug' => 'staff', 'name' => 'Staff']);
        Role::updateOrCreate(['id' => 5], ['slug' => 'technician', 'name' => 'Technician']);
        Role::updateOrCreate(['id' => 6], ['slug' => 'customer', 'name' => 'Customer']);
        Role::reguard();
    }

    public function test_admin_redirects_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'role_id' => 1,
        ]);

        $response = $this->actingAs($admin)
                         ->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_redirects_to_staff_dashboard()
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'role_id' => 3,
        ]);

        $response = $this->actingAs($staff)
                         ->get('/dashboard');

        $response->assertRedirect(route('staff.dashboard'));
    }
    
    public function test_technician_redirects_to_staff_dashboard()
    {
        $tech = User::factory()->create([
            'role' => 'technician',
            'role_id' => 5,
        ]);

        $response = $this->actingAs($tech)
                         ->get('/dashboard');

        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_customer_redirects_to_customer_dashboard()
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => 6,
        ]);

        $response = $this->actingAs($customer)
                         ->get('/dashboard');

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_authenticated_user_accessing_home_redirects_to_dashboard_logic()
    {
        // Admin -> Home -> Redirect Admin Dashboard
        $admin = User::find(1) ?? User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        $response = $this->actingAs($admin)->get('/');
        $response->assertRedirect(route('dashboard')); // First jump
        
        // This confirms the CustomerController::index logic works.
        // The subsequent jump from /dashboard to /admin is covered by other tests.
    }
}
