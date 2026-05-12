@extends('layouts.admin')

@section('title', 'Doanh Thu')

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . 'đ';
    $paymentLabels = [
        'paid' => 'Đã thanh toán',
        'partial' => 'Thanh toán một phần',
        'unpaid' => 'Chưa thanh toán',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-indigo-300">Báo cáo</p>
            <h2 class="mt-2 text-2xl font-black text-white">Doanh thu theo đợt sửa</h2>
            <p class="mt-2 text-sm text-slate-400">Chỉ ghi nhận các phiếu sửa đã thanh toán. Bấm vào mã phiếu để xem chi tiết từng khoản.</p>
        </div>

        <form method="GET" action="{{ route('admin.revenue.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Từ ngày</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Đến ngày</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-white">
            </div>
            <button class="self-end rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white transition hover:bg-indigo-500">Lọc</button>
        </form>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Doanh thu đã thu</div>
            <div class="mt-2 text-2xl font-black text-white">{{ $money($stats['total_revenue']) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Số đợt sửa</div>
            <div class="mt-2 text-2xl font-black text-white">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Trung bình/đợt</div>
            <div class="mt-2 text-2xl font-black text-white">{{ $money($stats['average_order']) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="text-xs font-black uppercase tracking-wider text-slate-500">Chờ thu</div>
            <div class="mt-2 text-2xl font-black text-amber-300">{{ $money($stats['pending_revenue']) }}</div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
        <div class="border-b border-slate-800 p-6">
            <h3 class="text-lg font-black text-white">Các đợt sửa đã thanh toán</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-950/50 text-xs font-black uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Đợt sửa</th>
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4">Xe</th>
                        <th class="px-6 py-4">Thanh toán</th>
                        <th class="px-6 py-4 text-right">Doanh thu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($repairOrders as $repairOrder)
                        <tr class="cursor-pointer transition hover:bg-slate-800/50" onclick="window.location='{{ route('admin.revenue.show', $repairOrder) }}'">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.revenue.show', $repairOrder) }}" class="font-black text-indigo-300 hover:text-indigo-200">{{ $repairOrder->track_id }}</a>
                                <div class="mt-1 text-xs text-slate-500">{{ $repairOrder->updated_at?->format('H:i d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $repairOrder->customer->name ?? 'Khách vãng lai' }}</div>
                                <div class="text-xs text-slate-500">{{ $repairOrder->customer->phone ?? 'Chưa có SĐT' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ trim(($repairOrder->vehicle->make ?? '').' '.($repairOrder->vehicle->model ?? '')) ?: 'Chưa rõ xe' }}</div>
                                <div class="mt-1 inline-flex rounded bg-blue-500/10 px-2 py-0.5 font-mono text-xs font-bold text-blue-300">{{ $repairOrder->vehicle->license_plate ?? 'Chưa có biển số' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-300">
                                    {{ $paymentLabels[$repairOrder->payment_status] ?? $repairOrder->payment_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-base font-black text-white">{{ $money($repairOrder->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có doanh thu trong khoảng thời gian này.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($repairOrders->hasPages())
            <div class="border-t border-slate-800 p-6">
                {{ $repairOrders->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
