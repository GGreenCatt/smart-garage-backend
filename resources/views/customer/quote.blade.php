@extends('layouts.customer')

@section('title', 'Chi tiết báo giá sửa chữa')

@php
    $statusLabels = [
        'pending_approval' => 'Chờ bạn duyệt',
        'approved' => 'Đã duyệt',
        'in_progress' => 'Đang sửa chữa',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $severityLabels = [
        'low' => 'Nhẹ',
        'minor' => 'Nhẹ',
        'medium' => 'Cần lưu ý',
        'high' => 'Nghiêm trọng',
        'critical' => 'Rất nghiêm trọng',
    ];
    $severityClasses = [
        'low' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
        'minor' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
        'medium' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
        'high' => 'bg-red-500/10 text-red-300 border-red-500/30',
        'critical' => 'bg-red-500/10 text-red-300 border-red-500/30',
    ];
    $parentTasks = $order->tasks->whereNull('parent_id');
    $quoteTasks = $order->tasks
        ->whereNotNull('parent_id')
        ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty());
    $quoteTotal = $quoteTasks->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal'));
    $approvedTotal = $quoteTasks
        ->where('customer_approval_status', 'approved')
        ->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal'));
    $initialTotal = $order->status === 'pending_approval' ? $quoteTotal : $approvedTotal;
    $canReview = $order->status === 'pending_approval';
    $hasPublishedVhc = $order->include_vhc && $order->vhcReport && $order->vhcReport->status === 'published';
    $backUrl = request()->routeIs('guest.*') ? route('home') : route('customer.dashboard');
@endphp

