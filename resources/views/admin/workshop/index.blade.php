@extends('layouts.admin')

@section('title', 'Bảng Vận Hành Xưởng')

@php
    $colorClasses = [
        'amber' => 'border-amber-500/30 bg-amber-500/10 text-amber-300',
        'blue' => 'border-blue-500/30 bg-blue-500/10 text-blue-300',
        'violet' => 'border-violet-500/30 bg-violet-500/10 text-violet-300',
        'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'cyan' => 'border-cyan-500/30 bg-cyan-500/10 text-cyan-300',
        'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
    ];

    $paymentLabels = [
        'paid' => 'Đã thanh toán',
        'partial' => 'Thanh toán một phần',
        'unpaid' => 'Chưa thanh toán',
    ];

    $nextStatusOptions = [
        \App\Models\RepairOrder::STATUS_PENDING => [
            \App\Models\RepairOrder::STATUS_IN_PROGRESS => 'Bắt đầu xử lý',
            \App\Models\RepairOrder::STATUS_CANCELLED => 'Hủy phiếu',
        ],
        \App\Models\RepairOrder::STATUS_IN_PROGRESS => [
            \App\Models\RepairOrder::STATUS_PENDING_APPROVAL => 'Chờ khách duyệt',
            \App\Models\RepairOrder::STATUS_COMPLETED => 'Hoàn thành',
            \App\Models\RepairOrder::STATUS_CANCELLED => 'Hủy phiếu',
        ],
        \App\Models\RepairOrder::STATUS_PENDING_APPROVAL => [
            \App\Models\RepairOrder::STATUS_APPROVED => 'Đánh dấu đã duyệt',
            \App\Models\RepairOrder::STATUS_IN_PROGRESS => 'Quay lại xử lý',
            \App\Models\RepairOrder::STATUS_CANCELLED => 'Hủy phiếu',
        ],
        \App\Models\RepairOrder::STATUS_APPROVED => [
            \App\Models\RepairOrder::STATUS_IN_PROGRESS => 'Tiếp tục sửa',
            \App\Models\RepairOrder::STATUS_COMPLETED => 'Hoàn thành',
            \App\Models\RepairOrder::STATUS_CANCELLED => 'Hủy phiếu',
        ],
    ];

    $tabOrder = [
        \App\Models\RepairOrder::STATUS_PENDING,
        \App\Models\RepairOrder::STATUS_IN_PROGRESS,
        \App\Models\RepairOrder::STATUS_PENDING_APPROVAL,
        \App\Models\RepairOrder::STATUS_APPROVED,
        \App\Models\RepairOrder::STATUS_COMPLETED,
        \App\Models\RepairOrder::STATUS_CANCELLED,
    ];

    $defaultTab = request('tab', \App\Models\RepairOrder::STATUS_PENDING);
