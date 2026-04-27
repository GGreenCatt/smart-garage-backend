@extends('layouts.admin')

@section('title', 'Quản Lý Khách Hàng')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Khách Hàng</h2>
            <p class="text-sm text-slate-400">Quản lý khách có tài khoản và khách vãng lai phát sinh từ tiếp nhận xe.</p>
        </div>
        <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
            <i class="fas fa-user-plus"></i>
            Thêm khách hàng
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Tổng khách hàng</p>
            <h3 class="mt-2 text-3xl font-black text-white">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Có tài khoản</p>
            <h3 class="mt-2 text-3xl font-black text-emerald-300">{{ number_format($stats['registered']) }}</h3>
        </div>
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Khách vãng lai</p>
            <h3 class="mt-2 text-3xl font-black text-amber-300">{{ number_format($stats['walk_in']) }}</h3>
        </div>
        <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
            <p class="text-sm font-medium text-slate-400">Khách nhiều xe</p>
            <h3 class="mt-2 text-3xl font-black text-indigo-300">{{ number_format($stats['loyalty']) }}</h3>
        </div>
    </div>

    <form action="{{ route('admin.customers.index') }}" method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="flex flex-col gap-3 md:flex-row">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>
                <input name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-slate-700 bg-slate-900/70 py-3 pl-10 pr-4 text-sm text-slate-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tìm theo tên, email, số điện thoại hoặc biển số xe">
            </div>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500" type="submit">Tìm kiếm</button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="rounded-lg bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
            @endif
        </div>
    </form>

    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-white/5">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Khách hàng</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Liên hệ</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Loại khách</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Xe</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Ngày ghi nhận</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($customers as $customer)
                        <tr class="transition hover:bg-white/[0.03]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">
                                        {{ \Illuminate\Support\Str::substr($customer->name ?: 'KH', 0, 2) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.customers.show', $customer->profile_id ?? $customer->id) }}" class="font-bold text-white transition hover:text-indigo-300">
                                            {{ $customer->name ?: 'Khách chưa có tên' }}
                                        </a>
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            {{ empty($customer->is_guest) ? 'ID #' . $customer->id : 'Khách vãng lai' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">
                                <div>{{ $customer->phone ?: 'Chưa có SĐT' }}</div>
                                <div class="text-slate-500">{{ $customer->email ?: 'Chưa có email' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(empty($customer->is_guest))
                                    <span class="inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">Có tài khoản</span>
                                @else
                                    <span class="inline-flex rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300">Vãng lai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 font-bold text-slate-200">
                                    <i class="fas fa-car text-slate-500"></i>
                                    {{ $customer->vehicles_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ optional($customer->created_at)->format('d/m/Y') ?: 'Không rõ' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer->profile_id ?? $customer->id) }}" class="rounded-lg border border-indigo-500/30 px-3 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 transition hover:bg-indigo-600 hover:text-white">
                                    Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Không tìm thấy khách hàng phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-700/50 px-6 py-4">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
