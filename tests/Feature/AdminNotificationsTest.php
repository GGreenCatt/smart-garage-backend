<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::unguard();
        Role::updateOrCreate(['id' => 1], ['slug' => 'admin', 'name' => 'Admin']);
        Role::reguard();
    }

    public function test_admin_can_view_notifications_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        Notification::create([
            'type' => 'App\\Notifications\\RepairOrderReady',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => [
                'title' => 'Phiếu sửa đã hoàn thành',
                'message' => 'Đơn RO-001 đã sẵn sàng thanh toán.',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));

        $response->assertOk();
        $response->assertSee('Thông Báo Admin');
        $response->assertSee('Phiếu sửa đã hoàn thành');
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);
        Notification::create([
            'type' => 'App\\Notifications\\SystemNotice',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => [
                'title' => 'Thông báo hệ thống',
                'message' => 'Bảo trì vào cuối tuần.',
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.notifications.readAll'))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
        ]);
    }
}
