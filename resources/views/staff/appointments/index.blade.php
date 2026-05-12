@extends('layouts.staff')

@section('title', 'Lá»‹ch Háº¹n')

@section('content')
@php
    $statusConfig = [
        'pending' => [
            'label' => 'Chá» xÃ¡c nháº­n',
            'badge' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
            'dot' => 'bg-amber-500',
        ],
        'confirmed' => [
            'label' => 'ÄÃ£ xÃ¡c nháº­n',
            'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
            'dot' => 'bg-emerald-500',
        ],
        'completed' => [
            'label' => 'ÄÃ£ tiáº¿p nháº­n',
            'badge' => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/20',
            'dot' => 'bg-blue-500',
        ],
        'cancelled' => [
            'label' => 'ÄÃ£ há»§y',
            'badge' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20',
            'dot' => 'bg-red-500',
        ],
        'no_show' => [
            'label' => 'KhÃ¡ch khÃ´ng Ä‘áº¿n',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            'dot' => 'bg-slate-500',
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
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-500 dark:text-indigo-400">Quáº£n lÃ½ lá»‹ch háº¹n</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Lá»‹ch háº¹n khÃ¡ch hÃ ng</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Theo dÃµi lá»‹ch Ä‘áº·t, xÃ¡c nháº­n khÃ¡ch Ä‘áº¿n vÃ  chuyá»ƒn nhanh sang lá»‡nh sá»­a chá»¯a.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('staff.appointments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-indigo-500">
                <i class="fas fa-rotate-right text-xs"></i>
                LÃ m má»›i
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
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Tá»•ng lá»‹ch</span>
                <i class="fas fa-calendar-days text-indigo-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $appointments->count() }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">HÃ´m nay</span>
                <i class="fas fa-clock text-blue-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $todayCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Chá» xÃ¡c nháº­n</span>
                <i class="fas fa-hourglass-half text-amber-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $pendingCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">ÄÃ£ xÃ¡c nháº­n</span>
                <i class="fas fa-circle-check text-emerald-500"></i>
            </div>
            <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $confirmedCount }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" action="{{ route('staff.appointments.index') }}" class="grid gap-3 lg:grid-cols-[1.4fr_0.8fr_0.8fr_auto]">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="TÃ¬m theo tÃªn, SÄT, biá»ƒn sá»‘, máº«u xe..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
            </div>

            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                <option value="">Táº¥t cáº£ tráº¡ng thÃ¡i</option>
                @foreach($statusConfig as $status => $config)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $config['label'] }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">

            <div class="flex gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                    <i class="fas fa-filter text-xs"></i>
                    Lá»c
                </button>
                @if(request()->hasAny(['q', 'status', 'date']))
                    <a href="{{ route('staff.appointments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-500 transition hover:text-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white">
                        XÃ³a
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
                        <th class="px-5 py-4">KhÃ¡ch hÃ ng</th>
                        <th class="px-5 py-4">Xe</th>
                        <th class="px-5 py-4">Dá»‹ch vá»¥ / yÃªu cáº§u</th>
                        <th class="px-5 py-4">Thá»i gian</th>
                        <th class="px-5 py-4">Tráº¡ng thÃ¡i</th>
                        <th class="px-5 py-4 text-right">Thao tÃ¡c</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($appointments as $appt)
                        @php
                            $config = $statusConfig[$appt->status] ?? ['label' => 'KhÃ´ng rÃµ', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700', 'dot' => 'bg-slate-400'];
                            $vehicleLabel = $appt->vehicle
                                ? trim(($appt->vehicle->license_plate ?? '') . ' - ' . ($appt->vehicle->model ?? ''))
                                : trim(($appt->license_plate ?? 'ChÆ°a rÃµ biá»ƒn sá»‘') . ' - ' . ($appt->vehicle_name ?? 'ChÆ°a rÃµ xe'));
                        @endphp
                        <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-black text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                        {{ mb_substr($appt->customer->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-black text-slate-900 dark:text-white">{{ $appt->customer->name ?? 'KhÃ¡ch láº»' }}</div>
                                        <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->customer->phone ?? 'ChÆ°a cÃ³ SÄT' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-black uppercase text-slate-800 dark:text-slate-100">{{ $appt->vehicle->license_plate ?? $appt->license_plate ?? 'ChÆ°a rÃµ' }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->vehicle->model ?? $appt->vehicle_name ?? 'ChÆ°a rÃµ xe' }}</div>
                            </td>
                            <td class="max-w-sm px-5 py-4">
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $appt->service->name ?? 'TÆ° váº¥n thÃªm' }}</div>
                                <div class="mt-1 truncate text-xs font-medium text-slate-500 dark:text-slate-400" title="{{ $appt->reason }}">{{ $appt->reason ?: 'KhÃ¡ch chÆ°a ghi yÃªu cáº§u cá»¥ thá»ƒ' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-black text-indigo-600 dark:text-indigo-300">{{ $appt->scheduled_at?->format('H:i') ?? '--:--' }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->scheduled_at?->format('d/m/Y') ?? 'ChÆ°a cÃ³ ngÃ y' }}</div>
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
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300" title="XÃ¡c nháº­n lá»‹ch">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($appt->status, ['pending', 'confirmed']))
                                        <form action="{{ route('staff.appointments.convert', $appt->id) }}" method="POST" onsubmit="return confirm('Tiáº¿p nháº­n xe vÃ  táº¡o lá»‡nh sá»­a chá»¯a?');">
                                            @csrf
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300" title="Tiáº¿p nháº­n xe">
                                                <i class="fas fa-file-invoice"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST" onsubmit="return confirm('Há»§y lá»‹ch háº¹n nÃ y?');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300" title="Há»§y lá»‹ch">
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
                                            "admin_notes" => $appt->admin_notes,
                                        "status" => $appt->status,
                                        "vehicle" => $vehicleLabel,
                                    ]))' class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" title="Chá»‰nh sá»­a">
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
                                <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">ChÆ°a cÃ³ lá»‹ch háº¹n phÃ¹ há»£p</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Thá»­ Ä‘á»•i bá»™ lá»c hoáº·c kiá»ƒm tra láº¡i cÃ¡c lá»‹ch khÃ¡ch má»›i Ä‘áº·t.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 lg:hidden">
            @forelse($appointments as $appt)
                @php
                    $config = $statusConfig[$appt->status] ?? ['label' => 'KhÃ´ng rÃµ', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700', 'dot' => 'bg-slate-400'];
                    $vehicleLabel = $appt->vehicle
                        ? trim(($appt->vehicle->license_plate ?? '') . ' - ' . ($appt->vehicle->model ?? ''))
                        : trim(($appt->license_plate ?? 'ChÆ°a rÃµ biá»ƒn sá»‘') . ' - ' . ($appt->vehicle_name ?? 'ChÆ°a rÃµ xe'));
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-black text-slate-900 dark:text-white">{{ $appt->customer->name ?? 'KhÃ¡ch láº»' }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $appt->customer->phone ?? 'ChÆ°a cÃ³ SÄT' }}</div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $config['badge'] }}">
                            <span class="h-2 w-2 rounded-full {{ $config['dot'] }}"></span>
                            {{ $config['label'] }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-950/60">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Xe</span>
                            <span class="text-right font-black uppercase text-slate-800 dark:text-slate-100">{{ $appt->vehicle->license_plate ?? $appt->license_plate ?? 'ChÆ°a rÃµ' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Dá»‹ch vá»¥</span>
                            <span class="text-right font-bold text-slate-800 dark:text-slate-100">{{ $appt->service->name ?? 'TÆ° váº¥n thÃªm' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-500 dark:text-slate-400">Thá»i gian</span>
                            <span class="text-right font-black text-indigo-600 dark:text-indigo-300">{{ $appt->scheduled_at?->format('H:i d/m/Y') ?? 'ChÆ°a cÃ³ ngÃ y' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        @if($appt->status === 'pending')
                            <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-black text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">XÃ¡c nháº­n</button>
                            </form>
                        @endif
                        @if(in_array($appt->status, ['pending', 'confirmed']))
                            <form action="{{ route('staff.appointments.convert', $appt->id) }}" method="POST" onsubmit="return confirm('Tiáº¿p nháº­n xe vÃ  táº¡o lá»‡nh sá»­a chá»¯a?');">
                                @csrf
                                <button type="submit" class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-black text-white">Tiáº¿p nháº­n</button>
                            </form>
                        @endif
                        <button type="button" onclick='openEditModal(@js([
                            "id" => $appt->id,
                            "scheduled_at" => $appt->scheduled_at?->format("Y-m-d\TH:i"),
                            "service_id" => $appt->service_id,
                            "reason" => $appt->reason,
                            "notes" => $appt->notes,
                            "admin_notes" => $appt->admin_notes,
                            "status" => $appt->status,
                            "vehicle" => $vehicleLabel,
                        ]))' class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">Sá»­a</button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <i class="fas fa-calendar-xmark text-2xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">ChÆ°a cÃ³ lá»‹ch háº¹n phÃ¹ há»£p</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Thá»­ Ä‘á»•i bá»™ lá»c hoáº·c kiá»ƒm tra láº¡i cÃ¡c lá»‹ch khÃ¡ch má»›i Ä‘áº·t.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<dialog id="editModal" class="m-auto w-full max-w-3xl bg-transparent p-4 backdrop:bg-slate-950/70 backdrop:backdrop-blur-sm">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500">Cáº­p nháº­t lá»‹ch</p>
                <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-white">Chi tiáº¿t lá»‹ch háº¹n</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Pháº£n há»“i garage cho khÃ¡ch</span>
                <textarea name="admin_notes" id="edit_admin_notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" placeholder="VÃ­ dá»¥: Garage Ä‘Ã£ xÃ¡c nháº­n lá»‹ch, vui lÃ²ng Ä‘áº¿n trÆ°á»›c 10 phÃºt."></textarea>
            </label>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">PhÆ°Æ¡ng tiá»‡n</span>
                    <input type="text" id="edit_vehicle" class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-600 outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300" readonly>
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Thá»i gian háº¹n</span>
                    <input type="datetime-local" name="scheduled_at" id="edit_scheduled_at" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" required>
                </label>
            </div>

            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Dá»‹ch vá»¥</span>
                <select name="service_id" id="edit_service_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                    <option value="">ChÆ°a xÃ¡c Ä‘á»‹nh / cáº§n tÆ° váº¥n thÃªm</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} - {{ number_format($service->base_price ?? $service->price ?? 0) }}Ä‘</option>
                    @endforeach
                </select>
            </label>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">YÃªu cáº§u cá»§a khÃ¡ch</span>
                    <textarea name="reason" id="edit_reason" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" placeholder="KhÃ¡ch chÆ°a ghi yÃªu cáº§u cá»¥ thá»ƒ"></textarea>
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Ghi chÃº ná»™i bá»™</span>
                    <textarea name="notes" id="edit_notes" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500" placeholder="ThÃ´ng tin nhÃ¢n viÃªn cáº§n lÆ°u Ã½"></textarea>
                </label>
            </div>

            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Tráº¡ng thÃ¡i</span>
                <select name="status" id="edit_status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                    <option value="pending">Chá» xÃ¡c nháº­n</option>
                    <option value="confirmed">ÄÃ£ xÃ¡c nháº­n</option>
                    <option value="cancelled">ÄÃ£ há»§y</option>
                    <option value="no_show">Khách không đến</option>
                </select>
            </label>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Há»§y</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">LÆ°u thay Ä‘á»•i</button>
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
        document.getElementById('edit_admin_notes').value = data.admin_notes || '';
        document.getElementById('edit_status').value = data.status || 'pending';
        document.getElementById('edit_vehicle').value = data.vehicle || 'ChÆ°a rÃµ xe';
        modal.showModal();
    }

    function closeEditModal() {
        modal.close();
    }
</script>
@endpush
