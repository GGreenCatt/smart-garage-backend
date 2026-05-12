@extends('layouts.customer')

@section('title', 'Chi tiết đơn #' . $order->track_id)

@php
    $statusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra',
        'pending_approval' => 'Chờ duyệt báo giá',
        'approved' => 'Đã duyệt báo giá',
        'completed' => $order->delivered_at ? 'Đã bàn giao' : 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $statusClasses = [
        'completed' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-200',
        'cancelled' => 'border-red-500/25 bg-red-500/10 text-red-200',
        'pending' => 'border-amber-500/25 bg-amber-500/10 text-amber-200',
        'pending_approval' => 'border-amber-500/25 bg-amber-500/10 text-amber-200',
        'approved' => 'border-cyan-500/25 bg-cyan-500/10 text-cyan-200',
        'in_progress' => 'border-cyan-500/25 bg-cyan-500/10 text-cyan-200',
    ];
    $paymentLabel = $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Thanh toán tại quầy';
    $paymentClass = $order->payment_status === 'paid' ? 'border-emerald-500/25 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/25 bg-amber-500/10 text-amber-100';
    $tasks = $order->tasks
        ->whereNull('parent_id')
        ->sortBy('created_at')
        ->values();
    $childTasks = $order->tasks
        ->whereNotNull('parent_id')
        ->sortBy('created_at')
        ->values();
    $displayTasks = $childTasks->isNotEmpty() ? $childTasks : $tasks;
    $standaloneItems = $order->items->whereNull('repair_task_id')->values();
    $itemsSubtotal = (float) $order->items->sum('subtotal');
    $laborSubtotal = (float) $displayTasks->sum(fn ($task) => (float) ($task->labor_cost ?? 0));
    $baseSubtotal = (float) ($order->subtotal ?: ($itemsSubtotal + $laborSubtotal));
    $totalAmount = (float) ($order->total_amount ?: max(0, $baseSubtotal - (float) ($order->discount_amount ?? 0)));
    $hasPublishedVhc = $order->vehicle_id && $order->vhcReport && $order->vhcReport->status === 'published';
    $vhcDefectCount = $hasPublishedVhc ? $order->vhcReport->defects->count() : 0;
@endphp

