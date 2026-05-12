@extends('layouts.admin')

@section('title', 'Chi Tiết Doanh Thu')

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . 'đ';
    $paymentLabels = [
        'paid' => 'Đã thanh toán',
        'partial' => 'Thanh toán một phần',
        'unpaid' => 'Chưa thanh toán',
    ];
    $paymentClass = $repairOrder->payment_status === 'paid'
        ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
        : 'border-amber-500/20 bg-amber-500/10 text-amber-300';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-black text-white">{{ $repairOrder->track_id }}</h2>
                <span class="rounded-full border px-3 py-1 text-xs font-black {{ $paymentClass }}">{{ $paymentLabels[$repairOrder->payment_status] ?? 'Chưa thanh toán' }}</span>
            </div>
            <p class="mt-2 text-sm text-slate-400">
                Thu lúc {{ $repairOrder->updated_at?->format('H:i d/m/Y') }} · Cố vấn {{ $repairOrder->advisor->name ?? 'Chưa rõ' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.repair_orders.show', $repairOrder) }}" class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:bg-slate-700">Mở phiếu sửa</a>
            <a href="{{ route('admin.revenue.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-500">Quay lại doanh thu</a>
        </div>
    </div>

    <section class="grid gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 lg:col-span-2">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Tổng đã thu</div>
            <div class="mt-2 text-3xl font-black text-white">{{ $money($repairOrder->total_amount) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Vật tư / dịch vụ</div>
            <div class="mt-2 text-2xl font-black text-white">{{ $money($itemsTotal) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Công sửa</div>
            <div class="mt-2 text-2xl font-black text-white">{{ $money($laborTotal) }}</div>
        </div>
    </section>

    @if($rejectedTasks->isNotEmpty() || $rejectedItems->isNotEmpty())
        <section class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-amber-200">Khoản khách từ chối</h3>
                    <p class="mt-1 text-sm text-amber-100/70">Các khoản này chỉ dùng để đối chiếu, không cộng vào doanh thu đã thu.</p>
                </div>
                <div class="font-mono text-xl font-black text-amber-100">{{ $money($rejectedTotal) }}</div>
            </div>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70 xl:col-span-2">
            <div class="border-b border-slate-800 p-6">
                <h3 class="text-lg font-black text-white">Chi tiết các khoản</h3>
                <p class="mt-1 text-sm text-slate-500">Gồm dịch vụ, vật tư và công sửa gắn với đợt sửa này.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-800 bg-slate-950/50 text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Khoản</th>
                            <th class="px-6 py-4">Nhóm</th>
                            <th class="px-6 py-4 text-center">SL</th>
                            <th class="px-6 py-4 text-right">Đơn giá</th>
                            <th class="px-6 py-4 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($billableItems as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $item->itemable->name ?? $item->name ?? 'Khoản phát sinh' }}</div>
                                    @if($item->repairTask)
                                        <div class="mt-1 text-xs text-slate-500">Thuộc việc: {{ $item->repairTask->title }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    {{ $item->itemable_type === \App\Models\Service::class ? 'Dịch vụ' : ($item->itemable_type === \App\Models\Part::class ? 'Vật tư' : 'Khoản khác') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-white">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right font-mono text-slate-300">{{ $money($item->unit_price) }}</td>
                                <td class="px-6 py-4 text-right font-mono font-black text-white">{{ $money($item->subtotal) }}</td>
                            </tr>
                        @endforeach

                        @foreach($billableTasks->where('labor_cost', '>', 0) as $task)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $task->title }}</div>
                                    @if($task->description)
                                        <div class="mt-1 text-xs text-slate-500">{{ $task->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-400">Công sửa</td>
                                <td class="px-6 py-4 text-center font-mono text-white">1</td>
                                <td class="px-6 py-4 text-right font-mono text-slate-300">{{ $money($task->labor_cost) }}</td>
                                <td class="px-6 py-4 text-right font-mono font-black text-white">{{ $money($task->labor_cost) }}</td>
                            </tr>
                        @endforeach

                        @if($billableItems->isEmpty() && $billableTasks->where('labor_cost', '>', 0)->isEmpty())
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">Phiếu này chưa có khoản chi tiết.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        @if($rejectedTasks->isNotEmpty() || $rejectedItems->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-slate-900/70 xl:col-span-2">
                <div class="border-b border-amber-500/20 p-6">
                    <h3 class="text-lg font-black text-amber-200">Hạng mục bị từ chối</h3>
                    <p class="mt-1 text-sm text-slate-500">Không tính vào tổng thu của phiếu.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-800 bg-slate-950/50 text-xs font-black uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Khoản</th>
                                <th class="px-6 py-4">Nhóm</th>
                                <th class="px-6 py-4 text-center">SL</th>
                                <th class="px-6 py-4 text-right">Đơn giá</th>
                                <th class="px-6 py-4 text-right">Giá trị bị từ chối</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($rejectedItems as $item)
                                <tr class="bg-amber-500/[0.03]">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white">{{ $item->itemable->name ?? $item->name ?? 'Khoản phát sinh' }}</div>
                                        @if($item->repairTask)
                                            <div class="mt-1 text-xs text-slate-500">Thuộc việc: {{ $item->repairTask->title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">
                                        {{ $item->itemable_type === \App\Models\Service::class ? 'Dịch vụ' : ($item->itemable_type === \App\Models\Part::class ? 'Vật tư' : 'Khoản khác') }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono text-white">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right font-mono text-slate-300">{{ $money($item->unit_price) }}</td>
                                    <td class="px-6 py-4 text-right font-mono font-black text-amber-200">{{ $money($item->subtotal) }}</td>
                                </tr>
                            @endforeach

                            @foreach($rejectedTasks->where('labor_cost', '>', 0) as $task)
                                <tr class="bg-amber-500/[0.03]">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white">{{ $task->title }}</div>
                                        @if($task->description)
                                            <div class="mt-1 text-xs text-slate-500">{{ $task->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">Công sửa</td>
                                    <td class="px-6 py-4 text-center font-mono text-white">1</td>
                                    <td class="px-6 py-4 text-right font-mono text-slate-300">{{ $money($task->labor_cost) }}</td>
                                    <td class="px-6 py-4 text-right font-mono font-black text-amber-200">{{ $money($task->labor_cost) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <aside class="space-y-6 xl:col-start-3 xl:row-start-1">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                <h3 class="text-sm font-black uppercase tracking-wider text-indigo-300">Tổng hợp</h3>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Tạm tính</span>
                        <span class="font-mono font-bold text-white">{{ $money($repairOrder->subtotal ?? $itemsTotal + $laborTotal) }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Giảm giá</span>
                        <span class="font-mono font-bold text-emerald-300">-{{ $money($repairOrder->discount_amount ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Thuế / phí</span>
                        <span class="font-mono font-bold text-white">{{ $money($repairOrder->tax_amount ?? 0) }}</span>
                    </div>
                    @if($repairOrder->promotion)
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Mã khuyến mãi</span>
                            <span class="font-bold text-indigo-300">{{ $repairOrder->promotion->code }}</span>
                        </div>
                    @endif
                    <div class="border-t border-slate-800 pt-3">
                        <div class="flex justify-between gap-4">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Tổng thu</span>
                            <span class="font-mono text-xl font-black text-indigo-300">{{ $money($repairOrder->total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                <h3 class="text-sm font-black uppercase tracking-wider text-indigo-300">Khách và xe</h3>
                <div class="mt-4">
                    <div class="font-bold text-white">{{ $repairOrder->customer->name ?? 'Khách vãng lai' }}</div>
                    <div class="text-sm text-slate-500">{{ $repairOrder->customer->phone ?? 'Chưa có SĐT' }}</div>
                </div>
                <div class="mt-5 border-t border-slate-800 pt-4">
                    <div class="font-bold text-white">{{ trim(($repairOrder->vehicle->make ?? '').' '.($repairOrder->vehicle->model ?? '')) ?: 'Chưa rõ xe' }}</div>
                    <div class="mt-1 inline-flex rounded bg-blue-500/10 px-2 py-0.5 font-mono text-xs font-bold text-blue-300">{{ $repairOrder->vehicle->license_plate ?? 'Chưa có biển số' }}</div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
