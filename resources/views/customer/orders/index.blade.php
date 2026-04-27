@extends('layouts.customer')

@section('title', 'Lịch sử sửa chữa')

@php
    $statusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra',
        'pending_approval' => 'Chờ duyệt báo giá',
        'approved' => 'Đã duyệt báo giá',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $statusClasses = [
        'completed' => 'text-emerald-300 bg-emerald-500/10 border-emerald-500/25',
        'cancelled' => 'text-red-300 bg-red-500/10 border-red-500/25',
        'pending' => 'text-amber-300 bg-amber-500/10 border-amber-500/25',
        'pending_approval' => 'text-amber-300 bg-amber-500/10 border-amber-500/25',
        'approved' => 'text-cyan-300 bg-cyan-500/10 border-cyan-500/25',
        'in_progress' => 'text-cyan-300 bg-cyan-500/10 border-cyan-500/25',
    ];
@endphp

@section('content')
<main class="pt-24 min-h-screen bg-[#0b1120]">
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <div>
            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white mb-3">
                <i class="fas fa-arrow-left"></i> Về tổng quan
            </a>
            <h1 class="text-3xl font-black text-white">Lịch sử sửa chữa</h1>
            <p class="text-slate-400 mt-2">Tra cứu các đơn sửa chữa, tổng tiền và trạng thái thanh toán.</p>
        </div>

        <div class="space-y-4">
            @forelse($orders as $order)
                <a href="{{ route('customer.orders.show', $order->id) }}" class="block bg-slate-900/70 border border-slate-800 rounded-2xl p-5 hover:border-cyan-500/40 hover:bg-slate-900 transition">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-black text-cyan-300">{{ $order->track_id }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase {{ $statusClasses[$order->status] ?? 'text-slate-300 bg-slate-500/10 border-slate-500/25' }}">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </div>
                            <h2 class="font-black text-white text-lg mt-2">{{ $order->vehicle->model ?? 'Xe' }} · {{ $order->vehicle->license_plate ?? 'Chưa rõ biển số' }}</h2>
                            <div class="text-sm text-slate-400 mt-1">{{ $order->created_at->format('d/m/Y') }} · Cố vấn: {{ $order->advisor->name ?? 'Garage' }}</div>
                        </div>
                        <div class="md:text-right">
                            <div class="font-black text-white text-xl">{{ number_format($order->total_amount) }}đ</div>
                            <div class="text-xs mt-1 {{ $order->payment_status === 'paid' ? 'text-emerald-300' : 'text-amber-300' }}">
                                {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Thanh toán tại quầy' }}
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-16 border border-dashed border-slate-700 rounded-2xl text-slate-400">
                    <i class="fas fa-clock-rotate-left text-5xl mb-4 opacity-30"></i>
                    <h3 class="font-black text-white">Chưa có lịch sử sửa chữa</h3>
                    <p class="text-sm mt-2">Khi garage tiếp nhận xe, đơn sửa chữa sẽ xuất hiện tại đây.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</main>
@endsection
