@extends('layouts.customer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('customer.dashboard') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm transition"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-black text-slate-800">Đặt Lịch Hẹn</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 p-6 md:p-8">
        <form action="{{ route('customer.appointments.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehicle Selection or Input -->
                <div class="space-y-4 md:col-span-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Thông Tin Xe</label>
                    
                    <div class="flex gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="vehicle_choice" value="existing" checked class="text-indigo-600 focus:ring-indigo-500" onchange="toggleVehicleInput()">
                            <span class="text-slate-700 font-medium">Chọn xe đã lưu</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="vehicle_choice" value="new" class="text-indigo-600 focus:ring-indigo-500" onchange="toggleVehicleInput()">
                            <span class="text-slate-700 font-medium">Nhập xe khác</span>
                        </label>
                    </div>

                    <!-- Existing Vehicles -->
                    <div id="existing_vehicles" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($vehicles as $v)
                        <label class="cursor-pointer relative">
                            <input type="radio" name="vehicle_id" value="{{ $v->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-100 bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition">
                                <div class="font-bold text-slate-800">{{ $v->license_plate }}</div>
                                <div class="text-xs text-slate-500">{{ $v->model }}</div>
                                <i class="fas fa-check-circle absolute top-4 right-4 text-indigo-500 opacity-0 peer-checked:opacity-100 transition transform scale-0 peer-checked:scale-100"></i>
                            </div>
                        </label>
                        @endforeach
                        @if($vehicles->isEmpty())
                        <div class="p-4 bg-yellow-50 text-yellow-600 rounded-lg text-sm">Bạn chưa có xe nào. Hãy chọn "Nhập xe khác".</div>
                        @endif
                    </div>

                    <!-- New Vehicle Input -->
                    <div id="new_vehicle_inputs" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase">Tên Xe (Ví dụ: Honda Civic)</label>
                            <input type="text" name="vehicle_name" id="vehicle_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" placeholder="Nhập tên xe">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase">Biển Số Xe</label>
                            <input type="text" name="license_plate" id="license_plate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" placeholder="Nhập biển số xe">
                        </div>
                    </div>
                </div>

                <!-- Service Selection -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Dịch Vụ (Tùy chọn)</label>
                    <select name="service_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700">
                        <option value="">-- Chọn dịch vụ --</option>
                        @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} - {{ number_format($s->price) }}đ</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Time -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Thời Gian</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" required>
                </div>

                <!-- Reason -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Lý do đặt lịch / Yêu cầu sửa chữa</label>
                    <textarea name="reason" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" placeholder="Nhập lý do hoặc vấn đề cần kiểm tra..."></textarea>
                </div>

                <!-- Notes -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Ghi Chú Thêm</label>
                    <textarea name="notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" placeholder="Bất kỳ ghi chú nào khác..."></textarea>
                </div>
            </div>

            <button class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 transform active:scale-95 transition text-lg">
                Xác Nhận Đặt Lịch
            </button>
        </form>
    </div>
</div>

<script>
    // Initial check in case there are no vehicles
    document.addEventListener('DOMContentLoaded', function() {
        const hasVehicles = "{{ !$vehicles->isEmpty() ? 'true' : 'false' }}" === "true";
        if (!hasVehicles) {
            document.querySelector('input[name="vehicle_choice"][value="new"]').click();
            document.querySelector('input[name="vehicle_choice"][value="existing"]').disabled = true;
        }
        toggleVehicleInput();
    });

    function toggleVehicleInput() {
        const choice = document.querySelector('input[name="vehicle_choice"]:checked').value;
        const existingSection = document.getElementById('existing_vehicles');
        const newSection = document.getElementById('new_vehicle_inputs');
        
        // Inputs
        const vehicleRadios = document.querySelectorAll('input[name="vehicle_id"]');
        const vehicleNameInput = document.getElementById('vehicle_name');
        const licensePlateInput = document.getElementById('license_plate');

        if (choice === 'existing') {
            existingSection.classList.remove('hidden');
            newSection.classList.add('hidden');
            
            // Enable radios, disable text inputs
            vehicleRadios.forEach(radio => radio.disabled = false);
            vehicleNameInput.disabled = true;
            licensePlateInput.disabled = true;
        } else {
            existingSection.classList.add('hidden');
            newSection.classList.remove('hidden');
            
            // Disable radios, enable text inputs
            vehicleRadios.forEach(radio => radio.disabled = true);
            vehicleNameInput.disabled = false;
            licensePlateInput.disabled = false;
        }
    }
</script>
@endsection
