@extends('layouts.customer')

@section('title', 'Xe của tôi')

@section('content')
<main class="pt-24 min-h-screen bg-[#0b1120]">
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white mb-3">
                    <i class="fas fa-arrow-left"></i> Về tổng quan
                </a>
                <h1 class="text-3xl font-black text-white">Xe của tôi</h1>
                <p class="text-slate-400 mt-2">Danh sách xe được garage liên kết với tài khoản của bạn.</p>
            </div>
            <a href="{{ route('customer.appointments.create') }}" class="inline-flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-3 rounded-xl text-sm font-black shadow-lg shadow-cyan-950/30 transition">
                <i class="fas fa-calendar-plus"></i> Đặt lịch cho xe
            </a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($vehicles as $vehicle)
                <article class="bg-slate-900/70 border border-slate-800 rounded-2xl overflow-hidden hover:border-cyan-500/40 transition">
                    <div class="aspect-video bg-slate-950 relative flex items-center justify-center">
                        <i class="fas fa-car-side text-6xl text-slate-700"></i>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-70"></div>
                        <div class="absolute left-4 bottom-4">
                            <div class="text-white font-black text-lg">{{ $vehicle->license_plate }}</div>
                            <div class="text-slate-400 text-sm">{{ $vehicle->model ?: 'Chưa rõ dòng xe' }}</div>
                        </div>
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-950/50 border border-slate-800 p-3">
                                <div class="text-slate-500 text-xs uppercase font-bold">Năm sản xuất</div>
                                <div class="text-white font-bold mt-1">{{ $vehicle->year ?? 'Chưa rõ' }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-950/50 border border-slate-800 p-3">
                                <div class="text-slate-500 text-xs uppercase font-bold">Màu xe</div>
                                <div class="text-white font-bold mt-1">{{ $vehicle->color ?? 'Chưa rõ' }}</div>
                            </div>
                        </div>
                        @if($vehicle->vin)
                            <div class="text-xs text-slate-500 font-mono break-all">VIN: {{ $vehicle->vin }}</div>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('customer.vehicle.3d', $vehicle->id) }}" class="flex-1 bg-cyan-600/20 hover:bg-cyan-600/30 border border-cyan-500/25 text-cyan-200 py-3 rounded-xl text-xs font-black text-center transition">
                                <i class="fas fa-cube mr-1"></i> Xem 3D
                            </a>
                            <a href="{{ route('customer.appointments.create') }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white py-3 rounded-xl text-xs font-black text-center transition">
                                <i class="fas fa-calendar-plus mr-1"></i> Đặt lịch
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-16 text-center border border-dashed border-slate-700 rounded-2xl text-slate-400">
                    <i class="fas fa-car-crash text-5xl mb-4 opacity-30"></i>
                    <h3 class="font-black text-white">Chưa có xe trong hệ thống</h3>
                    <p class="text-sm mt-2">Garage sẽ liên kết xe với tài khoản của bạn khi tiếp nhận hoặc xác nhận thông tin.</p>
                    <a href="{{ route('customer.appointments.create') }}" class="inline-flex mt-5 bg-cyan-600 hover:bg-cyan-500 text-white font-black py-3 px-5 rounded-xl">Đặt lịch kiểm tra xe</a>
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