@section('content')
<main class="min-h-screen bg-[#0b1120] pt-24">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <a href="{{ route('customer.orders.index') }}" class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    Lịch sử sửa chữa
                </a>
                <h1 class="text-2xl font-black text-white md:text-3xl">Chi tiết đơn sửa chữa</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-black text-cyan-300">{{ $order->track_id }}</span>
                    <span class="rounded-full border px-3 py-1 text-[11px] font-black uppercase {{ $statusClasses[$order->status] ?? 'border-slate-500/25 bg-slate-500/10 text-slate-200' }}">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                    <span class="rounded-full border px-3 py-1 text-[11px] font-black uppercase {{ $paymentClass }}">
                        {{ $paymentLabel }}
                    </span>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 px-5 py-4 md:text-right">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Tổng cần thanh toán</div>
                <div class="mt-1 text-3xl font-black text-white">{{ number_format($totalAmount) }}đ</div>
                @if($order->delivered_at)
                    <div class="mt-1 text-xs font-semibold text-emerald-300">Bàn giao {{ $order->delivered_at->format('H:i d/m/Y') }}</div>
                @elseif($order->payment_status === 'paid')
                    <div class="mt-1 text-xs font-semibold text-cyan-300">Đã thanh toán, chờ bàn giao xe</div>
                @endif
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <div class="text-xs font-black uppercase text-slate-500">Xe</div>
                <div class="mt-2 text-lg font-black text-white">{{ $order->vehicle->model ?? 'Xe' }}</div>
                <div class="mt-1 font-mono text-sm font-bold text-cyan-300">{{ $order->vehicle->license_plate ?? 'Chưa rõ biển số' }}</div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <div class="text-xs font-black uppercase text-slate-500">Cố vấn dịch vụ</div>
                <div class="mt-2 text-lg font-black text-white">{{ $order->advisor->name ?? 'Garage' }}</div>
                <div class="mt-1 text-sm text-slate-400">{{ $order->created_at->format('H:i d/m/Y') }}</div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <div class="text-xs font-black uppercase text-slate-500">Dự kiến hoàn tất</div>
                <div class="mt-2 text-lg font-black text-white">{{ $order->expected_completion_date?->format('H:i d/m/Y') ?? 'Garage sẽ cập nhật' }}</div>
                <div class="mt-1 text-sm text-slate-400">{{ $order->payment_method ? 'Thanh toán: ' . strtoupper($order->payment_method) : 'Chưa ghi nhận phương thức' }}</div>
            </div>
        </section>

        @if($hasPublishedVhc)
            <section class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/70">
                <div class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-white">Mô hình 3D lỗi đã ghi nhận</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $vhcDefectCount }} điểm lỗi trong lần kiểm tra của đơn {{ $order->track_id }}.</p>
                    </div>
                    <a href="{{ route('customer.vehicle.3d', ['id' => $order->vehicle_id, 'order_id' => $order->id, 'fullscreen' => 1]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-xs font-black text-white transition hover:bg-cyan-500">
                        <i class="fas fa-up-right-and-down-left-from-center"></i>
                        Mở toàn màn hình
                    </a>
                </div>
                <div class="h-[34rem] bg-slate-950">
                    <iframe
                        src="{{ route('customer.vehicle.3d', ['id' => $order->vehicle_id, 'order_id' => $order->id, 'iframe' => 1]) }}"
                        class="h-full w-full border-0"
                        loading="lazy"
                        title="Mô hình 3D lỗi đã ghi nhận của {{ $order->track_id }}"
                    ></iframe>
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="flex flex-col gap-2 border-b border-slate-800 pb-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-white">Các việc đã làm</h2>
                    <p class="mt-1 text-sm text-slate-400">Bao gồm công sửa chữa, vật tư/phụ tùng và trạng thái từng hạng mục.</p>
                </div>
                <div class="text-sm font-bold text-slate-300">{{ $displayTasks->count() }} hạng mục</div>
            </div>

            <div class="mt-4 space-y-4">
                @forelse($displayTasks as $task)
                    @php
                        $taskItems = $task->items;
                        $taskLabor = (float) ($task->labor_cost ?? 0);
                        $taskItemsTotal = (float) $taskItems->sum('subtotal');
                        $taskTotal = $taskLabor + $taskItemsTotal;
                        $approvalLabels = [
                            'approved' => 'Khách đã duyệt',
                            'rejected' => 'Khách từ chối',
                            'pending' => 'Chờ khách duyệt',
                        ];
                        $taskStatusLabels = [
                            'completed' => 'Đã hoàn thành',
                            'in_progress' => 'Đang thực hiện',
                            'pending' => 'Chưa thực hiện',
                        ];
                    @endphp
                    <article class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-white">{{ $task->title }}</h3>
                                    <span class="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">
                                        {{ $taskStatusLabels[$task->status] ?? $task->status }}
                                    </span>
                                    @if($task->customer_approval_status)
                                        <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-[10px] font-black uppercase text-cyan-200">
                                            {{ $approvalLabels[$task->customer_approval_status] ?? $task->customer_approval_status }}
                                        </span>
                                    @endif
                                </div>
                                @if($task->description)
                                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $task->description }}</p>
                                @endif
                                @if($task->mechanic)
                                    <div class="mt-2 text-xs font-semibold text-slate-500">Kỹ thuật viên: {{ $task->mechanic->name }}</div>
                                @endif
                            </div>
                            <div class="shrink-0 rounded-xl bg-slate-900 px-4 py-3 md:text-right">
                                <div class="text-xs font-bold uppercase text-slate-500">Thành tiền</div>
                                <div class="mt-1 text-lg font-black text-white">{{ number_format($taskTotal) }}đ</div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Công sửa chữa</span>
                                    <span class="font-bold text-white">{{ number_format($taskLabor) }}đ</span>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Vật tư/phụ tùng</span>
                                    <span class="font-bold text-white">{{ number_format($taskItemsTotal) }}đ</span>
                                </div>
                            </div>
                        </div>

                        @if($taskItems->isNotEmpty())
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-800">
                                <div class="grid grid-cols-12 bg-slate-900 px-3 py-2 text-[11px] font-black uppercase text-slate-500">
                                    <div class="col-span-6">Vật tư</div>
                                    <div class="col-span-2 text-right">SL</div>
                                    <div class="col-span-2 text-right">Đơn giá</div>
                                    <div class="col-span-2 text-right">Tiền</div>
                                </div>
                                @foreach($taskItems as $item)
                                    <div class="grid grid-cols-12 border-t border-slate-800 px-3 py-3 text-sm">
                                        <div class="col-span-6 font-semibold text-white">{{ $item->name ?? $item->itemable?->name ?? 'Vật tư' }}</div>
                                        <div class="col-span-2 text-right text-slate-300">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</div>
                                        <div class="col-span-2 text-right text-slate-300">{{ number_format((float) $item->unit_price) }}đ</div>
                                        <div class="col-span-2 text-right font-bold text-white">{{ number_format((float) $item->subtotal) }}đ</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-700 p-8 text-center text-slate-400">
                        <i class="fas fa-list-check mb-3 text-3xl opacity-40"></i>
                        <div class="font-bold text-white">Chưa có hạng mục công việc chi tiết</div>
                        <p class="mt-1 text-sm">Garage sẽ cập nhật công việc và vật tư khi xử lý xe.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if($standaloneItems->isNotEmpty())
            <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-black text-white">Chi phí khác</h2>
                <div class="mt-4 space-y-3">
                    @foreach($standaloneItems as $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-950/50 p-3 text-sm">
                            <div>
                                <div class="font-bold text-white">{{ $item->name ?? $item->itemable?->name ?? 'Hạng mục' }}</div>
                                <div class="text-xs text-slate-500">Số lượng {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} x {{ number_format((float) $item->unit_price) }}đ</div>
                            </div>
                            <div class="font-black text-white">{{ number_format((float) $item->subtotal) }}đ</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-black text-white">Lịch sử sửa chữa của xe</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $order->vehicle->license_plate ?? 'Xe này' }} trong các lần ghé garage trước đây.</p>

                <div class="mt-4 space-y-3">
                    @forelse($vehicleRepairHistory as $history)
                        @php
                            $historyItemsTotal = (float) $history->items->sum('subtotal');
                            $historyLaborTotal = (float) $history->tasks->sum(fn ($task) => (float) ($task->labor_cost ?? 0));
                            $historyTotal = (float) ($history->total_amount ?: $history->subtotal ?: ($historyItemsTotal + $historyLaborTotal));
                            $historyDoneTasks = $history->tasks->where('status', 'completed')->count();
                        @endphp
                        <a href="{{ route('customer.orders.show', $history->id) }}" class="block rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-cyan-500/40">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-xs font-black text-cyan-300">{{ $history->track_id }}</span>
                                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-black uppercase {{ $statusClasses[$history->status] ?? 'border-slate-500/25 bg-slate-500/10 text-slate-200' }}">
                                            {{ $statusLabels[$history->status] ?? $history->status }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-sm font-bold text-white">{{ $history->created_at->format('d/m/Y') }} · {{ $history->advisor->name ?? 'Garage' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $historyDoneTasks }}/{{ $history->tasks->count() }} việc hoàn thành · {{ $history->items->count() }} vật tư</div>
                                </div>
                                <div class="font-black text-white md:text-right">{{ number_format($historyTotal) }}đ</div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-700 p-6 text-center text-slate-400">
                            <div class="font-bold text-white">Chưa có lần sửa trước</div>
                            <p class="mt-1 text-sm">Đây là đơn đầu tiên được ghi nhận cho xe này.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <h2 class="text-lg font-black text-white">Quyết toán</h2>
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm text-slate-400">
                            <span>Công sửa chữa</span>
                            <span>{{ number_format($laborSubtotal) }}đ</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-400">
                            <span>Vật tư/phụ tùng</span>
                            <span>{{ number_format($itemsSubtotal) }}đ</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-800 pt-3 text-sm text-slate-400">
                            <span>Tạm tính</span>
                            <span>{{ number_format($baseSubtotal) }}đ</span>
                        </div>
                        @if($order->promotion || (float) ($order->discount_amount ?? 0) > 0)
                            <div class="flex justify-between text-sm font-bold text-emerald-300">
                                <span>Giảm giá {{ $order->promotion?->code ? '(' . $order->promotion->code . ')' : '' }}</span>
                                <span>-{{ number_format((float) ($order->discount_amount ?? 0)) }}đ</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-800 pt-3 text-base font-black text-white">
                            <span>Tổng cộng</span>
                            <span>{{ number_format($totalAmount) }}đ</span>
                        </div>
                    </div>
                </section>

                @if($order->payment_status !== 'paid')
                    <section class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5 text-amber-100">
                        <div class="flex gap-3">
                            <i class="fas fa-circle-info mt-0.5 text-amber-300"></i>
                            <div>
                                <h3 class="font-bold text-white">Thanh toán tại quầy</h3>
                                <p class="mt-1 text-sm text-amber-100/80">Vui lòng đọc hoặc gửi mã giảm giá cho nhân viên khi thanh toán. Nhân viên sẽ áp mã và xác nhận thanh toán trên hệ thống.</p>
                            </div>
                        </div>
                    </section>
                @endif
            </aside>
        </section>
    </div>
</main>
@endsection
