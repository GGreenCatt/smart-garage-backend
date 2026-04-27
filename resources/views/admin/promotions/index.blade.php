@extends('layouts.admin')

@section('title', 'Mã Khuyến Mãi')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Tổng mã</p>
            <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Đang bật</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-300">{{ number_format($stats['active']) }}</h3>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Tổng lượt dùng</p>
            <h3 class="mt-2 text-2xl font-black text-indigo-300">{{ number_format($stats['used']) }}</h3>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-white">
                <i class="fas fa-ticket-alt text-indigo-400"></i>
                Mã Khuyến Mãi
            </h2>
            <p class="mt-1 text-sm text-slate-400">Tạo mã giảm giá để nhân viên áp tại quầy khi khách thanh toán.</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
            <i class="fas fa-plus"></i>
            Tạo mã mới
        </a>
    </div>

    <form method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_220px_auto_auto]">
            <input name="search" value="{{ request('search') }}" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none" placeholder="Tìm mã, mô tả, tên hoặc SĐT khách">
            <select name="status" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                <option value="all">Tất cả trạng thái</option>
                <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Đã tắt</option>
            </select>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">Lọc</button>
            <a href="{{ route('admin.promotions.index') }}" class="rounded-lg bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
        </div>
    </form>

    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="border-b border-slate-700/50 bg-slate-900/50 text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-4">Mã / mô tả</th>
                    <th class="px-6 py-4">Giảm giá</th>
                    <th class="px-6 py-4">Thời gian</th>
                    <th class="px-6 py-4">Lượt dùng</th>
                    <th class="px-6 py-4">Trạng thái</th>
                    <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($promotions as $promotion)
                    @php
                        $limit = $promotion->usage_limit ?: null;
                        $percent = $limit ? min(100, ($promotion->used_count / $limit) * 100) : 0;
                    @endphp
                    <tr class="transition hover:bg-slate-800/30">
                        <td class="px-6 py-4">
                            <div class="font-mono text-lg font-black tracking-wider text-indigo-300">{{ $promotion->code }}</div>
                            <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($promotion->description ?: 'Không có mô tả', 70) }}</div>
                            @if($promotion->customer_id)
                                <div class="mt-2 inline-flex items-center gap-1 rounded border border-purple-500/20 bg-purple-500/10 px-2 py-0.5 text-[10px] font-bold text-purple-300">
                                    <i class="fas fa-user-tag"></i>
                                    {{ $promotion->customer->name ?? 'Khách #' . $promotion->customer_id }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($promotion->type === 'percent')
                                <span class="font-bold text-white">{{ rtrim(rtrim(number_format($promotion->value, 2), '0'), '.') }}%</span>
                            @else
                                <span class="font-bold text-white">{{ number_format($promotion->value, 0, ',', '.') }}đ</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div><span class="text-slate-600">Bắt đầu:</span> {{ $promotion->start_date ? $promotion->start_date->format('d/m/Y H:i') : 'Ngay khi bật' }}</div>
                            <div class="mt-1"><span class="text-slate-600">Kết thúc:</span> {{ $promotion->end_date ? $promotion->end_date->format('d/m/Y H:i') : 'Không giới hạn' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full bg-indigo-500" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="font-mono text-xs">{{ $promotion->used_count }} / {{ $promotion->usage_limit ?? '∞' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if(!$promotion->is_active)
                                <span class="rounded bg-slate-700 px-2 py-1 text-xs font-bold text-slate-300">Đã tắt</span>
                            @elseif($promotion->isValid())
                                <span class="rounded border border-green-500/20 bg-green-500/10 px-2 py-1 text-xs font-bold text-green-300">Đang dùng được</span>
                            @else
                                <span class="rounded border border-red-500/20 bg-red-500/10 px-2 py-1 text-xs font-bold text-red-300">Hết hạn / hết lượt</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="px-2 text-sm font-bold text-indigo-300 hover:text-indigo-200">Sửa</a>
                            <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="inline-block" onsubmit="return confirm('Xóa hoặc tắt mã này?');">
                                @csrf
                                @method('DELETE')
                                <button class="px-2 text-sm font-bold text-red-300 hover:text-red-200">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">Chưa có mã khuyến mãi nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-700 p-4">
            {{ $promotions->links() }}
        </div>
    </div>
</div>
@endsection
