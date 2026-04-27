@extends('layouts.admin')

@section('title', 'Quản Lý Lịch Hẹn')

@php
    $statusStyles = [
        'pending' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
        'confirmed' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
        'completed' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
        'cancelled' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
        'no_show' => 'bg-slate-500/15 text-slate-300 border-slate-500/30',
    ];

    $prevMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');
    $appointmentPayloads = $calendarAppointments->flatten()->merge($appointments)->unique('id')->values();
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-300">Điều phối xưởng</p>
            <h1 class="mt-2 text-3xl font-black text-white">Quản lý lịch hẹn</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-400">
                Theo dõi lịch khách đặt, xác nhận thời gian, ghi chú nội bộ và tiếp nhận xe thành phiếu sửa chữa.
            </p>
        </div>
        <button type="button" onclick="openAppointmentModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500">
            <i class="fas fa-plus"></i>
            Thêm lịch hẹn
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-bold text-emerald-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm font-bold text-rose-200">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm text-rose-100">
            <div class="font-black">Chưa thể lưu lịch hẹn</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-white/10 bg-slate-900/80 p-5">
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Hôm nay</p>
            <div class="mt-3 text-3xl font-black text-white">{{ $stats['today'] }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/80 p-5">
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Chờ xác nhận</p>
            <div class="mt-3 text-3xl font-black text-amber-300">{{ $stats['pending'] }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/80 p-5">
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Đã xác nhận</p>
            <div class="mt-3 text-3xl font-black text-emerald-300">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/80 p-5">
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Sắp tới</p>
            <div class="mt-3 text-3xl font-black text-indigo-300">{{ $stats['upcoming'] }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.appointments.index') }}" class="rounded-2xl border border-white/10 bg-slate-900/80 p-4">
        <div class="grid gap-3 lg:grid-cols-[1.3fr_0.8fr_0.8fr_0.8fr_auto]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo khách, SĐT, biển số, yêu cầu..." class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
                <option value="all">Tất cả trạng thái</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white transition hover:bg-indigo-500">Lọc</button>
                <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-3 text-sm font-black text-slate-300 transition hover:border-slate-500 hover:text-white">Xóa</a>
            </div>
        </div>
    </form>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <section class="rounded-2xl border border-white/10 bg-slate-900/80 p-5">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-black text-white">Lịch tháng {{ $month->format('m/Y') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Bấm vào một lịch hẹn trong ô ngày để xem và chỉnh sửa.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.appointments.index', array_merge(request()->except('page'), ['month' => $prevMonth])) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 text-slate-300 transition hover:border-indigo-400 hover:text-white">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <a href="{{ route('admin.appointments.index', ['month' => now()->format('Y-m')]) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-700 px-4 text-sm font-black text-slate-300 transition hover:border-indigo-400 hover:text-white">Tháng này</a>
                    <a href="{{ route('admin.appointments.index', array_merge(request()->except('page'), ['month' => $nextMonth])) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 text-slate-300 transition hover:border-indigo-400 hover:text-white">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-7 overflow-hidden rounded-2xl border border-slate-800">
                @foreach(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $weekday)
                    <div class="border-b border-slate-800 bg-slate-950 px-3 py-2 text-center text-xs font-black uppercase tracking-wider text-slate-500">{{ $weekday }}</div>
                @endforeach

                @foreach($calendarDays as $day)
                    @php
                        $dayAppointments = $calendarAppointments->get($day->toDateString(), collect());
                        $isCurrentMonth = $day->month === $month->month;
                    @endphp
                    <div class="min-h-[132px] border-b border-r border-slate-800 p-2 {{ $isCurrentMonth ? 'bg-slate-900' : 'bg-slate-950/70' }}">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-black {{ $day->isToday() ? 'bg-indigo-600 text-white' : ($isCurrentMonth ? 'text-slate-200' : 'text-slate-600') }}">
                                {{ $day->day }}
                            </span>
                            @if($dayAppointments->count())
                                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[11px] font-black text-slate-300">{{ $dayAppointments->count() }}</span>
                            @endif
                        </div>
                        <div class="mt-2 space-y-1.5">
                            @foreach($dayAppointments->take(3) as $appointment)
                                <button type="button" onclick="openAppointmentById({{ $appointment->id }})" class="block w-full rounded-lg border px-2 py-1.5 text-left text-[11px] font-bold leading-tight transition hover:scale-[1.01] {{ $statusStyles[$appointment->status] ?? $statusStyles['pending'] }}">
                                    <span class="block truncate">{{ $appointment->scheduled_at->format('H:i') }} - {{ $appointment->customer->name ?? 'Khách lẻ' }}</span>
                                    <span class="block truncate opacity-80">{{ $appointment->vehicle->license_plate ?? $appointment->license_plate ?? 'Chưa có biển số' }}</span>
                                </button>
                            @endforeach
                            @if($dayAppointments->count() > 3)
                                <a href="{{ route('admin.appointments.index', ['date' => $day->toDateString(), 'month' => $month->format('Y-m')]) }}" class="block rounded-lg bg-slate-800 px-2 py-1 text-center text-[11px] font-black text-slate-300 hover:bg-slate-700">
                                    +{{ $dayAppointments->count() - 3 }} lịch nữa
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-slate-900/80">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-xl font-black text-white">Danh sách lịch hẹn</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $appointments->count() }} lịch hẹn theo bộ lọc hiện tại</p>
            </div>
            <div class="max-h-[760px] overflow-y-auto p-4">
                @forelse($appointments as $appointment)
                    <div class="mb-3 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-black text-white">{{ $appointment->customer->name ?? 'Khách lẻ' }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500">{{ $appointment->customer->phone ?? 'Chưa có SĐT' }}</div>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusStyles[$appointment->status] ?? $statusStyles['pending'] }}">
                                {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-slate-300">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-clock w-4 text-indigo-300"></i>
                                <span class="font-bold">{{ $appointment->scheduled_at->format('H:i d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-car w-4 text-indigo-300"></i>
                                <span>{{ $appointment->vehicle->model ?? $appointment->vehicle_name ?? 'Chưa rõ xe' }} - {{ $appointment->vehicle->license_plate ?? $appointment->license_plate ?? 'Chưa có biển số' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-screwdriver-wrench w-4 text-indigo-300"></i>
                                <span>{{ $appointment->service->name ?? 'Chưa chọn dịch vụ' }}</span>
                            </div>
                        </div>

                        @if($appointment->reason)
                            <div class="mt-3 rounded-xl bg-slate-900 px-3 py-2 text-sm text-slate-400">{{ $appointment->reason }}</div>
                        @endif

                        <div class="mt-4 grid gap-2 sm:grid-cols-3">
                            <button type="button" onclick="openAppointmentById({{ $appointment->id }})" class="rounded-xl border border-slate-700 px-3 py-2 text-sm font-black text-slate-200 transition hover:border-indigo-400 hover:text-white">
                                Sửa
                            </button>
                            @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true))
                                <form method="POST" action="{{ route('admin.appointments.convert', $appointment) }}" onsubmit="return confirm('Tiếp nhận xe và tạo phiếu sửa chữa từ lịch hẹn này?');">
                                    @csrf
                                    <button class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-sm font-black text-white transition hover:bg-emerald-500">Tiếp nhận</button>
                                </form>
                            @endif
                            @if($appointment->status !== 'completed')
                                <form method="POST" action="{{ route('admin.appointments.destroy', $appointment) }}" onsubmit="return confirm('Xóa lịch hẹn này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full rounded-xl border border-rose-500/40 px-3 py-2 text-sm font-black text-rose-300 transition hover:bg-rose-500/10">Xóa</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-700 p-8 text-center">
                        <div class="text-lg font-black text-white">Chưa có lịch hẹn</div>
                        <p class="mt-2 text-sm text-slate-500">Thử đổi bộ lọc hoặc tạo lịch hẹn mới cho khách.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<dialog id="appointmentModal" class="w-[min(720px,calc(100vw-24px))] rounded-2xl border border-slate-700 bg-slate-950 p-0 text-white shadow-2xl backdrop:bg-black/70">
    <form id="appointmentForm" method="POST" action="{{ route('admin.appointments.store') }}">
        @csrf
        <input type="hidden" name="_method" id="appointmentMethod" value="POST" disabled>

        <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
            <div>
                <h3 id="appointmentModalTitle" class="text-xl font-black">Thêm lịch hẹn</h3>
                <p class="mt-1 text-sm text-slate-500">Nhập thông tin khách, xe và thời gian hẹn.</p>
            </div>
            <button type="button" onclick="closeAppointmentModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="grid gap-5 p-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Chọn khách đã có</label>
                <select name="customer_id" id="customer_id" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
                    <option value="">Tạo khách mới hoặc nhập theo SĐT</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Tên khách mới</label>
                <input type="text" name="customer_name" id="customer_name" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Số điện thoại</label>
                <input type="text" name="customer_phone" id="customer_phone" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Tên xe / dòng xe</label>
                <input type="text" name="vehicle_name" id="vehicle_name" placeholder="Toyota Vios, Mazda 3..." class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Biển số</label>
                <input type="text" name="license_plate" id="license_plate" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Thời gian hẹn</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400" required>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Trạng thái</label>
                <select name="status" id="status" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Dịch vụ dự kiến</label>
                <select name="service_id" id="service_id" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400">
                    <option value="">Chưa chọn dịch vụ</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}{{ $service->base_price ? ' - '.number_format($service->base_price, 0, ',', '.').'đ' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Yêu cầu của khách</label>
                <textarea name="reason" id="reason" rows="4" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400"></textarea>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Ghi chú nội bộ</label>
                <textarea name="notes" id="notes" rows="4" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-indigo-400"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-800 px-6 py-4">
            <button type="button" onclick="closeAppointmentModal()" class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-black text-slate-300 transition hover:border-slate-500 hover:text-white">Đóng</button>
            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white transition hover:bg-indigo-500">Lưu lịch hẹn</button>
        </div>
    </form>
</dialog>

<script>
    const appointmentModal = document.getElementById('appointmentModal');
    const appointmentForm = document.getElementById('appointmentForm');
    const appointmentMethod = document.getElementById('appointmentMethod');
    const appointmentPayloads = @json($appointmentPayloads);
    const storeUrl = @json(route('admin.appointments.store'));

    function openAppointmentById(appointmentId) {
        const appointment = appointmentPayloads.find((item) => Number(item.id) === Number(appointmentId));
        if (appointment) {
            openAppointmentModal(appointment);
        }
    }

    function openAppointmentModal(appointment = null) {
        appointmentForm.reset();
        appointmentForm.action = storeUrl;
        appointmentMethod.disabled = true;
        appointmentMethod.value = 'POST';
        document.getElementById('appointmentModalTitle').innerText = 'Thêm lịch hẹn';
        document.getElementById('status').value = 'confirmed';

        if (appointment) {
            document.getElementById('appointmentModalTitle').innerText = 'Cập nhật lịch hẹn';
            appointmentForm.action = `/admin/appointments/${appointment.id}`;
            appointmentMethod.disabled = false;
            appointmentMethod.value = 'PUT';

            document.getElementById('customer_id').value = appointment.customer_id || '';
            document.getElementById('customer_name').value = appointment.customer?.name || '';
            document.getElementById('customer_phone').value = appointment.customer?.phone || '';
            document.getElementById('vehicle_name').value = appointment.vehicle?.model || appointment.vehicle_name || '';
            document.getElementById('license_plate').value = appointment.vehicle?.license_plate || appointment.license_plate || '';
            document.getElementById('service_id').value = appointment.service_id || '';
            document.getElementById('status').value = appointment.status || 'pending';
            document.getElementById('reason').value = appointment.reason || '';
            document.getElementById('notes').value = appointment.notes || '';

            if (appointment.scheduled_at) {
                const date = new Date(appointment.scheduled_at);
                const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
                document.getElementById('scheduled_at').value = localDate.toISOString().slice(0, 16);
            }
        }

        appointmentModal.showModal();
    }

    function closeAppointmentModal() {
        appointmentModal.close();
    }
</script>
@endsection
