@extends('layouts.admin')

@section('title', 'Lịch Sử Kho')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('admin.inventory.index') }}" class="transition hover:text-indigo-300">Kho & Vật Tư</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-white">Lịch sử kho</span>
            </div>
            <h2 class="text-2xl font-bold text-white">Lịch Sử Nhập Xuất Kho</h2>
        </div>
    </div>

    <form method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_200px_auto_auto]">
            <input name="search" value="{{ request('search') }}" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none" placeholder="Tìm vật tư, SKU, ghi chú">
            <select name="type" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                <option value="all">Tất cả loại</option>
                <option value="in" @selected(request('type') === 'in')>Nhập kho</option>
                <option value="out" @selected(request('type') === 'out')>Xuất kho</option>
            </select>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">Lọc</button>
            <a href="{{ route('admin.inventory.transactions') }}" class="rounded-lg bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
        </div>
    </form>

    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="border-b border-slate-700/50 bg-slate-900/50 text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-4">Thời gian</th>
                    <th class="px-6 py-4">Vật tư</th>
                    <th class="px-6 py-4">Loại</th>
                    <th class="px-6 py-4">Số lượng</th>
                    <th class="px-6 py-4">Người thao tác</th>
                    <th class="px-6 py-4">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($transactions as $log)
                    <tr class="transition hover:bg-slate-800/30">
                        <td class="px-6 py-4">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-white">{{ $log->part->name ?? 'Vật tư đã xóa' }}</div>
                            <div class="text-xs text-slate-500">{{ $log->part->sku ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->type === 'in')
                                <span class="rounded bg-emerald-500/10 px-2 py-1 text-xs font-bold text-emerald-300">Nhập kho</span>
                            @else
                                <span class="rounded bg-orange-500/10 px-2 py-1 text-xs font-bold text-orange-300">Xuất kho</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-white">{{ $log->quantity }}</td>
                        <td class="px-6 py-4">{{ $log->user->name ?? 'Hệ thống' }}</td>
                        <td class="px-6 py-4 text-xs">
                            <div>{{ $log->note ?: 'Không có ghi chú' }}</div>
                            @if($log->reference)
                                <div class="mt-1 font-mono text-slate-600">{{ $log->reference }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">Chưa có giao dịch kho phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-800 p-4">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
