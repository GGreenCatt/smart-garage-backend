@extends('layouts.customer')

@section('title', 'Chi Tiết Đơn #' . $order->track_id)

@section('content')
<div class="px-4 py-6 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customer.orders.index') }}" class="w-10 h-10 flex items-center justify-center bg-slate-800 rounded-full text-white">
            <i class="fas fa-chevron-left"></i>
        </a>
        <div>
            <h1 class="font-bold text-white text-lg">Chi Tiết Đơn Hàng</h1>
            <div class="text-xs text-slate-400 font-mono">{{ $order->track_id }}</div>
        </div>
    </div>

    <!-- Status Card -->
     <div class="bg-indigo-600 rounded-2xl p-6 mb-6 text-white shadow-lg shadow-indigo-500/20 relative overflow-hidden">
        <div class="relative z-10">
            <div class="text-sm font-medium opacity-80 mb-1">Tổng Thanh Toán</div>
            <div class="text-3xl font-black mb-4">{{ number_format($order->total_amount) }} ₫</div>
            
            @if($order->payment_status == 'paid')
                <div class="inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-full text-xs font-bold">
                    <i class="fas fa-check-circle"></i> Đã Thanh Toán
                </div>
            @else
                <div class="inline-flex items-center gap-2 bg-yellow-400/20 text-yellow-200 px-3 py-1 rounded-full text-xs font-bold">
                    <i class="fas fa-clock"></i> Chờ Thanh Toán
                </div>
            @endif
        </div>
        <i class="fas fa-receipt absolute -right-4 -bottom-4 text-9xl text-white/10 z-0"></i>
    </div>

    <!-- Items -->
    <div class="bg-slate-900/50 border border-slate-700 rounded-2xl p-4 space-y-4 mb-6">
        <h3 class="text-slate-400 text-xs font-bold uppercase">Chi Tiết Dịch Vụ</h3>
        @foreach($order->items as $item)
        <div class="flex justify-between items-center text-sm">
            <div class="text-white font-medium">
                {{ $item->itemable->name }}
                <div class="text-xs text-slate-500">x{{ $item->quantity }}</div>
            </div>
            <div class="text-white">{{ number_format($item->subtotal) }} ₫</div>
        </div>
        @endforeach
        
        <div class="border-t border-slate-700 pt-4 space-y-2">
            <div class="flex justify-between text-sm text-slate-400">
                <span>Tạm Tính</span>
                <span>{{ number_format($order->subtotal) }} ₫</span>
            </div>
            @if($order->promotion)
            <div class="flex justify-between text-sm text-green-400 font-bold">
                <span>Mã: {{ $order->promotion->code }}</span>
                <span>- {{ number_format($order->discount_amount) }} ₫</span>
            </div>
            @endif
             <div class="flex justify-between text-base text-white font-bold pt-2">
                <span>Tổng Cộng</span>
                <span>{{ number_format($order->total_amount) }} ₫</span>
            </div>
        </div>
    </div>

    <!-- Coupon Input -->
    @if($order->status !== 'completed' && $order->payment_status !== 'paid' && !$order->promotion)
    <div class="bg-slate-900/50 border border-slate-700 rounded-2xl p-4 mb-6">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mã Giảm Giá</label>
        <form action="{{ route('customer.orders.coupon', $order->id) }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="code" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none uppercase font-mono placeholder:text-slate-600" placeholder="NHAP MA GIAM GIA">
            <button class="bg-indigo-600 px-4 rounded-lg font-bold text-white">Áp Dụng</button>
        </form>
         @if($errors->has('coupon'))
            <div class="text-red-400 text-xs mt-2">{{ $errors->first('coupon') }}</div>
        @endif
    </div>
    @endif

    <!-- Pay Button -->
    @if($order->payment_status !== 'paid')
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-slate-950/80 backdrop-blur-xl border-t border-slate-800">
        <button onclick="alert('Tính năng thanh toán Online (VNPAY/Momo) sẽ được tích hợp sau!')" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/20 hover:scale-[1.02] transition">
            Thanh Toán Ngay {{ number_format($order->total_amount) }} ₫
        </button>
    </div>
    @endif
</div>
@endsection