@endphp

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-indigo-400">Điều phối vận hành</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Bảng vận hành xưởng</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Chỉ hiển thị một nhóm phiếu tại một thời điểm để tránh kéo dài trang quá mức.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 dark:hover:text-white">
                    <i class="fas fa-calendar-check"></i>
                    Lịch hẹn
                </a>
                <a href="{{ route('admin.repair_orders.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500">
                    <i class="fas fa-plus"></i>
                    Tạo phiếu sửa chữa
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-bold text-emerald-700 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm font-bold text-rose-700 dark:text-rose-200">{{ session('error') }}</div>
    @endif

    <section class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Xe đang xử lý</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500"><i class="fas fa-tools"></i></span>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-900 dark:text-white">{{ $stats['active_orders'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Chờ khách duyệt</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-500"><i class="fas fa-user-check"></i></span>
            </div>
            <div class="mt-4 text-3xl font-black text-violet-500">{{ $stats['waiting_customer'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Hẹn hôm nay</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500"><i class="fas fa-calendar-day"></i></span>
            </div>
            <div class="mt-4 text-3xl font-black text-amber-500">{{ $stats['due_today'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Hoàn tất chưa thu</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500"><i class="fas fa-wallet"></i></span>
            </div>
            <div class="mt-4 text-3xl font-black text-rose-500">{{ $stats['unpaid_completed'] }}</div>
        </div>
    </section>

    <form method="GET" action="{{ route('admin.workshop.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
        <div class="grid gap-3 xl:grid-cols-[1.4fr_0.9fr_0.9fr_0.8fr_auto]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo mã phiếu, khách, SĐT, biển số..." class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <select name="advisor_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Tất cả cố vấn</option>
                @foreach($advisors as $advisor)
                    <option value="{{ $advisor->id }}" @selected((string) request('advisor_id') === (string) $advisor->id)>{{ $advisor->name }}</option>
                @endforeach
            </select>
            <select name="payment_status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Tất cả thanh toán</option>
                @foreach($paymentLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="scope" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all" @selected(request('scope', 'all') === 'all')>Tất cả phiếu</option>
                <option value="active" @selected(request('scope') === 'active')>Chỉ xe đang xử lý</option>
            </select>
            <div class="flex gap-2">
                <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white transition hover:bg-indigo-500">Lọc</button>
                <a href="{{ route('admin.workshop.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-500 transition hover:text-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white">Xóa</a>
            </div>
        </div>
    </form>

    <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <section class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <div class="flex flex-wrap gap-2">
                    @foreach($tabOrder as $status)
                        @php
                            $column = $columns[$status];
                            $count = ($groupedOrders[$status] ?? collect())->count();
                            $active = $defaultTab === $status;
                        @endphp
                        <button type="button" data-tab="{{ $status }}" class="workshop-tab inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-black transition {{ $active ? 'border-indigo-500 bg-indigo-600 text-white shadow-lg shadow-indigo-950/20' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300' }}">
                            <span class="h-2.5 w-2.5 rounded-full {{ $colorClasses[$column['color']] ?? '' }}"></span>
                            {{ $column['label'] }}
                            <span class="rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-black">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach($columns as $status => $column)
                @php
                    $columnOrders = $groupedOrders[$status] ?? collect();
                    $badgeClass = $colorClasses[$column['color']] ?? $colorClasses['blue'];
                    $active = $defaultTab === $status;
                @endphp
                <section data-panel="{{ $status }}" class="workshop-panel rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900/80 {{ $active ? '' : 'hidden' }}">
                    <div class="mb-4 rounded-2xl border px-4 py-4 {{ $badgeClass }}">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black">{{ $column['label'] }}</h2>
                                <p class="mt-1 text-sm font-medium opacity-80">{{ $column['hint'] }}</p>
                            </div>
                            <span class="rounded-full bg-white/60 px-3 py-1 text-xs font-black text-slate-700 dark:bg-black/20 dark:text-white">{{ $columnOrders->count() }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse($columnOrders as $order)
                            @php
                                $taskPercent = $order->tasks_count > 0 ? round(($order->completed_tasks_count / $order->tasks_count) * 100) : 0;
                                $isDueToday = $order->expected_completion_date?->isToday();
                                $isOverdue = $order->expected_completion_date && $order->expected_completion_date->isPast() && ! $order->expected_completion_date->isToday() && ! in_array($order->status, [\App\Models\RepairOrder::STATUS_COMPLETED, \App\Models\RepairOrder::STATUS_CANCELLED], true);
                            @endphp
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-950/70">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.repair_orders.show', $order) }}" class="block truncate text-base font-black text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300">
                                            {{ $order->track_id ?? '#'.$order->id }}
                                        </a>
                                        <div class="mt-1 text-xs font-semibold text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                        @if($isOverdue)
                                            <span class="rounded-full bg-rose-500/10 px-2 py-1 text-[10px] font-black text-rose-500">Trễ hẹn</span>
                                        @elseif($isDueToday)
                                            <span class="rounded-full bg-amber-500/10 px-2 py-1 text-[10px] font-black text-amber-500">Hôm nay</span>
                                        @endif
                                        @if(($order->payment_status ?? 'unpaid') === 'paid')
                                            <span class="rounded-full bg-emerald-500/10 px-2 py-1 text-[10px] font-black text-emerald-500">Đã thu</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="font-black text-slate-800 dark:text-slate-100">{{ $order->customer->name ?? 'Khách lẻ' }}</div>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                        <i class="fas fa-car w-4 text-indigo-400"></i>
                                        <span class="truncate">{{ $order->vehicle->license_plate ?? 'Chưa có biển số' }} - {{ $order->vehicle->model ?? 'Chưa rõ xe' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                        <i class="fas fa-user-tie w-4 text-indigo-400"></i>
                                        <span class="truncate">{{ $order->advisor->name ?? 'Chưa phân công cố vấn' }}</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="mb-1 flex items-center justify-between text-[11px] font-black text-slate-500">
                                        <span>Tiến độ công việc</span>
                                        <span>{{ $order->completed_tasks_count }}/{{ $order->tasks_count }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ $taskPercent }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <div class="font-bold text-slate-500">Tổng tiền</div>
                                        <div class="mt-1 font-black text-slate-900 dark:text-white">{{ number_format($order->total_amount ?? 0, 0, ',', '.') }}đ</div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <div class="font-bold text-slate-500">Thanh toán</div>
                                        <div class="mt-1 font-black {{ $order->payment_status === 'paid' ? 'text-emerald-500' : 'text-amber-500' }}">
                                            {{ $paymentLabels[$order->payment_status ?? 'unpaid'] ?? 'Chưa thanh toán' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('admin.repair_orders.show', $order) }}" class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-center text-xs font-black text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
                                        Chi tiết
                                    </a>
                                    @if(isset($nextStatusOptions[$order->status]))
                                        <details class="relative flex-1">
                                            <summary class="list-none cursor-pointer rounded-xl bg-slate-900 px-3 py-2 text-center text-xs font-black text-white transition hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                                                Chuyển
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                @foreach($nextStatusOptions[$order->status] as $nextStatus => $label)
                                                    <form method="POST" action="{{ route('admin.repair_orders.status', $order) }}" onsubmit="return confirm('Chuyển phiếu {{ $order->track_id }} sang trạng thái: {{ $label }}?');">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                                                        <button class="w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">{{ $label }}</button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-center text-sm font-bold text-slate-400 dark:border-slate-800 dark:bg-slate-900/40">
                                Chưa có xe ở bước này
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <div class="flex items-center justify-between">
                    <h2 class="font-black text-slate-900 dark:text-white">Lịch hẹn hôm nay</h2>
                    <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-black text-indigo-500">{{ $todayAppointments->count() }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($todayAppointments as $appointment)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-black text-slate-800 dark:text-slate-100">{{ $appointment->customer->name ?? 'Khách lẻ' }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500">{{ $appointment->vehicle->license_plate ?? $appointment->license_plate ?? 'Chưa có biển số' }}</div>
                                </div>
                                <div class="text-sm font-black text-indigo-500">{{ $appointment->scheduled_at->format('H:i') }}</div>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">{{ $appointment->service->name ?? $appointment->reason ?? 'Chưa rõ yêu cầu' }}</div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm font-bold text-slate-400 dark:border-slate-800">
                            Hôm nay chưa có lịch hẹn
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.workshop-tab').forEach((button) => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;
            document.querySelectorAll('.workshop-tab').forEach((item) => {
                const active = item.dataset.tab === tab;
                item.classList.toggle('bg-indigo-600', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('border-indigo-500', active);
                item.classList.toggle('shadow-lg', active);
                item.classList.toggle('shadow-indigo-950/20', active);
                item.classList.toggle('bg-slate-50', !active);
                item.classList.toggle('text-slate-600', !active);
                item.classList.toggle('border-slate-200', !active);
                item.classList.toggle('hover:border-indigo-300', !active);
                item.classList.toggle('hover:text-indigo-600', !active);
                item.classList.toggle('dark:bg-slate-950', !active);
                item.classList.toggle('dark:text-slate-300', !active);
                item.classList.toggle('dark:border-slate-800', !active);
            });

            document.querySelectorAll('.workshop-panel').forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.panel !== tab);
            });
        });
    });
</script>
@endpush
@endsection
