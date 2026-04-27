@extends('layouts.admin')

@section('title', 'Tổng Quan')

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . 'đ';
    $statusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra',
        'pending_approval' => 'Chờ khách duyệt',
        'approved' => 'Khách đã duyệt',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $statusColors = ['#f59e0b', '#3b82f6', '#a855f7', '#22c55e', '#64748b', '#ef4444'];
    $statusData = collect(array_keys($statusLabels))->map(fn ($status) => (int) ($statusCounts[$status] ?? 0))->values();
@endphp

<div class="space-y-8">
    @if(auth()->user()->isAdmin() && session('admin_view_mode') != 'manager')
        <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-8 text-center shadow-xl shadow-slate-950/20">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-300">
                <i class="fas fa-shield-alt text-3xl"></i>
            </div>
            <h2 class="text-2xl font-black text-white">Xin chào, quản trị viên hệ thống</h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-400">Bạn đang ở chế độ cấu hình hệ thống. Có thể chuyển sang chế độ quản lý vận hành để xem dữ liệu garage theo thời gian thực.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('admin.settings.index') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white transition hover:bg-indigo-500">Cấu hình hệ thống</a>
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-3 text-sm font-black text-slate-100 transition hover:bg-slate-700">Phân quyền & chức vụ</a>
                <form action="{{ route('admin.toggle-view-mode') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-3 text-sm font-black text-emerald-300 transition hover:bg-emerald-500/20">
                        Xem tổng quan vận hành
                    </button>
                </form>
            </div>
        </section>
    @endif

    @if(auth()->user()->isManager() || (auth()->user()->isAdmin() && session('admin_view_mode') == 'manager'))
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Doanh thu tháng</p>
                        <h3 class="mt-2 text-2xl font-black text-white">{{ $money($stats['monthly_revenue']) }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="mt-4 text-xs font-bold">
                    @if($stats['revenue_change_percent'] === null)
                        <span class="text-slate-500">Chưa có dữ liệu tháng trước</span>
                    @else
                        <span class="{{ $stats['revenue_change_percent'] >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ $stats['revenue_change_percent'] >= 0 ? '+' : '' }}{{ $stats['revenue_change_percent'] }}%
                        </span>
                        <span class="ml-1 text-slate-500">so với tháng trước</span>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Đơn đang xử lý</p>
                        <h3 class="mt-2 text-2xl font-black text-white">{{ $stats['active_orders'] }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-blue-300"><i class="fas fa-tools"></i></div>
                </div>
                <div class="mt-4 text-xs font-bold text-slate-500">
                    <span class="text-amber-300">{{ $stats['pending_approval_orders'] }}</span> chờ khách duyệt
                    <span class="mx-1">•</span>
                    <span class="text-emerald-300">{{ $stats['completed_unpaid_orders'] }}</span> chờ thanh toán
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Khách hàng</p>
                        <h3 class="mt-2 text-2xl font-black text-white">{{ $stats['total_customers'] }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-500/10 text-violet-300"><i class="fas fa-users"></i></div>
                </div>
                <div class="mt-4 text-xs font-bold text-slate-500">
                    <span class="text-violet-300">+{{ $stats['new_customers_this_month'] }}</span> khách mới trong tháng
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Lịch hẹn hôm nay</p>
                        <h3 class="mt-2 text-2xl font-black text-white">{{ $stats['appointments_today'] }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300"><i class="fas fa-calendar-check"></i></div>
                </div>
                <div class="mt-4 text-xs font-bold text-slate-500">
                    <span class="text-cyan-300">{{ $stats['pending_appointments'] }}</span> lịch chờ xác nhận
                    <span class="mx-1">•</span>
                    <span>{{ $stats['total_vehicles'] }} xe đã lưu</span>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-black text-white">Doanh thu 7 ngày gần nhất</h3>
                    <span class="text-xs font-bold text-slate-500">Đơn đã thanh toán</span>
                </div>
                <div class="h-72">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
                <div class="mb-5">
                    <h3 class="text-lg font-black text-white">Trạng thái lệnh sửa chữa</h3>
                    <p class="mt-1 text-sm text-slate-500">Tính theo toàn bộ dữ liệu hiện có.</p>
                </div>
                <div class="h-72">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 p-6">
                <div>
                    <h3 class="text-lg font-black text-white">Hoạt động gần đây</h3>
                    <p class="mt-1 text-sm text-slate-500">Lấy từ nhật ký thao tác thực tế của hệ thống.</p>
                </div>
                <a href="{{ route('admin.staff.logs') }}" class="text-sm font-black text-indigo-300 transition hover:text-indigo-200">Xem nhật ký</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-800 bg-slate-950/50 text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Người thao tác</th>
                            <th class="px-6 py-4">Hành động</th>
                            <th class="px-6 py-4">Chi tiết</th>
                            <th class="px-6 py-4">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentActivities as $activity)
                            <tr class="transition hover:bg-slate-800/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-800 text-xs font-black text-white">
                                            {{ mb_substr($activity->user->name ?? 'H', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white">{{ $activity->user->name ?? 'Hệ thống' }}</div>
                                            <div class="text-xs text-slate-500">{{ $activity->ip_address ?? 'Không rõ IP' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-black text-indigo-300">{{ $activity->action }}</span>
                                </td>
                                <td class="max-w-xl px-6 py-4 text-slate-400">{{ $activity->details }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $activity->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">Chưa có hoạt động nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>

@push('scripts')
@if(auth()->user()->isManager() || (auth()->user()->isAdmin() && session('admin_view_mode') == 'manager'))
<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = '#1e293b';

    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($revenueChart->pluck('label')),
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: @json($revenueChart->pluck('value')),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.12)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#818cf8'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: @json(array_values($statusLabels)),
            datasets: [{
                data: @json($statusData),
                backgroundColor: @json($statusColors),
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            cutout: '68%'
        }
    });
</script>
@endif
@endpush
@endsection
