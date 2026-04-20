@extends('layouts.customer')

@section('title', 'Lịch Sử Sửa Chữa')

@section('content')
<main class="pt-24 min-h-screen">
    <div class="px-4 py-6">
        <h2 class="text-xl font-bold text-white mb-6">Lịch Sử Sửa Chữa</h2>
        
        <div class="space-y-4">
            @forelse($orders as $order)
            <a href="{{ route('customer.orders.show', $order->id) }}" class="block bg-slate-900/50 border border-slate-700 rounded-xl p-4 hover:bg-slate-800 transition">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="font-mono text-xs font-bold text-indigo-400">{{ $order->track_id }}</span>
                        <h3 class="font-bold text-white">{{ $order->vehicle->model ?? 'Xe' }} ({{ $order->vehicle->license_plate ?? 'Unknown' }})</h3>
                    </div>
                    @php
                        $statusColor = match($order->status) {
                            'completed' => 'text-green-400 bg-green-500/10 border-green-500/20',
                            'cancelled' => 'text-red-400 bg-red-500/10 border-red-500/20',
                            'pending' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20',
                            default => 'text-blue-400 bg-blue-500/10 border-blue-500/20'
                        };
                    @endphp
                    <span class="px-2 py-1 rounded text-[10px] font-bold border {{ $statusColor }} uppercase">{{ $order->status }}</span>
                </div>
                
                <div class="flex justify-between items-end text-sm">
                    <div class="text-slate-500">
                        <div>{{ $order->created_at->format('d/m/Y') }}</div>
                        <div>{{ $order->advisor->name ?? 'Admin' }}</div>
                    </div>
                    <div class="font-bold text-white text-lg">
                        {{ number_format($order->total_amount) }} ₫
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-10 text-slate-500">
                Bạn chưa có lịch sử sửa chữa nào.
            </div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</main>
@endsection
