<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::unguard();
        Role::updateOrCreate(['id' => 1], ['slug' => 'admin', 'name' => 'Admin', 'permissions' => ['*']]);
        Role::reguard();
    }

    public function test_admin_created_promotion_with_remaining_time_is_usable(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);

        $response = $this->actingAs($admin)->post(route('admin.promotions.store'), [
            'code' => 'GARAGE10',
            'type' => 'percent',
            'value' => 10,
            'description' => 'Discount for testing',
            'start_date' => now()->subHour()->format('Y-m-d\TH:i'),
            'end_date' => now()->addDay()->format('Y-m-d\TH:i'),
            'usage_limit' => 5,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.promotions.index'));

        $promotion = Promotion::where('code', 'GARAGE10')->firstOrFail();

        $this->assertTrue($promotion->isValid());
        $this->assertSame('active', $promotion->statusReason());
        $this->assertSame(0, $promotion->used_count);
    }

    public function test_admin_promotion_index_shows_scheduled_instead_of_expired_when_start_date_is_future(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'role_id' => 1]);

        Promotion::create([
            'code' => 'FUTURE10',
            'type' => 'percent',
            'value' => 10,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'usage_limit' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.promotions.index'));

        $response->assertOk();
        $response->assertSee('FUTURE10');
        $response->assertSee('Chưa đến ngày bắt đầu');
        $response->assertDontSee('Hết hạn / hết lượt');
    }
}
