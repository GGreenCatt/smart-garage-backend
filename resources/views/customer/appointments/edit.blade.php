@extends('layouts.customer')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center gap-3">
        <a href="{{ route('customer.appointments.index') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm transition"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-black text-slate-800">Cập Nhật Lịch Hẹn</h1>
    </div>

    @if(session('error'))
        <div class="bg-red-100 text-red-600 p-4 rounded-xl font-medium border border-red-200">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-600 p-4 rounded-xl font-medium border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.appointments.update', $appointment->id) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
        @csrf
        @method('PUT')

        <!-- Vehicle Selection Mode -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-slate-800 border-b pb-2">1. Thông tin phương tiện</h3>
            
            <div class="flex gap-4 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="vehicle_mode" value="saved" class="text-indigo-600 focus:ring-indigo-500" {{ $appointment->vehicle_id ? 'checked' : '' }} onchange="toggleVehicleMode()">
                    <span class="font-bold text-slate-700">Xe đã lưu</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="vehicle_mode" value="new" class="text-indigo-600 focus:ring-indigo-500" {{ !$appointment->vehicle_id ? 'checked' : '' }} onchange="toggleVehicleMode()">
                    <span class="font-bold text-slate-700">Nhập xe khác</span>
                </label>
            </div>

            <!-- Saved Vehicles -->
            <div id="saved_vehicle_section" class="{{ !$appointment->vehicle_id ? 'hidden' : '' }}">
                @if($vehicles->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($vehicles as $vehicle)
                            <label class="relative block cursor-pointer group">
                                <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="peer sr-only" {{ $appointment->vehicle_id == $vehicle->id ? 'checked' : '' }}>
                                <div class="p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-100 bg-slate-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                    <div class="font-bold text-slate-800 uppercase">{{ $vehicle->license_plate }}</div>
                                    <div class="text-sm text-slate-500">{{ $vehicle->model }} ({{ $vehicle->year }})</div>
                                    <div class="absolute top-4 right-4 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-xl font-medium border border-yellow-200">
                        Bạn chưa có xe nào được lưu. Vui lòng chọn "Nhập xe khác".
                    </div>
                @endif
            </div>

            <!-- Manual Vehicle Entry -->
            <div id="manual_vehicle_section" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ $appointment->vehicle_id ? 'hidden' : '' }}">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tên xe / Dòng xe <span class="text-red-500">*</span></label>
                    <input type="text" name="vehicle_name" id="vehicle_name" value="{{ old('vehicle_name', $appointment->vehicle_name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="Ví dụ: Toyota Vios, Honda City...">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Biển số xe <span class="text-red-500">*</span></label>
                    <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate', $appointment->license_plate) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all uppercase" placeholder="Ví dụ: 30A-123.45">
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">2. Dịch vụ & Thời gian</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Thời gian mang xe đến <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" required>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Dịch vụ (Tùy chọn)</label>
                    <select name="service_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-slate-700">
                        <option value="">-- Chưa xác định / Tư vấn thêm --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ (old('service_id', $appointment->service_id) == $service->id) ? 'selected' : '' }}>
                                {{ $service->name }} ({{ number_format($service->base_price ?? 0) }}đ)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">3. Vấn đề của xe</h3>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả tình trạng / Lý do đặt lịch</label>
                <textarea name="reason" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-slate-400" placeholder="Xe bạn đang gặp vấn đề gì? (Kêu lạch cạch, rỉ nhớt, tới hạn bảo dưỡng...)">{{ old('reason', $appointment->reason) }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 transform active:scale-[0.99] transition-all text-lg flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Lưu Thay Đổi
            </button>
        </div>
    </form>
</div>

<script>
    function toggleVehicleMode() {
        const mode = document.querySelector('input[name="vehicle_mode"]:checked').value;
        const savedSection = document.getElementById('saved_vehicle_section');
        const manualSection = document.getElementById('manual_vehicle_section');
        const vehicleNameInput = document.getElementById('vehicle_name');
        const licensePlateInput = document.getElementById('license_plate');
        const radioSaved = document.querySelectorAll('input[name="vehicle_id"]');

        if (mode === 'saved') {
            savedSection.classList.remove('hidden');
            manualSection.classList.add('hidden');
            vehicleNameInput.required = false;
            licensePlateInput.required = false;
            vehicleNameInput.value = '';
            licensePlateInput.value = '';
        } else {
            savedSection.classList.add('hidden');
            manualSection.classList.remove('hidden');
            vehicleNameInput.required = true;
            licensePlateInput.required = true;
            // Uncheck saved vehicles
            radioSaved.forEach(radio => radio.checked = false);
        }
    }
</script>
@endsection
