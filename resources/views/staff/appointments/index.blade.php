@extends('layouts.staff')

@section('title', 'Lịch Hẹn')

@section('content')
@php
    $statusConfig = [
        'pending' => [
            'label' => 'Chờ xác nhận',
            'badge' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
            'dot' => 'bg-amber-500',
        ],
        'confirmed' => [
            'label' => 'Đã xác nhận',
            'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
            'dot' => 'bg-emerald-500',
        ],
        'completed' => [
            'label' => 'Đã tiếp nhận',
            'badge' => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/20',
            'dot' => 'bg-blue-500',
        ],
        'cancelled' => [
            'label' => 'Đã hủy',
            'badge' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20',
            'dot' => 'bg-red-500',
        ],
    ];

    $statusCounts = $appointments->groupBy('status')->map->count();
    $todayCount = $appointments->filter(fn ($appt) => $appt->scheduled_at?->isToday())->count();
    $pendingCount = $statusCounts->get('pending', 0);
    $confirmedCount = $statusCounts->get('confirmed', 0);
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-500 dark:text-indigo-400">Quản lý lịch hẹn</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Lịch hẹn khách hàng</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Theo dõi lịch đặt, xác nhận khách đến và chuyển nhanh sang lệnh sửa chữa.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('staff.appointments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-indigo-500">
                <i class="fas fa-rotate-right text-xs"></i>
                Làm mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Tổng lịch</span>
                <i class="fas fa-calendar-days text-indigo-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $appointments->count() }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Hôm nay</span>
                <i class="fas fa-clock text-blue-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $todayCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Chờ xác nhận</span>
                <i class="fas fa-hourglass-half text-amber-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $pendingCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Đã xác nhận</span>
                <i class="fas fa-circle-check text-emerald-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $confirmedCount }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" action="{{ route('staff.appointments.index') }}" class="grid gap-3 lg:grid-cols-[1.4fr_0.8fr_0.8fr_auto]">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, SĐT, biển số, mẫu xe..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
            </div>

            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                <option value="">Tất cả trạng thái</option>
                @foreach($statusConfig as $status => $config)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $config['label'] }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">

            <div class="flex gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                    <i class="fas fa-filter text-xs"></i>
                    Lọc
                </button>
                @if(request()->hasAny(['q', 'status', 'date']))
                    <a href="{{ route('staff.appointments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-500 transition hover:text-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white">
                        Xóa
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-400">
                        <th class="px-5 py-4">Khách hàng</th>
                        <th class="px-5 py-4">Xe</th>
                        <th class="px-5 py-4">Dịch vụ / yêu cầu</th>
                        <th class="px-5 py-4">Thời gian</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($appointments as $appt)
                        @php
                            $config = $statusConfig[$appt->status] ?? ['label' => 'Không rõ', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700', 'dot' => 'bg-slate-400'];
                            $vehicleLabel = $appt->vehicle
                                ? trim(($appt->vehicle->license_plate ?? '') . ' - ' . ($appt->vehicle->model ?? ''))
                                : trim(($appt->license_plate ?? 'Chưa rõ biển số') . ' - ' . ($appt->vehicle_name ?? 'Chưa rõ xe'));
                        @endphp
                        <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-black text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                        {{ mb_substr($appt->customer->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-black text-slate-900 dark:text-white">{{ $appt->customer->name ?? 'Khách lẻ' }}</div>
                                        <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->customer->phone ?? 'Chưa có SĐT' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-black uppercase text-slate-800 dark:text-slate-100">{{ $appt->vehicle->license_plate ?? $appt->license_plate ?? 'Chưa rõ' }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->vehicle->model ?? $appt->vehicle_name ?? 'Chưa rõ xe' }}</div>
                            </td>
                            <td class="max-w-sm px-5 py-4">
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $appt->service->name ?? 'Tư vấn thêm' }}</div>
                                <div class="mt-1 truncate text-xs font-medium text-slate-500 dark:text-slate-400" title="{{ $appt->reason }}">{{ $appt->reason ?: 'Khách chưa ghi yêu cầu cụ thể' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-black text-indigo-600 dark:text-indigo-300">{{ $appt->scheduled_at?->format('H:i') ?? '--:--' }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->scheduled_at?->format('d/m/Y') ?? 'Chưa có ngày' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $config['badge'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $config['dot'] }}"></span>
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if($appt->status === 'pending')
                                        <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300" title="Xác nhận lịch">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($appt->status, ['pending', 'confirmed']))
                                        <form action="{{ route('staff.appointments.convert', $appt->id) }}" method="POST" onsubmit="return confirm('Tiếp nhận xe và tạo lệnh sửa chữa?');">
                                            @csrf
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300" title="Tiếp nhận xe">
                                                <i class="fas fa-file-invoice"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST" onsubmit="return confirm('Hủy lịch hẹn này?');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300" title="Hủy lịch">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" onclick='openEditModal(@js([
                                        "id" => $appt->id,
                                        "scheduled_at" => $appt->scheduled_at?->format("Y-m-d\TH:i"),
                                        "service_id" => $appt->service_id,
                                        "reason" => $appt->reason,
                                        "notes" => $appt->notes,
                                        "status" => $appt->status,
                                        "vehicle" => $vehicleLabel,
                                    ]))' class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                    <i class="fas fa-calendar-xmark text-2xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">Chưa có lịch hẹn phù hợp</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Thử đổi bộ lọc hoặc kiểm tra lại các lịch khách mới đặt.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 lg:hidden">
            @forelse($appointments as $appt)
                @php
                    $config = $statusConfig[$appt->status] ?? ['label' => 'Không rõ', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700', 'dot' => 'bg-slate-400'];
                    $vehicleLabel = $appt->vehicle
                        ? trim(($appt->vehicle->license_plate ?? '') . ' - ' . ($appt->vehicle->model ?? ''))
                        : trim(($appt->license_plate ?? 'Chưa rõ biển số') . ' - ' . ($appt->vehicle_name ?? 'Chưa rõ xe'));
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-black text-slate-900 dark:text-white">{{ $appt->customer->name ?? 'Khách lẻ' }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->customer->phone ?? 'Chưa có SĐT' }}</div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $config['badge'] }}">
                            <span class="h-2 w-2 rounded-full {{ $config['dot'] }}"></span>
                            {{ $config['label'] }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-950/60">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Xe</span>
                            <span class="text-right font-black uppercase text-slate-800 dark:text-slate-100">{{ $appt->vehicle->license_plate ?? $appt->license_plate ?? 'Chưa rõ' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Dịch vụ</span>
                            <span class="text-right font-bold text-slate-800 dark:text-slate-100">{{ $appt->service->name ?? 'Tư vấn thêm' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Thời gian</span>
                            <span class="text-right font-black text-indigo-600 dark:text-indigo-300">{{ $appt->scheduled_at?->format('H:i d/m/Y') ?? 'Chưa có ngày' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        @if($appt->status === 'pending')
                            <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-black text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">Xác nhận</button>
                            </form>
                        @endif
                        @if(in_array($appt->status, ['pending', 'confirmed']))
                            <form action="{{ route('staff.appointments.convert', $appt->id) }}" method="POST" onsubmit="return confirm('Tiếp nhận xe và tạo lệnh sửa chữa?');">
                                @csrf
                                <button type="submit" class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-black text-white">Tiếp nhận</button>
                            </form>
                        @endif
                        <button type="button" onclick='openEditModal(@js([
                            "id" => $appt->id,
                            "scheduled_at" => $appt->scheduled_at?->format("Y-m-d\TH:i"),
                            "service_id" => $appt->service_id,
                            "reason" => $appt->reason,
                            "notes" => $appt->notes,
                            "status" => $appt->status,
                            "vehicle" => $vehicleLabel,
                        ]))' class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">Sửa</button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <i class="fas fa-calendar-xmark text-2xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">Chưa có lịch hẹn phù hợp</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Thử đổi bộ lọc hoặc kiểm tra lại các lịch khách mới đặt.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<dialog id="editModal" class="m-auto w-full max-w-3xl bg-transparent p-4 backdrop:bg-slate-950/70 backdrop:backdrop-blur-sm">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500">Cập nhật lịch</p>
                <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-white">Chi tiết lịch hẹn</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Phương tiện</span>
                    <input type="text" id="edit_vehicle" class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-600 outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300" readonly>
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Thời gian hẹn</span>
                    <input type="datetime-local" name="scheduled_at" id="edit_scheduled_at" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" required>
                </label>
            </div>

            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Dịch vụ</span>
                <select name="service_id" id="edit_service_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                    <option value="">Chưa xác định / cần tư vấn thêm</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} - {{ number_format($service->base_price ?? $service->price ?? 0) }}đ</option>
                    @endforeach
                </select>
            </label>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Yêu cầu của khách</span>
                    <textarea name="reason" id="edit_reason" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" placeholder="Khách chưa ghi yêu cầu cụ thể"></textarea>
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Ghi chú nội bộ</span>
                    <textarea name="notes" id="edit_notes" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" placeholder="Thông tin nhân viên cần lưu ý"></textarea>
                </label>
            </div>

            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Trạng thái</span>
                <select name="status" id="edit_status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="cancelled">Đã hủy</option>
                    <option value="completed">Đã tiếp nhận</option>
                </select>
            </label>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Hủy</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('editModal');

    function openEditModal(data) {
        document.getElementById('editForm').action = `/staff/appointments/${data.id}`;
        document.getElementById('edit_scheduled_at').value = data.scheduled_at || '';
        document.getElementById('edit_service_id').value = data.service_id || '';
        document.getElementById('edit_reason').value = data.reason || '';
        document.getElementById('edit_notes').value = data.notes || '';
        document.getElementById('edit_status').value = data.status || 'pending';
        document.getElementById('edit_vehicle').value = data.vehicle || 'Chưa rõ xe';
        modal.showModal();
    }

    function closeEditModal() {
        modal.close();
    }
</script>
@endpush
