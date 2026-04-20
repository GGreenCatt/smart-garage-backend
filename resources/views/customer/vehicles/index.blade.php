@extends('layouts.customer')

@section('title', 'Xe Của Tôi')

@section('content')
<main class="pt-24 min-h-screen">
    <div class="px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-white">Xe Của Tôi</h1>
            <button class="bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-cyan-900/30 transition">
                <i class="fas fa-plus mr-2"></i> Thêm Xe
            </button>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($vehicles as $vehicle)
            <div class="glass-panel bg-[#1e293b] border border-[#334155] rounded-xl overflow-hidden group hover:border-cyan-500/50 transition">
                <div class="aspect-video bg-slate-800 relative flex items-center justify-center">
                    <i class="fas fa-car text-5xl text-slate-600"></i>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1e293b] to-transparent opacity-60"></div>
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-bold text-white mb-1">{{ $vehicle->model }}</h3>
                    <p class="text-cyan-400 font-mono font-bold mb-4">{{ $vehicle->license_plate }}</p>
                    
                    <div class="space-y-2 text-sm text-slate-400 mb-6">
                        <div class="flex justify-between">
                            <span>Năm SX:</span> <span class="text-slate-300">{{ $vehicle->year ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VIN:</span> <span class="text-slate-300 font-mono text-xs">{{ $vehicle->vin ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('customer.vehicle.3d', $vehicle->id) }}" class="flex-1 bg-slate-700 hover:bg-cyan-600 text-white py-2 rounded-lg text-xs font-bold text-center transition">
                            <i class="fas fa-cube mr-1"></i> Xem 3D
                        </a>
                        <button class="flex-1 bg-slate-700 hover:bg-slate-600 text-white py-2 rounded-lg text-xs font-bold transition">
                            <i class="fas fa-history mr-1"></i> Lịch Sử
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <i class="fas fa-car-crash text-5xl mb-4 opacity-30"></i>
                <p>Bạn chưa có xe nào trong hệ thống.</p>
                <p class="text-sm mt-2">Vui lòng liên hệ garage để thêm xe mới.</p>
            </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
