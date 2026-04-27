@extends('layouts.admin')

@section('title', 'Chi Tiết Phương Tiện')

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

<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.vehicles.index') }}" class="transition hover:text-indigo-300">Phương tiện</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">{{ $vehicle->license_plate }}</span>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel flex flex-col overflow-hidden rounded-2xl border border-slate-700/50 md:flex-row">
        <div class="relative flex w-full flex-col items-center justify-center border-r border-slate-700/50 bg-slate-900 p-8 md:w-1/3">
            <div class="mb-6 flex h-40 w-40 items-center justify-center rounded-full bg-slate-800 text-7xl shadow-2xl">
                @if($vehicle->type == 'motorcycle')
                    <i class="fas fa-motorcycle text-slate-500"></i>
                @elseif($vehicle->type == 'truck')
                    <i class="fas fa-truck-pickup text-slate-500"></i>
                @else
                    <i class="fas fa-car text-slate-500"></i>
                @endif
            </div>
            <h1 class="text-center text-3xl font-bold text-white">{{ trim(($vehicle->make ?? '').' '.($vehicle->model ?? '')) ?: 'Chưa rõ dòng xe' }}</h1>
            <div class="mt-1 text-sm uppercase tracking-widest text-indigo-300">{{ $vehicle->type }}</div>
        </div>

        <div class="flex-1 space-y-8 p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Biển số</label>
                    <div class="font-mono text-4xl font-black tracking-wide text-white">{{ $vehicle->license_plate }}</div>
                </div>
                <div class="md:text-right">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Số khung / VIN</label>
                    <div class="font-mono text-lg text-slate-300">{{ $vehicle->vin ?: 'Chưa đăng ký' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 border-t border-slate-700/50 pt-8 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Thông tin chủ xe</label>
                    @if($vehicle->user)
                        <a href="{{ route('admin.customers.show', $vehicle->user->id) }}" class="group flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($vehicle->user->name) }}&background=14b8a6&color=fff" class="h-10 w-10 rounded-lg" alt="{{ $vehicle->user->name }}">
                            <div>
                                <div class="font-bold text-white transition group-hover:text-indigo-300">{{ $vehicle->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $vehicle->user->phone }}</div>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('admin.customers.show', 'guest-'.$vehicle->id) }}" class="group flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800">
                                <i class="fas fa-user text-slate-600"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-300 transition group-hover:text-indigo-300">{{ $vehicle->owner_name ?: 'Khách vãng lai' }}</div>
                                <div class="text-xs text-slate-600">{{ $vehicle->owner_phone ?: 'Chưa có liên hệ' }}</div>
                            </div>
                        </a>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Tóm tắt sửa chữa</label>
                    <div class="flex gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ $vehicle->repairOrders->count() }}</div>
                            <div class="text-[10px] uppercase text-slate-500">Lượt vào xưởng</div>
                        </div>
                        <div class="w-px bg-slate-700"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-300">{{ $vehicle->repairOrders->where('status', 'completed')->count() }}</div>
                            <div class="text-[10px] uppercase text-slate-500">Đã hoàn thành</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="fas fa-tools text-indigo-400"></i>
                Lịch sử sửa chữa
            </h2>
            <a href="{{ route('admin.vehicles.3d', $vehicle) }}" class="rounded-lg border border-indigo-500/30 px-4 py-2 text-sm font-bold text-indigo-300 transition hover:bg-indigo-600 hover:text-white">
                Xem kiểm tra 3D
            </a>
        </div>

        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            @forelse($vehicle->repairOrders->sortByDesc('created_at') as $order)
                <div class="group relative border-l border-slate-700 pb-8 pl-8 last:border-0 last:pb-0">
                    <div class="absolute left-[-5px] top-1 h-2.5 w-2.5 rounded-full bg-slate-600 ring-4 ring-slate-900 transition group-hover:bg-indigo-500"></div>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white transition group-hover:text-indigo-300">{{ $order->service_type ?: 'Lệnh sửa #' . $order->id }}</h3>
                            <p class="mt-1 text-sm text-slate-400">Cố vấn: {{ $order->advisor->name ?? 'Chưa phân công' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="rounded px-2 py-1 text-xs font-bold uppercase {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                            <div class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-500">Xe này chưa có lịch sử sửa chữa.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
