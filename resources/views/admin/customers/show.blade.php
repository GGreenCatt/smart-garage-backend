@extends('layouts.admin')

@section('title', 'Hồ Sơ Khách Hàng')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Đang chờ',
        'in_progress' => 'Đang sửa',
        'pending_approval' => 'Chờ duyệt báo giá',
        'approved' => 'Đã duyệt',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.customers.index') }}" class="transition hover:text-indigo-300">Khách hàng</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">{{ $customer->name }}</span>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel flex flex-col gap-8 rounded-2xl border border-slate-700/50 p-8 md:flex-row md:items-start">
        <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}&background=6366f1&color=fff&size=128" class="h-32 w-32 rounded-2xl border-4 border-slate-800 shadow-2xl" alt="{{ $customer->name }}">
        <div class="flex-1 space-y-3 text-center md:text-left">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <h1 class="text-3xl font-bold text-white">{{ $customer->name }}</h1>
                @if($isGuest)
                    <span class="mx-auto rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300 md:mx-0">Khách vãng lai</span>
                @else
                    <span class="mx-auto rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300 md:mx-0">Có tài khoản</span>
                @endif
            </div>
            <div class="flex flex-wrap justify-center gap-4 text-slate-400 md:justify-start">
                <span class="flex items-center gap-2"><i class="fas fa-envelope text-indigo-400"></i> {{ $customer->email ?: 'Chưa có email' }}</span>
                <span class="flex items-center gap-2"><i class="fas fa-phone text-indigo-400"></i> {{ $customer->phone ?: 'Chưa có SĐT' }}</span>
                <span class="flex items-center gap-2"><i class="fas fa-calendar text-indigo-400"></i> Ghi nhận {{ optional($customer->created_at)->format('m/Y') ?: 'không rõ' }}</span>
            </div>
            <div class="flex justify-center gap-3 pt-4 md:justify-start">
                @unless($isGuest)
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-white transition hover:bg-slate-700">
                        <i class="fas fa-edit mr-2"></i>
                        Chỉnh sửa
                    </a>
                @endunless
            </div>
        </div>

        <div class="grid w-full grid-cols-3 gap-4 md:w-auto">
            <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ $vehicles->count() }}</div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Xe</div>
            </div>
            <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4 text-center">
                <div class="text-2xl font-bold text-green-300">{{ $orders->count() }}</div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Lệnh sửa</div>
            </div>
            <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4 text-center">
                <div class="text-2xl font-bold text-amber-300">{{ number_format($totalSpent, 0, ',', '.') }}đ</div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Đã trả</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <h2 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="fas fa-car text-indigo-400"></i>
                Phương tiện
            </h2>
            @forelse($vehicles as $vehicle)
                <div class="glass-panel rounded-xl border border-slate-700/50 p-5 transition hover:border-indigo-500/50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-800 text-2xl text-slate-500">
                                <i class="fas fa-car"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white">{{ trim(($vehicle->make ?? '').' '.($vehicle->model ?? '')) ?: 'Chưa rõ dòng xe' }}</h3>
                                <div class="mt-1 inline-block rounded border border-indigo-500/20 bg-indigo-500/10 px-2 py-0.5 font-mono text-xs font-bold text-indigo-300">{{ $vehicle->license_plate }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="text-slate-500 transition hover:text-white" title="Xem xe">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="mt-4 flex justify-between text-xs text-slate-400">
                        <span>VIN: {{ $vehicle->vin ?: 'Chưa có' }}</span>
                        <span>{{ $vehicle->repairOrders->count() }} lệnh sửa</span>
                    </div>
                </div>
            @empty
                <div class="glass-panel rounded-xl border border-slate-700/50 p-6 text-center text-slate-500">Chưa có phương tiện.</div>
            @endforelse
        </div>

        <div class="space-y-4">
            <h2 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="fas fa-history text-indigo-400"></i>
                Lịch sử sửa chữa gần đây
            </h2>
            <div class="glass-panel overflow-hidden rounded-xl border border-slate-700/50">
                <table class="w-full text-left text-sm text-slate-400">
                    <tbody class="divide-y divide-slate-800">
                        @forelse($orders->take(8) as $order)
                            <tr class="transition hover:bg-slate-800/30">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-white">{{ $order->service_type ?: 'Lệnh sửa #' . $order->id }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->vehicle?->license_plate }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="rounded px-2 py-1 text-[10px] font-bold uppercase {{ $order->status === 'completed' ? 'bg-green-500/10 text-green-300' : 'bg-blue-500/10 text-blue-300' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                    <div class="mt-1 text-[10px] text-slate-600">{{ $order->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-500">Chưa có lịch sử sửa chữa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
