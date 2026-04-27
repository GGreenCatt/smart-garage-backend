<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\RepairOrder;
use App\Models\RepairTask;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff']);
        Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);
    }

    public function test_customer_general_chat_is_visible_and_replyable_by_staff(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);

        $this->actingAs($customer)
            ->post(route('chat.send'), ['message' => 'Tôi cần hỗ trợ chung'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $session = ChatSession::where('customer_id', $customer->id)->first();
        $this->assertNotNull($session);
        $this->assertNull($session->repair_order_id);

        $this->actingAs($staff)
            ->getJson(route('staff.chat.sessions'))
            ->assertOk()
            ->assertJsonPath('sessions.0.id', $session->id);

        $this->actingAs($staff)
            ->post(route('staff.chat.reply'), [
                'chat_session_id' => $session->id,
                'message' => 'Garage đã nhận thông tin.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender_id' => $staff->id,
            'is_staff' => true,
            'message' => 'Garage đã nhận thông tin.',
        ]);
    }

    public function test_customer_message_uses_existing_open_job_chat_session(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909000003',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-11111',
            'model' => 'Toyota Vios',
            'type' => 'sedan',
            'year' => 2020,
            'color' => 'White',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-CHAT-001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $staff->id,
            'status' => 'in_progress',
        ]);
        $session = ChatSession::create([
            'repair_order_id' => $order->id,
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $this->actingAs($customer)
            ->post(route('chat.send'), ['message' => 'Xe của tôi sửa tới đâu rồi?'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, ChatSession::count());
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender_id' => $customer->id,
            'is_staff' => false,
            'message' => 'Xe của tôi sửa tới đâu rồi?',
        ]);
    }

    public function test_staff_can_only_reply_to_job_chat_when_advisor_or_assigned_mechanic(): void
    {
        $advisor = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $assignedStaff = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $otherStaff = User::factory()->create([
            'role' => 'staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => Role::where('slug', 'customer')->value('id'),
            'phone' => '0909000004',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => '51A-22222',
            'model' => 'Honda City',
            'type' => 'sedan',
            'year' => 2021,
            'color' => 'Black',
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
        $order = RepairOrder::create([
            'track_id' => 'RO-CHAT-002',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => $advisor->id,
            'status' => 'in_progress',
        ]);
        RepairTask::create([
            'repair_order_id' => $order->id,
            'mechanic_id' => $assignedStaff->id,
            'title' => 'Kiểm tra động cơ',
            'status' => 'in_progress',
        ]);
        $session = ChatSession::create([
            'repair_order_id' => $order->id,
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $this->actingAs($advisor)
            ->post(route('staff.chat.reply'), [
                'chat_session_id' => $session->id,
                'message' => 'Cố vấn đã nhận.',
            ])
            ->assertOk();

        $this->actingAs($assignedStaff)
            ->post(route('staff.chat.reply'), [
                'chat_session_id' => $session->id,
                'message' => 'Kỹ thuật viên đang kiểm tra.',
            ])
            ->assertOk();

        $this->actingAs($otherStaff)
            ->post(route('staff.chat.reply'), [
                'chat_session_id' => $session->id,
                'message' => 'Không được phép.',
            ])
            ->assertForbidden();

        $this->assertSame(2, ChatMessage::where('chat_session_id', $session->id)->where('is_staff', true)->count());
    }
}
