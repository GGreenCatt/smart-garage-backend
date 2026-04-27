@extends('layouts.admin')

@section('title', 'Nhật Ký Thao Tác')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Nhật Ký Thao Tác</h2>
            <p class="text-sm text-slate-400">Theo dõi các thay đổi quan trọng trong hệ thống.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Kết quả đang hiển thị</p>
            <p class="mt-2 text-3xl font-black text-white">{{ $logs->total() }}</p>
        </div>
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Trên trang này</p>
            <p class="mt-2 text-3xl font-black text-white">{{ $logs->count() }}</p>
        </div>
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Loại thao tác</p>
            <p class="mt-2 text-3xl font-black text-white">{{ $actions->count() }}</p>
        </div>
    </div>

    <form method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_220px_190px_auto_auto]">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>
                <input name="q" value="{{ request('q') }}" class="w-full rounded-lg border border-slate-700 bg-slate-900/70 py-3 pl-10 pr-4 text-sm text-slate-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tìm theo tên, email, thao tác hoặc nội dung">
            </div>
            <select name="action" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả thao tác</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500" type="submit">Lọc</button>
            <a href="{{ route('admin.staff.logs') }}" class="rounded-lg bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
        </div>
    </form>

    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-white/5">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Người thao tác</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Thao tác</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Nội dung</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Địa chỉ IP</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Thời gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($logs as $log)
                        <tr class="transition hover:bg-white/[0.03]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                        {{ $log->user ? mb_substr($log->user->name, 0, 2) : 'HT' }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-200">{{ $log->user?->name ?? 'Hệ thống' }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300">{{ $log->action }}</span>
                            </td>
                            <td class="max-w-md px-6 py-4 text-sm text-slate-300">{{ $log->details }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-400">{{ $log->ip_address ?: 'Không rõ' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-200">{{ $log->created_at->diffForHumans() }}</div>
                                <div class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">Không tìm thấy nhật ký phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-700/50 px-6 py-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
