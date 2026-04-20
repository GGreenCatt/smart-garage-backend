@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('admin.roles.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Edit Role: {{ $role->name }}</h2>
            <p class="text-sm text-slate-400">Modify permissions for this role</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Info -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-id-card text-indigo-400"></i> Role Details
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Role Name</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" rows="1" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 transition">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Permissions Matrix -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-shield-alt text-teal-400"></i> Access & Permissions
                </h3>
                <span class="text-xs text-slate-500 bg-slate-800 px-3 py-1 rounded-full">Select capabilities</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dashboard & Analytics -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-chart-pie text-indigo-400 mr-2"></i>Hệ Thống & Báo Cáo</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_dashboard" {{ in_array('view_dashboard', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem Dashboard</span>
                        </label>
                         <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_settings" {{ in_array('manage_settings', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Cài đặt Hệ thống</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_reports" {{ in_array('view_reports', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem Báo cáo & Log</span>
                        </label>
                    </div>
                </div>

                <!-- Staff & Internal -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-users-cog text-indigo-400 mr-2"></i>Nhân Sự & Nội Bộ</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_staff" {{ in_array('view_staff', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem danh sách nhân viên</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_staff" {{ in_array('manage_staff', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý nhân viên</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="access_chat" {{ in_array('access_chat', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Sử dụng Chat nội bộ</span>
                        </label>
                    </div>
                </div>

                <!-- Customers & Vehicles -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-car text-indigo-400 mr-2"></i>Khách Hàng & Xe</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_customers" {{ in_array('manage_customers', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý KH</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_own_vehicles" {{ in_array('view_own_vehicles', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem xe cá nhân</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_vehicles" {{ in_array('manage_vehicles', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Xe</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="delete_vehicles" {{ in_array('delete_vehicles', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xóa Xe</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_3d" {{ in_array('view_3d', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem mô hình 3D</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="edit_3d" {{ in_array('edit_3d', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Đánh dấu lỗi 3D</span>
                        </label>
                    </div>
                </div>

                <!-- Repair Orders -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-tools text-indigo-400 mr-2"></i>Phiếu Sửa Chữa (RO)</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="create_repair_orders" {{ in_array('create_repair_orders', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Tạo Phiếu SC</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_repair_orders" {{ in_array('view_repair_orders', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem Phiếu SC</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_repair_orders" {{ in_array('manage_repair_orders', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý tổng (RO)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="approve_repair_orders" {{ in_array('approve_repair_orders', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Duyệt Phiếu SC</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="update_repair_progress" {{ in_array('update_repair_progress', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Cập nhật tiến độ</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_assigned_tasks" {{ in_array('view_assigned_tasks', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem việc được giao</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_sos" {{ in_array('manage_sos', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Điều phối Cứu Hộ</span>
                        </label>
                    </div>
                </div>

                <!-- Inventory & Suppliers -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-box-open text-indigo-400 mr-2"></i>Kho & Vật Tư</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_inventory" {{ in_array('view_inventory', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem Kho</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_inventory" {{ in_array('manage_inventory', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Phụ tùng/Nhập kho</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_suppliers" {{ in_array('manage_suppliers', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Nhà cung cấp</span>
                        </label>
                    </div>
                </div>

                <!-- Services & Others -->
                <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                    <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-concierge-bell text-indigo-400 mr-2"></i>Dịch Vụ & Khác</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="view_services" {{ in_array('view_services', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Xem Dịch vụ</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_services" {{ in_array('manage_services', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Cài đặt Dịch vụ</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_appointments" {{ in_array('manage_appointments', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Đặt Lịch</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="manage_finance" {{ in_array('manage_finance', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Tài chính</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group text-amber-300">
                            <input type="checkbox" name="permissions[]" value="manage_promotions" {{ in_array('manage_promotions', $role->permissions ?? []) ? 'checked' : '' }} class="rounded border-slate-600 text-amber-500 focus:ring-amber-500 bg-slate-700">
                            <span class="text-sm text-slate-300 group-hover:text-white">Cấu hình Khuyến Mãi</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <button type="button" id="selectAllBtn" class="text-sm font-bold text-indigo-400 hover:text-indigo-300 transition">
                <i class="fas fa-check-double mr-1"></i> Select All
            </button>
            <div class="flex gap-4">
                <a href="{{ route('admin.roles.index') }}" class="px-6 py-3 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 font-bold transition">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition shadow-lg shadow-indigo-500/20">
                    Update Role
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        
        checkboxes.forEach(c => c.checked = !allChecked);
        this.innerHTML = allChecked ? '<i class="fas fa-check-double mr-1"></i> Select All' : '<i class="fas fa-times mr-1"></i> Deselect All';
    });
</script>
@endsection