@section('content')
<main class="min-h-screen bg-[#0b1120] pt-20 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <section class="rounded-2xl border border-slate-800 bg-slate-900 overflow-hidden">
            <div class="p-5 md:p-7 border-b border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950/50">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                    <div class="min-w-0">
                        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-white mb-5">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-xs font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-300 border border-indigo-500/30 rounded-full px-3 py-1">
                                Phiếu #{{ $order->id }}
                            </span>
                            <span class="text-xs text-slate-500">
                                Gửi lúc {{ $order->quote_sent_at?->format('H:i d/m/Y') ?? $order->created_at?->format('d/m/Y') }}
                            </span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-black text-white">Phiếu báo giá sửa chữa</h1>
                        <p class="text-slate-400 mt-2">
                            {{ $order->vehicle->model ?? 'Xe của bạn' }}
                            <span class="text-slate-600 mx-2">|</span>
                            <span class="font-bold text-slate-200">{{ $order->vehicle->license_plate ?? 'Chưa có biển số' }}</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 w-full lg:w-auto lg:min-w-[420px]">
                        <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-4">
                            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-black mb-1">Trạng thái</div>
                            <div class="text-sm font-black {{ $canReview ? 'text-amber-300' : 'text-emerald-300' }}">{{ $statusLabels[$order->status] ?? $order->status }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-4">
                            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-black mb-1">Hạng mục</div>
                            <div class="text-xl font-black text-white">{{ $quoteTasks->count() }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-4 col-span-2 sm:col-span-1">
                            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-black mb-1">Tổng báo giá</div>
                            <div class="text-xl font-black text-amber-400">{{ number_format($quoteTotal) }}đ</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($canReview)
                <div class="px-5 md:px-7 py-4 bg-amber-500/10 border-b border-amber-500/20 text-amber-100 text-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-circle-info text-amber-300 mt-0.5"></i>
                        <span>Vui lòng xem kỹ từng hạng mục. Garage chỉ thi công những hạng mục bạn chọn đồng ý.</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAllTasks('approved')" class="px-3 py-2 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 text-xs font-black">
                            Chọn tất cả
                        </button>
                        <button type="button" onclick="setAllTasks('rejected')" class="px-3 py-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-200 text-xs font-black">
                            Từ chối tất cả
                        </button>
                    </div>
                </div>
            @endif
        </section>

        <form id="approvalForm" onsubmit="event.preventDefault(); submitApproval();">
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
                <div class="space-y-6">
                    @if($hasPublishedVhc)
                        @php
                            $threeDParams = ['id' => $order->vehicle_id, 'readonly' => 1, 'order_id' => $order->id];
                            $threeDUrl = request()->routeIs('guest.*')
                                ? URL::signedRoute('guest.vehicle.3d', $threeDParams)
                                : route('customer.vehicle.3d', $threeDParams);
                        @endphp
                        <section class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="font-black text-white flex items-center gap-2"><i class="fas fa-cube text-indigo-400"></i> Kiểm tra 3D/VHC</h2>
                                    <p class="text-xs text-slate-500 mt-1">Các điểm lỗi 3D đã được công bố cùng phiếu báo giá này.</p>
                                </div>
                                <a href="{!! $threeDUrl !!}" target="_blank" class="text-xs font-bold text-indigo-300 hover:text-indigo-200 border border-indigo-500/30 rounded-lg px-3 py-2">
                                    Mở toàn màn hình
                                </a>
                            </div>
                            <iframe src="{!! $threeDUrl !!}" class="w-full h-[420px] border-0"></iframe>
                        </section>
                    @endif

                    <section class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div>
                                <h2 class="font-black text-white flex items-center gap-2"><i class="fas fa-file-invoice-dollar text-amber-400"></i> Hạng mục báo giá</h2>
                                <p class="text-xs text-slate-500 mt-1">Mỗi dòng bên dưới là một đề xuất sửa chữa có chi phí riêng.</p>
                            </div>
                            <div class="text-xs text-slate-500">{{ $quoteTasks->count() }} hạng mục cần phản hồi</div>
                        </div>

                        <div class="p-4 md:p-5 space-y-5">
                            @if($quoteTasks->isEmpty())
                                <div class="rounded-xl border border-dashed border-slate-700 p-8 text-center text-slate-500">
                                    <i class="fas fa-file-circle-exclamation text-3xl mb-3 opacity-40"></i>
                                    <p>Phiếu này chưa có hạng mục báo giá chi tiết.</p>
                                </div>
                            @else
                                @foreach($parentTasks as $parentTask)
                                    @php
                                        $children = $parentTask->children
                                            ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty());
                                    @endphp
                                    @continue($children->isEmpty())

                                    <div class="rounded-xl border border-slate-800 overflow-hidden">
                                        <div class="bg-slate-950/60 px-4 py-3 flex items-center justify-between gap-3">
                                            <h3 class="font-bold text-slate-100">{{ str_replace(' (VHC)', '', $parentTask->title) }}</h3>
                                            <span class="text-[10px] uppercase font-black text-slate-400 border border-slate-700 rounded px-2 py-1">
                                                {{ $parentTask->type === 'vhc' ? 'Kiểm tra 3D' : 'Dịch vụ' }}
                                            </span>
                                        </div>

                                        <div class="divide-y divide-slate-800">
                                            @foreach($children as $task)
                                                @php
                                                    $partCost = $task->items->sum('subtotal');
                                                    $rowTotal = (float) ($task->labor_cost ?? 0) + $partCost;
                                                    $severity = strtolower($task->severity ?? 'medium');
                                                    $severityClass = $severityClasses[$severity] ?? $severityClasses['medium'];
                                                @endphp
                                                <div class="task-row p-4 md:p-5 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_230px] gap-5" data-task-id="{{ $task->id }}" data-cost="{{ $rowTotal }}">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                                            <h4 class="font-black text-white">{{ $task->title }}</h4>
                                                            <span class="text-[10px] uppercase font-black rounded-full px-2 py-0.5 border {{ $severityClass }}">
                                                                {{ $severityLabels[$severity] ?? 'Cần lưu ý' }}
                                                            </span>
                                                        </div>
                                                        @if($task->description)
                                                            <p class="text-sm text-slate-400 leading-relaxed">{!! nl2br(e($task->description)) !!}</p>
                                                        @endif

                                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            @if((float) ($task->labor_cost ?? 0) > 0)
                                                                <div class="flex justify-between gap-3 text-xs rounded-lg bg-slate-950/60 border border-slate-800 px-3 py-2">
                                                                    <span class="text-slate-300">Công sửa chữa</span>
                                                                    <span class="text-slate-400 font-mono">{{ number_format($task->labor_cost) }}đ</span>
                                                                </div>
                                                            @endif
                                                            @foreach($task->items as $item)
                                                                <div class="flex justify-between gap-3 text-xs rounded-lg bg-slate-950/60 border border-slate-800 px-3 py-2">
                                                                    <span class="text-slate-300">{{ $item->name }} x{{ $item->quantity }}</span>
                                                                    <span class="text-slate-400 font-mono">{{ number_format($item->subtotal) }}đ</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="flex md:flex-col items-center md:items-end justify-between gap-3">
                                                        <div class="text-right">
                                                            <div class="text-xs text-slate-500">Thành tiền</div>
                                                            <div class="font-black text-2xl text-white">{{ number_format($rowTotal) }}đ</div>
                                                        </div>

                                                        @if($canReview)
                                                            <div class="inline-flex bg-slate-950 border border-slate-800 p-1 rounded-xl">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="tasks[{{ $task->id }}]" value="approved" class="peer sr-only task-radio" checked onchange="calculateTotal()">
                                                                    <span class="block px-4 py-2 rounded-lg text-sm font-bold text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white">Đồng ý</span>
                                                                </label>
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="tasks[{{ $task->id }}]" value="rejected" class="peer sr-only task-radio" onchange="calculateTotal()">
                                                                    <span class="block px-4 py-2 rounded-lg text-sm font-bold text-slate-400 peer-checked:bg-red-500 peer-checked:text-white">Từ chối</span>
                                                                </label>
                                                            </div>
                                                        @else
                                                            @if($task->customer_approval_status === 'approved')
                                                                <span class="text-xs font-black uppercase rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 px-3 py-2">Đã đồng ý</span>
                                                            @elseif($task->customer_approval_status === 'rejected')
                                                                <span class="text-xs font-black uppercase rounded-lg bg-red-500/10 text-red-300 border border-red-500/30 px-3 py-2">Đã từ chối</span>
                                                            @else
                                                                <span class="text-xs font-black uppercase rounded-lg bg-slate-800 text-slate-400 px-3 py-2">Chưa phản hồi</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </section>
                </div>

                <aside class="lg:sticky lg:top-24">
                    <div class="bg-indigo-600 rounded-2xl border border-indigo-400/30 shadow-xl shadow-indigo-950/30 overflow-hidden">
                        <div class="p-6 space-y-5">
                            <div>
                                <div class="text-xs uppercase tracking-widest font-black text-indigo-200 mb-2">Tổng tiền bạn đang chọn</div>
                                <div class="flex items-end gap-2">
                                    <span id="totalCost" class="text-4xl font-black text-white">{{ number_format($initialTotal) }}</span>
                                    <span class="text-indigo-200 font-bold mb-1">đ</span>
                                </div>
                                <p class="text-xs text-indigo-100/80 mt-2">Tổng này tự cập nhật khi bạn đồng ý hoặc từ chối từng hạng mục.</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-white/10 border border-white/10 p-3">
                                    <div class="text-[10px] uppercase text-indigo-200 font-bold">Đồng ý</div>
                                    <div id="approvedCount" class="text-xl font-black text-white">0</div>
                                </div>
                                <div class="rounded-xl bg-white/10 border border-white/10 p-3">
                                    <div class="text-[10px] uppercase text-indigo-200 font-bold">Từ chối</div>
                                    <div id="rejectedCount" class="text-xl font-black text-white">0</div>
                                </div>
                            </div>

                            <div class="rounded-xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[10px] uppercase text-indigo-200 font-bold mb-1">3D/VHC</div>
                                <div class="text-sm font-black {{ $hasPublishedVhc ? 'text-emerald-200' : 'text-indigo-200' }}">
                                    {{ $hasPublishedVhc ? 'Có dữ liệu kiểm tra 3D' : 'Không đính kèm dữ liệu 3D' }}
                                </div>
                            </div>

                            @if($canReview)
                                <div>
                                    <label for="customerNote" class="block text-sm font-bold text-indigo-100 mb-2">Ghi chú cho garage</label>
                                    <textarea id="customerNote" rows="3" class="w-full bg-white/10 border border-indigo-300/30 rounded-xl p-3 text-white placeholder-indigo-200/60 focus:ring-2 focus:ring-white/30 focus:border-white/50 text-sm resize-none" placeholder="Ví dụ: Tôi muốn trao đổi thêm về hạng mục..."></textarea>
                                </div>
                                <button id="submitApprovalButton" type="submit" class="w-full bg-white hover:bg-indigo-50 text-indigo-700 px-5 py-4 rounded-xl font-black text-base transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" {{ $quoteTasks->isEmpty() ? 'disabled' : '' }}>
                                    Gửi phản hồi <i class="fas fa-arrow-right"></i>
                                </button>
                                <p class="text-xs text-indigo-100/80 text-center">Garage chỉ thi công các hạng mục bạn đồng ý.</p>
                            @else
                                <div class="rounded-xl bg-indigo-950/40 border border-indigo-400/20 text-indigo-100 px-4 py-3 text-center font-bold">
                                    Phiếu báo giá đã được phản hồi
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
    function calculateTotal() {
        let total = 0;
        let approved = 0;
        let rejected = 0;

        document.querySelectorAll('.task-row').forEach(row => {
            const radio = row.querySelector('input[type="radio"]:checked');
            if (radio && radio.value === 'approved') {
                approved++;
                total += parseFloat(row.getAttribute('data-cost') || 0);
            } else if (radio && radio.value === 'rejected') {
                rejected++;
            } else if (!row.querySelector('input[type="radio"]')) {
                const approvedBadge = row.querySelector('.bg-emerald-500\\/10');
                const rejectedBadge = row.querySelector('.bg-red-500\\/10');
                if (approvedBadge) {
                    approved++;
                    total += parseFloat(row.getAttribute('data-cost') || 0);
                } else if (rejectedBadge) {
                    rejected++;
                }
            }
        });

        document.getElementById('totalCost').innerText = new Intl.NumberFormat('vi-VN').format(total);
        const approvedCount = document.getElementById('approvedCount');
        const rejectedCount = document.getElementById('rejectedCount');
        if (approvedCount) approvedCount.innerText = approved;
        if (rejectedCount) rejectedCount.innerText = rejected;
    }

    function setAllTasks(status) {
        document.querySelectorAll(`.task-radio[value="${status}"]`).forEach(input => {
            input.checked = true;
        });
        calculateTotal();
    }

    document.addEventListener('DOMContentLoaded', calculateTotal);

    @if($canReview)
    function submitApproval() {
        const formData = new FormData(document.getElementById('approvalForm'));
        const tasks = [];

        document.querySelectorAll('.task-row').forEach(row => {
            const taskId = row.getAttribute('data-task-id');
            const status = formData.get(`tasks[${taskId}]`);
            if (status) tasks.push({ id: taskId, status });
        });

        if (tasks.length === 0) {
            Swal.fire('Chưa có hạng mục', 'Phiếu này chưa có hạng mục nào để phản hồi.', 'warning');
            return;
        }

        const approvedCount = tasks.filter(task => task.status === 'approved').length;
        const rejectedCount = tasks.filter(task => task.status === 'rejected').length;

        Swal.fire({
            title: 'Xác nhận phản hồi báo giá?',
            html: `Bạn đồng ý <b>${approvedCount}</b> hạng mục và từ chối <b>${rejectedCount}</b> hạng mục.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Gửi phản hồi',
            cancelButtonText: 'Xem lại'
        }).then((result) => {
            if (!result.isConfirmed) return;

            @php
                $actionUrl = request()->routeIs('guest.*')
                    ? URL::signedRoute('guest.quote.action', $order->id)
                    : route('customer.quote.action', $order->id);
            @endphp

            const button = document.getElementById('submitApprovalButton');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            }

            fetch(`{!! $actionUrl !!}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tasks,
                    customer_note: document.getElementById('customerNote')?.value || null
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Không thể gửi phản hồi.');
                return data;
            })
            .then(() => {
                Swal.fire('Đã gửi phản hồi', 'Garage đã nhận quyết định của bạn.', 'success')
                    .then(() => window.location.reload());
            })
            .catch(error => {
                Swal.fire('Lỗi', error.message || 'Không thể gửi phản hồi. Vui lòng thử lại.', 'error');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = 'Gửi phản hồi <i class="fas fa-arrow-right"></i>';
                }
            });
        });
    }
    @endif
</script>
@endpush
