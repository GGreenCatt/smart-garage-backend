@extends('layouts.admin')

@section('title', 'Vehicle Registry')

@section('content')
<div class="space-y-6">
    <!-- Header & Search -->
    <div class="flex justify-between items-center bg-slate-900/50 p-4 rounded-xl border border-slate-700">
        <form action="{{ route('admin.vehicles.index') }}" method="GET" class="relative w-96">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm biển số, VIN, chủ xe..." class="w-full bg-slate-800 border border-slate-600 rounded-lg pl-10 pr-4 py-2 text-slate-200 focus:outline-none focus:border-indigo-500">
            <i class="fas fa-search absolute left-3 top-3 text-slate-500"></i>
        </form>
        <button onclick="document.getElementById('quickAddVehicle').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition shadow-lg shadow-indigo-500/20">
            <i class="fas fa-plus mr-2"></i> Đăng Ký Xe Mới
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4">
         <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700">
             <div class="text-xs text-slate-500 font-bold uppercase">Tổng Số Xe</div>
             <div class="text-2xl font-bold text-white">{{ $vehicles->total() }}</div>
         </div>
         <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700">
             <div class="text-xs text-slate-500 font-bold uppercase">Đang Sửa Chữa</div>
             <div class="text-2xl font-bold text-blue-400">0</div>
         </div>
    </div>

    <!-- Vehicle Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($vehicles as $vehicle)
        <div class="glass-panel p-5 rounded-2xl border border-slate-700/50 hover:border-indigo-500/50 transition group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-20 group-hover:opacity-100 transition">
                <i class="fas fa-arrow-right text-indigo-400"></i>
            </div>
            
            <div class="flex flex-col items-center mb-4">
                <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center text-4xl mb-3 shadow-inner">
                    @if($vehicle->type == 'motorcycle') <i class="fas fa-motorcycle text-slate-600 group-hover:text-indigo-500 transition"></i>
                    @elseif($vehicle->type == 'truck') <i class="fas fa-truck-pickup text-slate-600 group-hover:text-indigo-500 transition"></i>
                    @else <i class="fas fa-car text-slate-600 group-hover:text-indigo-500 transition"></i> @endif
                </div>
                <h3 class="font-bold text-white text-lg">{{ $vehicle->model }}</h3>
                <div class="bg-indigo-600 text-white font-mono font-bold px-3 py-1 rounded text-sm mt-1 shadow-lg shadow-indigo-600/20">
                    {{ $vehicle->license_plate }}
                </div>
            </div>

            <div class="space-y-2 text-sm text-slate-400 border-t border-slate-700/50 pt-4">
                <div class="flex justify-between">
                    <span>Owner:</span>
                    <span class="text-white font-medium truncate w-1/2 text-right">
                        {{ $vehicle->user->name ?? $vehicle->owner_name ?? 'Guest' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>VIN:</span>
                    <span class="font-mono text-xs">{{ $vehicle->vin ?? '---' }}</span>
                </div>
            </div>

            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="absolute inset-0 z-10"></a>
        </div>
        @empty
        <div class="col-span-full p-12 text-center text-slate-500 italic border border-dashed border-slate-700 rounded-2xl">
            No vehicles found. Try searching by License Plate or VIN.
        </div>
        @endforelse
    </div>
    
    <div class="p-4">
        {{ $vehicles->links() }}
    </div>

    <!-- Quick Add Modal -->
    <div id="quickAddVehicle" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                 <h3 class="font-bold text-white text-lg">Quick Register Vehicle</h3>
                 <button onclick="document.getElementById('quickAddVehicle').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.vehicles.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">License Plate</label>
                    <input type="text" name="license_plate" required placeholder="30A-123.45" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono uppercase focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                     <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Model</label>
                        <input type="text" name="model" required placeholder="Toyota Vios" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Type</label>
                        <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="truck">Truck</option>
                            <option value="motorcycle">Motorcycle</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Owner Name (Required)</label>
                    <input type="text" name="owner_name" required placeholder="Nguyen Van A" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Owner Phone (Optional)</label>
                    <input type="tel" name="owner_phone" placeholder="Link to existing user..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">VIN Number (Optional)</label>
                    <input type="text" name="vin" placeholder="17-char VIN..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono uppercase focus:border-indigo-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg mt-2">Register Now</button>
            </form>
        </div>
    </div>
</div>
@endsection
