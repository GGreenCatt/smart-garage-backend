@extends('layouts.admin')

@section('title', 'Quản Lý Phương Tiện')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-xl border border-slate-700 bg-slate-900/50 p-4 lg:flex-row lg:items-center lg:justify-between">
        <form action="{{ route('admin.vehicles.index') }}" method="GET" class="relative w-full lg:max-w-xl">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm biển số, VIN, hãng xe, chủ xe hoặc số điện thoại" class="w-full rounded-lg border border-slate-600 bg-slate-800 py-3 pl-10 pr-4 text-slate-200 focus:border-indigo-500 focus:outline-none">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
        </form>
        <div class="flex gap-3">
            @if(request('search'))
                <a href="{{ route('admin.vehicles.index') }}" class="rounded-lg bg-slate-800 px-4 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
            @endif
            <button onclick="document.getElementById('quickAddVehicle').classList.remove('hidden')" class="rounded-lg bg-indigo-600 px-4 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
                <i class="fas fa-plus mr-2"></i>
                Đăng ký xe mới
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="text-xs font-bold uppercase text-slate-500">Tổng số xe</div>
            <div class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="text-xs font-bold uppercase text-slate-500">Đang trong xưởng</div>
            <div class="text-2xl font-bold text-blue-300">{{ number_format($stats['in_service']) }}</div>
        </div>
        <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="text-xs font-bold uppercase text-slate-500">Đã gắn tài khoản</div>
            <div class="text-2xl font-bold text-emerald-300">{{ number_format($stats['with_owner']) }}</div>
        </div>
        <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="text-xs font-bold uppercase text-slate-500">Xe khách vãng lai</div>
            <div class="text-2xl font-bold text-amber-300">{{ number_format($stats['walk_in']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($vehicles as $vehicle)
            <div class="glass-panel group relative overflow-hidden rounded-2xl border border-slate-700/50 p-5 transition hover:border-indigo-500/50">
                <div class="absolute right-0 top-0 p-3 opacity-20 transition group-hover:opacity-100">
                    <i class="fas fa-arrow-right text-indigo-400"></i>
                </div>

                <div class="mb-4 flex flex-col items-center">
                    <div class="mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-slate-800 text-4xl shadow-inner">
                        @if($vehicle->type == 'motorcycle')
                            <i class="fas fa-motorcycle text-slate-600 transition group-hover:text-indigo-400"></i>
                        @elseif($vehicle->type == 'truck')
                            <i class="fas fa-truck-pickup text-slate-600 transition group-hover:text-indigo-400"></i>
                        @else
                            <i class="fas fa-car text-slate-600 transition group-hover:text-indigo-400"></i>
                        @endif
                    </div>
                    <h3 class="text-center text-lg font-bold text-white">{{ trim(($vehicle->make ?? '').' '.($vehicle->model ?? '')) ?: 'Chưa rõ dòng xe' }}</h3>
                    <div class="mt-1 rounded bg-indigo-600 px-3 py-1 font-mono text-sm font-bold text-white shadow-lg shadow-indigo-600/20">
                        {{ $vehicle->license_plate }}
                    </div>
                </div>

                <div class="space-y-2 border-t border-slate-700/50 pt-4 text-sm text-slate-400">
                    <div class="flex justify-between gap-3">
                        <span>Chủ xe:</span>
                        <span class="w-1/2 truncate text-right font-medium text-white">{{ $vehicle->user->name ?? $vehicle->owner_name ?? 'Khách vãng lai' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>SĐT:</span>
                        <span class="font-mono text-xs">{{ $vehicle->owner_phone ?: 'Chưa có' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>VIN:</span>
                        <span class="font-mono text-xs">{{ $vehicle->vin ?: 'Chưa có' }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="absolute inset-0 z-10" aria-label="Xem phương tiện {{ $vehicle->license_plate }}"></a>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-700 p-12 text-center text-slate-500">
                Không tìm thấy phương tiện phù hợp.
            </div>
        @endforelse
    </div>

    <div class="p-4">
        {{ $vehicles->links() }}
    </div>

    <div id="quickAddVehicle" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 p-6">
                <h3 class="text-lg font-bold text-white">Đăng ký xe nhanh</h3>
                <button onclick="document.getElementById('quickAddVehicle').classList.add('hidden')" class="text-slate-500 hover:text-white" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.vehicles.store') }}" method="POST" class="space-y-4 p-6">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Biển số xe</label>
                    <input type="text" name="license_plate" value="{{ old('license_plate') }}" required placeholder="30A-123.45" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 font-mono uppercase text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Hãng xe</label>
                        <input type="text" name="make" value="{{ old('make') }}" placeholder="Toyota" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Dòng xe</label>
                        <input type="text" name="model" value="{{ old('model') }}" required placeholder="Vios" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Loại xe</label>
                    <select name="type" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                        <option value="sedan">Sedan</option>
                        <option value="suv">SUV</option>
                        <option value="truck">Bán tải / tải nhẹ</option>
                        <option value="motorcycle">Xe máy</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Tên chủ xe</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" required placeholder="Nguyễn Văn A" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Số điện thoại chủ xe</label>
                    <input type="tel" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="Tự gắn tài khoản nếu trùng SĐT khách" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Số khung / VIN</label>
                    <input type="text" name="vin" value="{{ old('vin') }}" placeholder="Nếu có" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 font-mono uppercase text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <button type="submit" class="mt-2 w-full rounded-lg bg-indigo-600 py-3 font-bold text-white transition hover:bg-indigo-500">Đăng ký xe</button>
            </form>
        </div>
    </div>
</div>
@endsection
