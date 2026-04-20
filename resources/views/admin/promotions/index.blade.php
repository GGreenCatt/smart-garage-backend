@extends('layouts.admin')

@section('title', 'Quản Lý Khuyến Mãi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-slate-900/50 p-4 rounded-xl border border-slate-700">
        <div>
            <h2 class="text-xl font-black text-white">Chương Trình Khuyến Mãi</h2>
            <p class="text-sm text-slate-400">Tạo mã giảm giá và quản lý marketing</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Tạo Mã Mới
        </a>
    </div>

    <!-- Table -->
    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-500">
                <tr>
                    <th class="px-6 py-4">Mã Code / Mô Tả</th>
                    <th class="px-6 py-4">Giảm Giá</th>
                    <th class="px-6 py-4">Thời Gian</th>
                    <th class="px-6 py-4">Lượt Dùng</th>
                    <th class="px-6 py-4">Trạng Thái</th>
                    <th class="px-6 py-4 text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($promotions as $promo)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-6 py-4">
                        <div class="font-black text-white text-lg tracking-wider font-mono text-indigo-400">{{ $promo->code }}</div>
                        <div class="text-xs text-slate-500">{{ Str::limit($promo->description, 50) }}</div>
                         @if($promo->customer_id)
                            <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                <i class="fas fa-user-tag"></i> {{ $promo->customer->name ?? 'User #'.$promo->customer_id }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($promo->type == 'percent')
                            <span class="font-bold text-white">{{ $promo->value }}%</span>
                        @else
                            <span class="font-bold text-white">{{ number_format($promo->value) }} ₫</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs">
                            <div class="mb-1"><span class="text-slate-600">Start:</span> {{ $promo->start_date ? $promo->start_date->format('d/m/Y H:i') : '__/__/____' }}</div>
                            <div><span class="text-slate-600">End:</span> {{ $promo->end_date ? $promo->end_date->format('d/m/Y H:i') : 'Vô thời hạn' }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                             <div class="w-24 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                @php
                                    $percent = $promo->usage_limit > 0 ? ($promo->used_count / $promo->usage_limit) * 100 : 0;
                                @endphp
                                <div class="bg-indigo-500 h-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-xs font-mono">{{ $promo->used_count }} / {{ $promo->usage_limit ?? '∞' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if(!$promo->is_active)
                            <span class="px-2 py-1 rounded text-xs font-bold bg-slate-700 text-slate-400">Inactive</span>
                        @elseif($promo->isValid())
                             <span class="px-2 py-1 rounded text-xs font-bold bg-green-500/10 text-green-500 border border-green-500/20">Active</span>
                        @else
                             <span class="px-2 py-1 rounded text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20">Expired/Limit</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.promotions.edit', $promo) }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm px-2">Sửa</a>
                        <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST" class="inline-block" onsubmit="return confirm('Xóa mã này?');">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 font-bold text-sm px-2">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">Chưa có mã khuyến mãi nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-700">
            {{ $promotions->links() }}
        </div>
    </div>
</div>
@endsection
