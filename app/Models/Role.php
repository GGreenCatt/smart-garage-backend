<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function permissionGroups(): array
    {
        return [
            'Hệ thống & báo cáo' => [
                'view_dashboard' => 'Xem bảng điều khiển',
                'view_reports' => 'Xem báo cáo và nhật ký',
                'manage_settings' => 'Cài đặt hệ thống',
                'manage_roles' => 'Quản lý phân quyền và chức vụ',
            ],
            'Nhân sự & trao đổi' => [
                'view_staff' => 'Xem danh sách nhân viên',
                'manage_staff' => 'Quản lý nhân viên',
                'access_chat' => 'Sử dụng chat nội bộ',
                'customer_support_chat' => 'Hỗ trợ chat khách hàng',
            ],
            'Khách hàng & xe' => [
                'manage_customers' => 'Quản lý khách hàng',
                'view_own_vehicles' => 'Xem xe cá nhân',
                'manage_vehicles' => 'Quản lý xe',
                'delete_vehicles' => 'Xóa xe',
                'view_3d' => 'Xem mô hình 3D/VHC',
                'edit_3d' => 'Đánh dấu lỗi 3D/VHC',
            ],
            'Lệnh sửa chữa' => [
                'create_repair_orders' => 'Tạo lệnh sửa chữa',
                'view_repair_orders' => 'Xem lệnh sửa chữa',
                'manage_repair_orders' => 'Quản lý lệnh sửa chữa',
                'approve_repair_orders' => 'Duyệt lệnh sửa chữa',
                'update_repair_progress' => 'Cập nhật tiến độ sửa chữa',
                'view_assigned_tasks' => 'Xem công việc được giao',
            ],
            'Lịch hẹn & cứu hộ' => [
                'manage_appointments' => 'Quản lý lịch hẹn',
                'manage_sos' => 'Điều phối cứu hộ SOS',
            ],
            'Kho & vật tư' => [
                'view_inventory' => 'Xem kho',
                'manage_inventory' => 'Quản lý kho và vật tư',
                'view_requests' => 'Xem yêu cầu vật tư',
                'manage_suppliers' => 'Quản lý nhà cung cấp',
            ],
            'Dịch vụ, tài chính & khuyến mãi' => [
                'view_services' => 'Xem dịch vụ',
                'manage_services' => 'Quản lý dịch vụ',
                'manage_finance' => 'Quản lý tài chính và thanh toán',
                'manage_promotions' => 'Quản lý khuyến mãi',
            ],
        ];
    }

    public static function permissions(): array
    {
        return collect(self::permissionGroups())
            ->flatMap(fn ($permissions) => array_keys($permissions))
            ->values()
            ->all();
    }

    public static function permissionLabels(): array
    {
        return collect(self::permissionGroups())
            ->flatMap(fn ($permissions) => $permissions)
            ->all();
    }
}
