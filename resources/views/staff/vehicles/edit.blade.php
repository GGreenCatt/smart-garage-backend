@extends('layouts.staff')

@section('title', 'Chỉnh Sửa Xe')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Chỉnh Sửa Thông Tin Xe</h1>
        <a href="{{ route('staff.customers.show', $vehicle->user_id ?? 1) }}" class="text-slate-400 hover:text-white transition">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    <div class="glass-panel p-8 rounded-2xl max-w-2xl mx-auto bg-[#1e293b] border border-[#334155]">
        <form action="{{ route('staff.vehicles.update', $vehicle->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_id" value="{{ $vehicle->user_id }}">
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Biển Số</label>
                    <input type="text" name="license_plate" value="{{ $vehicle->license_plate }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white font-mono focus:outline-none focus:border-cyan-500" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Số VIN</label>
                    <input type="text" name="vin" value="{{ $vehicle->vin }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white font-mono focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Mẫu Xe (Model)</label>
                    <input type="text" name="model" value="{{ $vehicle->model }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Loại Xe</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500">
                        <option value="Sedan" {{ $vehicle->type == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="SUV" {{ $vehicle->type == 'SUV' ? 'selected' : '' }}>SUV/Crossover</option>
                        <option value="Hatchback" {{ $vehicle->type == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                        <option value="Truck" {{ $vehicle->type == 'Truck' ? 'selected' : '' }}>Bán tải</option>
                        <option value="Van" {{ $vehicle->type == 'Van' ? 'selected' : '' }}>Van/Minivan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Năm Sản Xuất</label>
                    <input type="number" name="year" value="{{ $vehicle->year }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Màu Sắc</label>
                    <input type="text" name="color" value="{{ $vehicle->color }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700 flex justify-end">
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-cyan-900/40 transition">
                    <i class="fas fa-save mr-2"></i> Cập Nhật Xe
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
