@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Doanh Thu Tháng</p>
                    <h3 class="text-2xl font-heading font-bold text-white mt-1">{{ number_format($stats['monthly_revenue'] / 1000000, 1) }}M <span class="text-sm text-slate-500">VND</span></h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded font-bold">+12.5%</span>
                <span class="text-slate-500">so với tháng trước</span>
            </div>
        </div>

        <!-- Orders -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
             <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition"></div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Đơn Sửa Chữa</p>
                    <h3 class="text-2xl font-heading font-bold text-white mt-1">{{ $stats['active_orders'] }} <span class="text-sm text-slate-500">Active</span></h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center"><i class="fas fa-tools"></i></div>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-bold">5 Chờ duyệt</span>
                <span class="text-slate-500">cần xử lý ngay</span>
            </div>
        </div>

        <!-- Customers -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
             <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition"></div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Khách Hàng Mới</p>
                    <h3 class="text-2xl font-heading font-bold text-white mt-1">+{{ $stats['total_customers'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center"><i class="fas fa-users"></i></div>
            </div>
             <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400">Tổng cộng: 1,240 khách hàng</span>
            </div>
        </div>
        
        <!-- Vehicles -->
         <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
             <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition"></div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Xe Đã Tiếp Nhận</p>
                    <h3 class="text-2xl font-heading font-bold text-white mt-1">{{ $stats['total_vehicles'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center"><i class="fas fa-car"></i></div>
            </div>
             <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400">Hiệu suất xưởng: 85%</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl border border-slate-800">
            <h3 class="text-lg font-bold text-white mb-4">Doanh Thu 7 Ngày Qua</h3>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Order Status -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-800">
            <h3 class="text-lg font-bold text-white mb-4">Trạng Thái Đơn Hàng</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Table (Placeholder for Audit Log) -->
    <div class="glass-panel rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Hoạt Động Gần Đây</h3>
            <button class="text-xs font-bold text-indigo-400 hover:text-indigo-300">Xem tất cả</button>
        </div>
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-500">
                <tr>
                    <th class="px-6 py-4">Nhân Viên</th>
                    <th class="px-6 py-4">Hành Động</th>
                    <th class="px-6 py-4">Thời Gian</th>
                    <th class="px-6 py-4">Trạng Thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-6 py-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white">K</div>
                        <span class="text-white font-medium">Khoa Kỹ Thuật</span>
                    </td>
                    <td class="px-6 py-4">Đã cập nhật trạng thái đơn <span class="text-indigo-400 font-mono">#LSC-123</span></td>
                    <td class="px-6 py-4">2 phút trước</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 rounded text-[10px] font-bold bg-green-500/10 text-green-400">Success</span></td>
                </tr>
                 <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-6 py-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white">T</div>
                        <span class="text-white font-medium">Thu Ngân 01</span>
                    </td>
                    <td class="px-6 py-4">Đã tạo báo giá cho xe <span class="text-indigo-400 font-mono">30A-999.99</span></td>
                    <td class="px-6 py-4">15 phút trước</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 rounded text-[10px] font-bold bg-blue-500/10 text-blue-400">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@push('scripts')
<script>
    // Config Defaults
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = '#1e293b';

    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            datasets: [{
                label: 'Doanh Thu (Triệu VND)',
                data: [12, 19, 15, 25, 22, 30, 28],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Status Chart
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Đang Sửa', 'Chờ Duyệt', 'Hoàn Thành'],
            datasets: [{
                data: [{{ $stats['active_orders'] }}, 5, 12],
                backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
@endsection
