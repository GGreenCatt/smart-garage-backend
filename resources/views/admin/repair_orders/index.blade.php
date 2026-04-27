@extends('layouts.admin')

@section('title', 'Phiếu Sửa Chữa')

@section('content')
@php
    $statusStyles = [
        'pending' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
        'pending_approval' => 'bg-orange-500/10 text-orange-300 border-orange-500/20',
        'approved' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20',
        'in_progress' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
        'completed' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
        'cancelled' => 'bg-red-500/10 text-red-300 border-red-500/20',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tổng phiếu</p>
            <p class="mt-2 text-3xl font-black text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Chờ tiếp nhận</p>
            <p class="mt-2 text-3xl font-black text-amber-300">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Chờ khách duyệt</p>
            <p class="mt-2 text-3xl font-black text-orange-300">{{ number_format($stats['pending_approval']) }}</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Đang xử lý</p>
            <p class="mt-2 text-3xl font-black text-blue-300">{{ number_format($stats['in_progress']) }}</p>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-white">
                <span class="material-icons-round text-indigo-400">car_repair</span>
                Phiếu Sửa Chữa
            </h2>
            <p class="mt-1 text-sm text-slate-400">Theo dõi tiếp nhận, báo giá, thi công và hoàn tất xe trong xưởng.</p>
        </div>
        <a href="{{ route('admin.repair_orders.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
            <span class="material-icons-round text-lg">add_circle</span>
            Tiếp nhận xe
        </a>
    </div>

    <form action="{{ route('admin.repair_orders.index') }}" method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_240px_auto_auto]">
            <div class="relative">
                <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                <input name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-700 bg-slate-900/70 py-3 pl-10 pr-4 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none" placeholder="Tìm mã phiếu, khách hàng, SĐT hoặc biển số">
            </div>
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                <option value="all">Tất cả trạng thái</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">Lọc</button>
            <a href="{{ route('admin.repair_orders.index') }}" class="rounded-xl bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900/60 shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="border-b border-slate-700/50 bg-white/5 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Phiếu</th>
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4">Phương tiện</th>
                        <th class="px-6 py-4 text-center">Tiến độ</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Tổng tiền</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($repairOrders as $repairOrder)
                        @php
                            $totalTasks = $repairOrder->tasks->count();
                            $completedTasks = $repairOrder->tasks->where('status', 'completed')->count();
                            $percent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                        @endphp
                        <tr class="cursor-pointer transition hover:bg-white/[0.04]" onclick="window.location='{{ route('admin.repair_orders.show', $repairOrder) }}'">
                            <td class="px-6 py-4">
                                <div class="font-bold text-indigo-300">{{ $repairOrder->track_id }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $repairOrder->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $repairOrder->customer->name ?? 'Khách vãng lai' }}</div>
                                <div class="text-xs text-slate-500">{{ $repairOrder->customer->phone ?? 'Chưa có SĐT' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ trim(($repairOrder->vehicle->make ?? '').' '.($repairOrder->vehicle->model ?? '')) ?: 'Chưa rõ xe' }}</div>
                                <div class="mt-1 inline-flex rounded bg-blue-500/10 px-2 py-0.5 font-mono text-xs font-bold text-blue-300">{{ $repairOrder->vehicle->license_plate ?? 'Chưa có biển số' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="mb-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-700">
                                    <div class="h-full rounded-full {{ $percent === 100 ? 'bg-emerald-400' : 'bg-blue-400' }}" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="text-center text-xs font-medium text-slate-500">{{ $completedTasks }}/{{ $totalTasks }} công việc</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full border px-3 py-1 text-[11px] font-bold {{ $statusStyles[$repairOrder->status] ?? 'bg-slate-500/10 text-slate-300 border-slate-500/20' }}">
                                    {{ $statusLabels[$repairOrder->status] ?? $repairOrder->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-base font-bold text-white">{{ number_format($repairOrder->total_amount ?? 0, 0, ',', '.') }}đ</td>
                            <td class="px-6 py-4 text-right">
                                <span class="material-icons-round text-slate-500">arrow_forward_ios</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">Chưa có phiếu sửa chữa nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700/50 px-6 py-4">
            {{ $repairOrders->links() }}
        </div>
    </div>
</div>
@endsection
