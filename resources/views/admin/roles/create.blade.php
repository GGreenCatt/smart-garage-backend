@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.roles.index') }}" class="hover:text-indigo-400 transition">Roles</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">Create New Role</span>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="glass-panel p-8 rounded-2xl border border-slate-700/50">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Basic Info -->
            <div class="md:col-span-1 space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Role Name</label>
                    <input type="text" name="name" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="e.g. Senior Technician">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="Describe the responsibilities..."></textarea>
                </div>
            </div>

            <!-- Right: Permissions Matrix -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Permissions Assignment</label>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dashboard & Analytics -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-chart-pie text-indigo-400 mr-2"></i>Hệ Thống & Báo Cáo</h4>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_dashboard" {{ in_array('view_dashboard', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem Dashboard</span>
                                </label>
                                 <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_settings" {{ in_array('manage_settings', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Cài đặt Hệ thống</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_reports" {{ in_array('view_reports', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem Báo cáo & Log</span>
                                </label>
                            </div>
                        </div>

                        <!-- Staff & Internal -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-users-cog text-indigo-400 mr-2"></i>Nhân Sự & Nội Bộ</h4>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_staff" {{ in_array('view_staff', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem danh sách nhân viên</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_staff" {{ in_array('manage_staff', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý nhân viên</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="access_chat" {{ in_array('access_chat', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Sử dụng Chat nội bộ</span>
                                </label>
                            </div>
                        </div>

                        <!-- Customers & Vehicles -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-car text-indigo-400 mr-2"></i>Khách Hàng & Xe</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_customers" {{ in_array('manage_customers', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý KH</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_own_vehicles" {{ in_array('view_own_vehicles', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem xe cá nhân</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_vehicles" {{ in_array('manage_vehicles', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Xe</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="delete_vehicles" {{ in_array('delete_vehicles', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xóa Xe</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_3d" {{ in_array('view_3d', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem mô hình 3D</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="edit_3d" {{ in_array('edit_3d', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Đánh dấu lỗi 3D</span>
                                </label>
                            </div>
                        </div>

                        <!-- Repair Orders -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-tools text-indigo-400 mr-2"></i>Lệnh Sửa Chữa</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="create_repair_orders" {{ in_array('create_repair_orders', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Tạo Phiếu SC</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_repair_orders" {{ in_array('view_repair_orders', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem Phiếu SC</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_repair_orders" {{ in_array('manage_repair_orders', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý tổng (RO)</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="approve_repair_orders" {{ in_array('approve_repair_orders', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Duyệt Phiếu SC</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="update_repair_progress" {{ in_array('update_repair_progress', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Cập nhật tiến độ</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_assigned_tasks" {{ in_array('view_assigned_tasks', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem việc được giao</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_sos" {{ in_array('manage_sos', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Điều phối Cứu Hộ</span>
                                </label>
                            </div>
                        </div>

                        <!-- Inventory & Suppliers -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-box-open text-indigo-400 mr-2"></i>Kho & Vật Tư</h4>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_inventory" {{ in_array('view_inventory', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem Kho</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_inventory" {{ in_array('manage_inventory', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Phụ tùng/Nhập kho</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_suppliers" {{ in_array('manage_suppliers', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Nhà cung cấp</span>
                                </label>
                            </div>
                        </div>

                        <!-- Services & Others -->
                        <div class="bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                            <h4 class="text-white font-bold mb-3 border-b border-slate-700 pb-2"><i class="fas fa-concierge-bell text-indigo-400 mr-2"></i>Dịch Vụ & Khác</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="view_services" {{ in_array('view_services', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Xem Dịch vụ</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_services" {{ in_array('manage_services', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Cài đặt Dịch vụ</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_appointments" {{ in_array('manage_appointments', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Đặt Lịch</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="manage_finance" {{ in_array('manage_finance', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Quản lý Tài chính</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group text-amber-300">
                                    <input type="checkbox" name="permissions[]" value="manage_promotions" {{ in_array('manage_promotions', old('permissions', [])) ? 'checked' : '' }} class="rounded border-slate-600 text-amber-500 focus:ring-amber-500 bg-slate-700">
                                    <span class="text-sm text-slate-300 group-hover:text-white">Cấu hình Khuyến Mãi</span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('admin.roles.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition transform active:scale-95">
                        Create Role
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
