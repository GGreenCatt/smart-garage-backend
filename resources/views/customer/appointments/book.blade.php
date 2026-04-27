@extends('layouts.customer')

@section('title', 'Đặt lịch hẹn - Smart Garage')

@section('content')
@php
    $defaultChoice = old('vehicle_choice', $vehicles->isEmpty() ? 'new' : 'existing');
    $minSchedule = now()->addMinutes(30)->format('Y-m-d\TH:i');
@endphp

<main class="min-h-screen bg-[#0f172a] px-4 pb-16 pt-28 text-white md:px-6">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex items-center justify-between gap-3">
            <a href="{{ route('customer.dashboard') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white">
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="{{ route('customer.appointments.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-black text-cyan-200 transition hover:bg-cyan-400/20">
                <i class="fas fa-calendar-days"></i>
                Lịch hẹn của tôi
            </a>
        </div>

        <section class="mb-8 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900 shadow-2xl shadow-black/20">
            <div class="grid gap-0 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="p-6 md:p-8 lg:p-10">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-200">
                        <i class="fas fa-clock"></i>
                        Đặt lịch nhanh
                    </div>
                    <h1 class="mt-5 text-3xl font-black leading-tight text-white md:text-5xl">Đặt lịch kiểm tra xe</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-300">
                        Chọn xe, thời gian phù hợp và mô tả tình trạng xe. Nhân viên garage sẽ xác nhận lại lịch hẹn trước khi bạn mang xe đến.
                    </p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300"><i class="fas fa-car"></i></div>
                            <div class="mt-3 text-sm font-black">Chọn xe</div>
                            <div class="mt-1 text-xs text-slate-400">Xe đã lưu hoặc xe mới</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300"><i class="fas fa-screwdriver-wrench"></i></div>
                            <div class="mt-3 text-sm font-black">Chọn dịch vụ</div>
                            <div class="mt-1 text-xs text-slate-400">Có thể để trống để tư vấn</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400/10 text-amber-300"><i class="fas fa-calendar-check"></i></div>
                            <div class="mt-3 text-sm font-black">Chờ xác nhận</div>
                            <div class="mt-1 text-xs text-slate-400">Garage sẽ phản hồi lịch</div>
                        </div>
                    </div>
                </div>

                <div class="relative min-h-[280px] border-t border-white/10 bg-slate-950 lg:border-l lg:border-t-0">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(34,211,238,0.28),transparent_34%),radial-gradient(circle_at_70%_70%,rgba(16,185,129,0.18),transparent_30%)]"></div>
                    <div class="relative flex h-full flex-col justify-end p-6 md:p-8">
                        <div class="rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-xl backdrop-blur">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-xs font-black uppercase tracking-wider text-slate-500">Khung giờ sớm nhất</div>
                                    <div class="mt-1 text-2xl font-black text-white">{{ now()->addMinutes(30)->format('H:i') }}</div>
                                </div>
                                <div class="rounded-2xl bg-cyan-400/10 px-4 py-3 text-right">
                                    <div class="text-xs font-bold text-cyan-200">{{ now()->addMinutes(30)->format('d/m/Y') }}</div>
                                    <div class="mt-1 text-[11px] font-bold text-slate-400">Có thể đặt từ thời điểm này</div>
                                </div>
                            </div>
                            <div class="mt-5 rounded-2xl bg-white/5 p-4 text-sm leading-6 text-slate-300">
                                Mẹo: mô tả rõ âm thanh lạ, vị trí lỗi, thời điểm lỗi xuất hiện hoặc hình thức bảo dưỡng mong muốn để nhân viên chuẩn bị tốt hơn.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($errors->any())
            <div class="mb-6 rounded-3xl border border-red-400/30 bg-red-500/10 p-5 text-red-100">
                <div class="font-black">Vui lòng kiểm tra lại thông tin đặt lịch</div>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.appointments.store') }}" method="POST" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            @csrf

            <section class="space-y-6">
                <div class="rounded-3xl border border-white/10 bg-slate-900 p-5 shadow-xl shadow-black/10 md:p-6">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                            <span class="text-sm font-black">1</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Thông tin xe</h2>
                            <p class="mt-1 text-sm text-slate-400">Chọn xe đã lưu hoặc nhập xe khác nếu bạn muốn đặt lịch cho xe mới.</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="vehicle_choice" value="existing" {{ $defaultChoice === 'existing' ? 'checked' : '' }} class="peer sr-only" onchange="toggleVehicleInput()" @disabled($vehicles->isEmpty())>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-400/10 peer-disabled:cursor-not-allowed peer-disabled:opacity-40">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-black text-white">Chọn xe đã lưu</div>
                                    <i class="fas fa-circle-check text-cyan-300 opacity-0 transition peer-checked:opacity-100"></i>
                                </div>
                                <div class="mt-1 text-sm text-slate-400">{{ $vehicles->count() }} xe trong hồ sơ</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="vehicle_choice" value="new" {{ $defaultChoice === 'new' ? 'checked' : '' }} class="peer sr-only" onchange="toggleVehicleInput()">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-400/10">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-black text-white">Nhập xe khác</div>
                                    <i class="fas fa-circle-check text-cyan-300 opacity-0 transition peer-checked:opacity-100"></i>
                                </div>
                                <div class="mt-1 text-sm text-slate-400">Dùng khi xe chưa có trong hồ sơ</div>
                            </div>
                        </label>
                    </div>

                    <div id="existing_vehicles" class="mt-5 grid gap-3 md:grid-cols-2">
                        @forelse($vehicles as $vehicle)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="peer sr-only" {{ (string) old('vehicle_id', $loop->first ? $vehicle->id : '') === (string) $vehicle->id ? 'checked' : '' }}>
                                <div class="rounded-2xl border border-white/10 bg-slate-950 p-4 transition hover:border-cyan-400/40 peer-checked:border-cyan-400 peer-checked:bg-cyan-400/10">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-mono text-lg font-black uppercase text-white">{{ $vehicle->license_plate }}</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-400">{{ $vehicle->model }}</div>
                                        </div>
                                        <i class="fas fa-check-circle text-cyan-300 opacity-0 transition peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="rounded-2xl border border-amber-400/20 bg-amber-400/10 p-4 text-sm font-semibold text-amber-100 md:col-span-2">
                                Bạn chưa có xe nào trong hồ sơ. Hãy chọn “Nhập xe khác” để tiếp tục đặt lịch.
                            </div>
                        @endforelse
                    </div>

                    <div id="new_vehicle_inputs" class="mt-5 hidden grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Tên xe</span>
                            <input type="text" name="vehicle_name" id="vehicle_name" value="{{ old('vehicle_name') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-bold text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400" placeholder="Ví dụ: Honda Civic">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Biển số xe</span>
                            <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-mono font-black uppercase text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400" placeholder="Ví dụ: 51A-123.45">
                        </label>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900 p-5 shadow-xl shadow-black/10 md:p-6">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/10 text-emerald-300">
                            <span class="text-sm font-black">2</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Dịch vụ và thời gian</h2>
                            <p class="mt-1 text-sm text-slate-400">Bạn có thể chọn dịch vụ cụ thể hoặc để garage tư vấn khi xác nhận lịch.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Dịch vụ mong muốn</span>
                            <select name="service_id" id="service_id" class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-bold text-white outline-none transition focus:border-cyan-400">
                                <option value="">Cần tư vấn thêm</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}{{ ($service->base_price ?? 0) > 0 ? ' - '.number_format($service->base_price, 0, ',', '.').'đ' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Thời gian hẹn</span>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" min="{{ $minSchedule }}" class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-bold text-white outline-none transition focus:border-cyan-400" required>
                        </label>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900 p-5 shadow-xl shadow-black/10 md:p-6">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-400/10 text-amber-300">
                            <span class="text-sm font-black">3</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Tình trạng xe</h2>
                            <p class="mt-1 text-sm text-slate-400">Ghi rõ vấn đề để nhân viên chuẩn bị trước khi bạn đến garage.</p>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Lý do đặt lịch / yêu cầu sửa chữa</span>
                            <textarea name="reason" id="reason" rows="4" class="w-full resize-none rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-semibold text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400" placeholder="Ví dụ: Xe phát tiếng kêu khi phanh, điều hòa không lạnh, cần kiểm tra trước chuyến đi...">{{ old('reason') }}</textarea>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Ghi chú thêm</span>
                            <textarea name="notes" id="notes" rows="3" class="w-full resize-none rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 font-semibold text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400" placeholder="Ví dụ: Tôi muốn được gọi trước khi xác nhận lịch.">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </div>
            </section>

            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-3xl border border-white/10 bg-slate-900 p-5 shadow-xl shadow-black/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div>
                            <h2 class="font-black text-white">Tóm tắt lịch hẹn</h2>
                            <p class="text-xs font-semibold text-slate-500">Kiểm tra trước khi gửi</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="rounded-2xl bg-slate-950 p-4">
                            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Xe</div>
                            <div id="summaryVehicle" class="mt-1 font-black text-white">Chưa chọn xe</div>
                        </div>
                        <div class="rounded-2xl bg-slate-950 p-4">
                            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Dịch vụ</div>
                            <div id="summaryService" class="mt-1 font-black text-white">Cần tư vấn thêm</div>
                        </div>
                        <div class="rounded-2xl bg-slate-950 p-4">
                            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Thời gian</div>
                            <div id="summaryTime" class="mt-1 font-black text-white">Chưa chọn thời gian</div>
                        </div>
                    </div>

                    <button class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-cyan-500 px-5 py-4 text-base font-black text-slate-950 shadow-lg shadow-cyan-950/30 transition hover:bg-cyan-400 active:scale-[0.99]">
                        <i class="fas fa-calendar-check"></i>
                        Xác nhận đặt lịch
                    </button>

                    <p class="mt-4 text-center text-xs leading-5 text-slate-500">
                        Sau khi gửi, lịch hẹn sẽ ở trạng thái chờ xác nhận. Nhân viên có thể liên hệ lại nếu cần đổi giờ.
                    </p>
                </div>
            </aside>
        </form>
    </div>
</main>

<script>
    const vehicleData = @json($vehicles->map(fn($v) => ['id' => $v->id, 'plate' => $v->license_plate, 'model' => $v->model]));

    document.addEventListener('DOMContentLoaded', function() {
        const hasVehicles = @json(!$vehicles->isEmpty());
        if (!hasVehicles) {
            const existingChoice = document.querySelector('input[name="vehicle_choice"][value="existing"]');
            const newChoice = document.querySelector('input[name="vehicle_choice"][value="new"]');
            if (existingChoice) existingChoice.disabled = true;
            if (newChoice) newChoice.checked = true;
        }

        toggleVehicleInput();
        updateSummary();

        document.querySelectorAll('input[name="vehicle_choice"], input[name="vehicle_id"], #vehicle_name, #license_plate, #service_id, #scheduled_at')
            .forEach(element => {
                element.addEventListener('change', updateSummary);
                element.addEventListener('input', updateSummary);
            });
    });

    function toggleVehicleInput() {
        const checkedChoice = document.querySelector('input[name="vehicle_choice"]:checked');
        const choice = checkedChoice ? checkedChoice.value : 'new';
        const existingSection = document.getElementById('existing_vehicles');
        const newSection = document.getElementById('new_vehicle_inputs');
        const vehicleRadios = document.querySelectorAll('input[name="vehicle_id"]');
        const vehicleNameInput = document.getElementById('vehicle_name');
        const licensePlateInput = document.getElementById('license_plate');

        if (choice === 'existing') {
            existingSection.classList.remove('hidden');
            newSection.classList.add('hidden');
            vehicleRadios.forEach(radio => radio.disabled = false);
            vehicleNameInput.disabled = true;
            licensePlateInput.disabled = true;
            vehicleNameInput.required = false;
            licensePlateInput.required = false;
        } else {
            existingSection.classList.add('hidden');
            newSection.classList.remove('hidden');
            vehicleRadios.forEach(radio => radio.disabled = true);
            vehicleNameInput.disabled = false;
            licensePlateInput.disabled = false;
            vehicleNameInput.required = true;
            licensePlateInput.required = true;
        }

        updateSummary();
    }

    function updateSummary() {
        const choice = document.querySelector('input[name="vehicle_choice"]:checked')?.value || 'new';
        let vehicleText = 'Chưa chọn xe';

        if (choice === 'existing') {
            const selectedVehicleId = document.querySelector('input[name="vehicle_id"]:checked')?.value;
            const selectedVehicle = vehicleData.find(vehicle => String(vehicle.id) === String(selectedVehicleId));
            if (selectedVehicle) {
                vehicleText = `${selectedVehicle.plate} - ${selectedVehicle.model || 'Xe đã lưu'}`;
            }
        } else {
            const plate = document.getElementById('license_plate')?.value?.trim();
            const name = document.getElementById('vehicle_name')?.value?.trim();
            vehicleText = plate || name ? `${plate || 'Chưa có biển số'} - ${name || 'Chưa có tên xe'}` : 'Chưa nhập xe mới';
        }

        const serviceSelect = document.getElementById('service_id');
        const serviceText = serviceSelect?.selectedOptions?.[0]?.textContent?.trim() || 'Cần tư vấn thêm';
        const timeValue = document.getElementById('scheduled_at')?.value;

        document.getElementById('summaryVehicle').textContent = vehicleText;
        document.getElementById('summaryService').textContent = serviceSelect?.value ? serviceText : 'Cần tư vấn thêm';
        document.getElementById('summaryTime').textContent = timeValue ? new Date(timeValue).toLocaleString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }) : 'Chưa chọn thời gian';
    }
</script>
@endsection
