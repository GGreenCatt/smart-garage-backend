<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\MaterialRequest;
use App\Models\Notification;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationObserver
{
    public function created($model)
    {
        // 1. New Appointment Created
        if ($model instanceof Appointment) {
            // Notify Admin
            $this->notifyAdmins([
                'title' => 'Lịch Hẹn Mới',
                'message' => "Khách hàng {$model->customer->name} vừa đặt lịch hẹn mới.",
                'type' => 'appointment_new',
                'url' => route('admin.appointments.index')
            ]);
        }

        // 2. New Material Request
        if ($model instanceof MaterialRequest) {
            // Notify Admin
            $this->notifyAdmins([
                'title' => 'Yêu Cầu Vật Tư Mới',
                'message' => "Nhân viên {$model->staff->name} yêu cầu vật tư: {$model->part_name}",
                'type' => 'material_request_new',
                'url' => route('admin.requests.index')
            ]);
        }
    }

    public function updated($model)
    {
        // 1. Appointment Status Change
        if ($model instanceof Appointment && $model->isDirty('status')) {
            // Notify Customer if status confirmed/completed
            if (in_array($model->status, ['confirmed', 'completed', 'cancelled'])) {
                $statusText = match($model->status) {
                    'confirmed' => 'đã được xác nhận',
                    'completed' => 'đã hoàn thành',
                    'cancelled' => 'đã bị hủy',
                    default => 'cập nhật trạng thái'
                };
                
                $this->notifyUser($model->customer, [
                    'title' => 'Cập Nhật Lịch Hẹn',
                    'message' => "Lịch hẹn dịch vụ của bạn {$statusText}.",
                    'type' => 'appointment_status',
                    'url' => route('customer.appointments.index')
                ]);
            }
        }

        // 2. Material Request Status Change
        if ($model instanceof MaterialRequest && $model->isDirty('status')) {
            $statusText = match($model->status) {
                'approved' => 'đã được duyệt',
                'rejected' => 'đã bị từ chối',
                default => 'đang chờ'
            };

            $this->notifyUser($model->staff, [
                'title' => 'Kết Quả Yêu Cầu Vật Tư',
                'message' => "Yêu cầu '{$model->part_name}' của bạn {$statusText}.",
                'type' => 'material_request_status',
                'url' => route('staff.requests.index')
            ]);
        }
    }

    protected function notifyAdmins($data)
    {
        $admins = User::whereIn('role', ['admin', 'manager'])->get();
        foreach ($admins as $admin) {
            $this->createNotification($admin, $data);
        }
    }

    protected function notifyUser($user, $data)
    {
        if ($user) {
            $this->createNotification($user, $data);
        }
    }

    protected function createNotification($user, $data)
    {
        Notification::create([
            'id' => Str::uuid(),
            'type' => $data['type'],
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => null
        ]);
    }
}
