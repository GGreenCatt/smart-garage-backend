@extends('layouts.admin')

@section('title', 'Quản Lý Chức Vụ')

@section('content')
<!-- Custom Tailwind Config for this page (if needed, though layout usually handles it) -->
<style>
    /* Google Fonts from Template */
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');
    
    .font-display {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    .glass {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .avatar-stack img {
        border: 2px solid transparent;
        transition: transform 0.2s;
    }
    .avatar-stack img:hover {
        transform: translateY(-2px);
        z-index: 10;
    }
    .dark .avatar-stack img {
        border-color: #0F172A;
    }
</style>

<script>
    function togglePermissions(roleId, btn) {
        const container = document.getElementById('perm-container-' + roleId);
        if (!container) return;
        
        // Show all hidden items
        const hiddenItems = container.querySelectorAll('.perm-item.hidden');
        hiddenItems.forEach(item => {
            item.classList.remove('hidden');
            item.classList.add('animate-fade-in'); // Optional animation class if defined, or just appear
        });

        // Hide the button itself
        btn.style.display = 'none';
    }
</script>

<div class="max-w-7xl mx-auto py-6">
    <header class="mb-12">
        <div class="p-6 rounded-2xl border border-slate-800 bg-card-dark/50 glass">
            <h2 class="text-xl font-display font-bold mb-1 text-white">Phân quyền & Chức vụ</h2>
            <p class="text-sm text-slate-400">Quản lý các cấp độ truy cập hệ thống thông qua các vai trò được định nghĩa trước.</p>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-24">
        @foreach($roles as $role)
            @php
                $isSystem = in_array($role->slug, ['admin', 'manager', 'technician', 'staff', 'customer']);
                $icon = match($role->slug) {
                    'admin' => 'shield',
                    'manager' => 'manage_accounts',
                    'technician' => 'handyman',
                    'staff' => 'badge',
                    'customer' => 'person',
                    default => 'lock'
                };
                $iconColor = match($role->slug) {
                    'admin' => 'text-indigo-500 bg-indigo-500/10',
                    'manager' => 'text-blue-500 bg-blue-500/10',
                    'technician' => 'text-emerald-500 bg-emerald-500/10',
                    'staff' => 'text-orange-500 bg-orange-500/10',
                    'customer' => 'text-slate-500 bg-slate-500/10',
                    default => 'text-pink-500 bg-pink-500/10'
                };

                // DATA TRANSLATION MAP
                $roleNameVN = match($role->slug) {
                    'admin' => 'Quản Trị Viên',
                    'manager' => 'Quản Lý Cửa Hàng',
                    'technician' => 'Kỹ Thuật Viên',
                    'staff' => 'Nhân Viên Kho/Thu Ngân',
                    'customer' => 'Khách Hàng',
                    default => $role->name
                };

                $roleDescVN = match($role->slug) {
                    'admin' => 'Quyền truy cập toàn bộ hệ thống, báo cáo và cài đặt.',
                    'manager' => 'Quản lý nhân viên, tồn kho và duyệt các lệnh sửa chữa phức tạp.',
                    'technician' => 'Thực hiện các công việc sửa chữa và cập nhật nhật ký dịch vụ.',
                    'staff' => 'Quyền truy cập cơ bản để bán hàng và xem thông tin.',
                    'customer' => 'Tài khoản khách hàng để đặt lịch và xem lịch sử xe.',
                    default => $role->description ?? 'Chưa có mô tả.'
                };

                $permMap = [
                    'view_dashboard' => 'Xem Thống Kê',
                    'manage_staff' => 'QL Nhân Sự',
                    'manage_inventory' => 'QL Kho',
                    'manage_vehicles' => 'QL Xe & Dịch Vụ',
                    'manage_settings' => 'Cài Đặt Hệ Thống',
                    'view_reports' => 'Xem Báo Cáo',
                    'create_appointments' => 'Tạo Lịch Hẹn',
                    'manage_appointments' => 'QL Lịch Hẹn',
                    'manage_repair_orders' => 'QL Lệnh Sửa Chữa',
                    'create_repair_orders' => 'Tạo Lệnh SC',
                    'approve_repair_orders' => 'Duyệt Lệnh SC',
                    'update_repair_progress' => 'Cập Nhật Tiến Độ',
                    'view_assigned_tasks' => 'Xem Việc Được Giao',
                    'manage_services' => 'QL Dịch Vụ',
                    'manage_suppliers' => 'QL Nhà Cung Cấp',
                    'manage_finance' => 'QL Tài Chính',
                    'view_inventory' => 'Xem Tồn Kho',
                    'view_services' => 'Xem Dịch Vụ',
                    'view_own_vehicles' => 'Xem Xe Của Tôi',
                    'access_chat' => 'Chat Nội Bộ',
                    'manage_promotions' => 'QL Khuyến Mãi',
                    'manage_sos' => 'QL Cứu Hộ',
                ];
            @endphp
            
            <div class="group relative p-6 rounded-2xl border border-slate-800 bg-card-dark shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-xl {{ explode(' ', $iconColor)[1] }} flex items-center justify-center">
                        <span class="material-icons-round {{ explode(' ', $iconColor)[0] }}">{{ $icon }}</span>
                    </div>
                    @if($isSystem)
                        <span class="material-icons-round text-slate-600 text-lg" title="Vai trò hệ thống">lock</span>
                    @else
                         <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa chức vụ này?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-600 hover:text-red-400"><span class="material-icons-round">delete</span></button>
                        </form>
                    @endif
                </div>

                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-xl font-display font-bold text-white">{{ $roleNameVN }}</h3>
                        <span class="px-2 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-bold bg-slate-800 text-slate-500">{{ $role->slug }}</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed min-h-[40px]">{{ $roleDescVN }}</p>
                </div>

                <div class="mb-8">
                    <h4 class="text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-3">Quyền Hạn</h4>
                    <div class="flex flex-wrap gap-2" id="perm-container-{{ $role->id }}">
                         @if(in_array('*', $role->permissions ?? []))
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 shadow-[0_0_15px_rgba(99,102,241,0.1)]">
                                TOÀN QUYỀN HỆ THỐNG
                            </span>
                        @else
                            @foreach($role->permissions ?? [] as $index => $perm)
                                <span class="px-2 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700 perm-item {{ $index >= 4 ? 'hidden' : '' }}">
                                    {{ $permMap[$perm] ?? str_replace('_', ' ', $perm) }}
                                </span>
                            @endforeach
                            @if(count($role->permissions ?? []) > 4)
                                <button onclick="togglePermissions('{{ $role->id }}', this)" class="px-2 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-slate-500 border border-slate-700 hover:bg-slate-700 hover:text-slate-300 transition-colors cursor-pointer">
                                    +{{ count($role->permissions) - 4 }}
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-800">
                    <div class="flex items-center gap-2">
                        <!-- Avatar Stack Placeholder -->
                        <div class="flex avatar-stack -space-x-2">
                             @for($i=0; $i < min(3, $role->users_count); $i++)
                                <div class="w-7 h-7 rounded-full bg-slate-700 border-2 border-card-dark flex items-center justify-center text-[10px] text-slate-400 font-bold">
                                    {{ substr($roleNameVN, 0, 1) }}
                                </div>
                             @endfor
                        </div>
                        <span class="text-xs font-medium text-slate-500">{{ $role->users_count }} Thành Viên</span>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($role->slug !== 'admin')
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="text-sm font-semibold text-primary hover:underline hover:text-indigo-400">Chỉnh Sửa</a>
                        @else
                            <span class="text-sm font-semibold text-slate-500 cursor-not-allowed" title="Quyền này được hệ thống bảo vệ">Mặc định</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Add New Role Card -->
        <a href="{{ route('admin.roles.create') }}" class="group cursor-pointer p-6 rounded-2xl border-2 border-dashed border-slate-800 flex flex-col items-center justify-center text-center hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all duration-300 min-h-[340px]">
            <div class="w-16 h-16 rounded-full bg-slate-900 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                <span class="material-icons-round text-3xl text-slate-500 group-hover:text-indigo-500">add_circle</span>
            </div>
            <div>
                <h3 class="text-lg font-display font-bold mb-2 text-white">Thêm Chức Vụ Mới</h3>
                <p class="text-sm text-slate-400 px-8">Tạo các bộ quyền riêng biệt cho từng thành viên hoặc nhiệm vụ cụ thể.</p>
            </div>
        </a>
    </div>

    <!-- Floating Stats Bar -->
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full bg-card-dark border border-slate-700 shadow-2xl glass flex items-center gap-8 z-50">
        <div class="flex items-center gap-3">
            <span class="text-slate-400 material-icons-round text-sm">groups</span>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold tracking-tighter text-slate-400 leading-none mb-1">Tổng Số</span>
                <span class="text-sm font-bold leading-none text-white">{{ $roles->count() }} Chức Vụ</span>
            </div>
        </div>
        <div class="h-8 w-px bg-slate-700"></div>
        <div class="flex items-center gap-3">
            <span class="text-slate-400 material-icons-round text-sm">admin_panel_settings</span>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold tracking-tighter text-slate-400 leading-none mb-1">Trạng Thái</span>
                <span class="text-sm font-bold leading-none text-emerald-400">Hoạt Động</span>
            </div>
        </div>
    </div>
</div>
@endsection
