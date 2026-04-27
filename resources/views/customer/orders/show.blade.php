@extends('layouts.customer')

@section('title', 'Chi tiết đơn #' . $order->track_id)

@section('content')
<main class="pt-24 min-h-screen bg-[#0b1120]">
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('customer.orders.index') }}" class="w-10 h-10 flex items-center justify-center bg-slate-800 rounded-full text-white">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div>
                <h1 class="font-bold text-white text-lg">Chi tiết đơn sửa chữa</h1>
                <div class="text-xs text-slate-400 font-mono">{{ $order->track_id }}</div>
            </div>
        </div>

        <section class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20 relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-sm font-medium opacity-80 mb-1">Tổng thanh toán tại quầy</div>
                <div class="text-3xl font-black mb-4">{{ number_format($order->total_amount) }}đ</div>

                @if($order->payment_status === 'paid')
                    <div class="inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fas fa-check-circle"></i> Đã thanh toán
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 bg-yellow-400/20 text-yellow-100 px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fas fa-store"></i> Thanh toán tại quầy
                    </div>
                @endif
            </div>
            <i class="fas fa-receipt absolute -right-4 -bottom-4 text-9xl text-white/10 z-0"></i>
        </section>

        <section class="bg-slate-900/50 border border-slate-700 rounded-2xl p-4 space-y-4">
            <h3 class="text-slate-400 text-xs font-bold uppercase">Chi tiết dịch vụ</h3>
            @forelse($order->items as $item)
                <div class="flex justify-between items-center text-sm">
                    <div class="text-white font-medium">
                        {{ $item->name ?? $item->itemable?->name ?? 'Hạng mục dịch vụ' }}
                        <div class="text-xs text-slate-500">x{{ $item->quantity }}</div>
                    </div>
                    <div class="text-white">{{ number_format($item->subtotal) }}đ</div>
                </div>
            @empty
                <div class="text-sm text-slate-500">Chưa có vật tư/dịch vụ riêng trong đơn.</div>
            @endforelse

            <div class="border-t border-slate-700 pt-4 space-y-2">
                <div class="flex justify-between text-sm text-slate-400">
                    <span>Tạm tính</span>
                    <span>{{ number_format($order->subtotal ?: $order->items->sum('subtotal')) }}đ</span>
                </div>
                @if($order->promotion)
                    <div class="flex justify-between text-sm text-green-400 font-bold">
                        <span>Mã đã áp tại quầy: {{ $order->promotion->code }}</span>
                        <span>- {{ number_format($order->discount_amount) }}đ</span>
                    </div>
                @endif
                <div class="flex justify-between text-base text-white font-bold pt-2">
                    <span>Tổng cộng</span>
                    <span>{{ number_format($order->total_amount) }}đ</span>
                </div>
            </div>
        </section>

        @if($order->payment_status !== 'paid')
            <section class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-amber-100">
                <div class="flex gap-3">
                    <i class="fas fa-circle-info text-amber-300 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-white">Thanh toán tại quầy</h3>
                        <p class="text-sm mt-1 text-amber-100/80">
                            Vui lòng đọc hoặc gửi mã giảm giá cho nhân viên khi thanh toán. Nhân viên sẽ áp mã và xác nhận thanh toán trên hệ thống.
                        </p>
                    </div>
                </div>
            </section>
        @endif
    </div>
</main>
@endsection
