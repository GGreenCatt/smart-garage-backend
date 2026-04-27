@extends('layouts.admin')

@section('title', 'Hồ Sơ Nhân Viên: ' . $staff->name)

@section('content')
@php
    $permissionLabels = \App\Models\Role::permissionLabels();
    $statusLabels = [
        'active' => ['label' => 'Đang hoạt động', 'class' => 'text-emerald-300'],
        'inactive' => ['label' => 'Ngừng hoạt động', 'class' => 'text-slate-300'],
        'banned' => ['label' => 'Đã khóa', 'class' => 'text-red-300'],
    ];
    $status = $statusLabels[$staff->status ?? 'active'] ?? $statusLabels['inactive'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.staff.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-slate-400 transition hover:bg-slate-700 hover:text-white">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $staff->name }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-400">
                    <span class="rounded border border-teal-500/20 bg-teal-500/10 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-teal-300">
                        {{ $staff->assignedRole?->name ?? ucfirst($staff->role ?? 'staff') }}
                    </span>
                    <span>{{ $staff->email }}</span>
                </div>
            </div>
        </div>
        @can('manage_staff')
            <a href="{{ route('admin.staff.edit', $staff) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white transition hover:bg-indigo-500">
                <i class="fas fa-pen"></i>
                Chỉnh sửa
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-1">
            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <div class="flex flex-col items-center text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=14b8a6&color=fff&size=128" class="mb-4 h-32 w-32 rounded-2xl shadow-2xl" alt="{{ $staff->name }}">
                    <h3 class="text-xl font-bold text-white">{{ $staff->name }}</h3>
                    <p class="mb-4 text-sm text-slate-400">{{ $staff->assignedRole?->description ?: 'Nhân sự nội bộ' }}</p>

                    <div class="grid w-full grid-cols-2 gap-4 border-t border-slate-700 pt-4">
                        <div class="text-center">
                            <span class="block text-xs uppercase text-slate-500">Ngày tạo</span>
                            <span class="block font-bold text-white">{{ $staff->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xs uppercase text-slate-500">Trạng thái</span>
                            <span class="block font-bold {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <h4 class="mb-4 flex items-center gap-2 font-bold text-white">
                    <i class="fas fa-key text-indigo-400"></i>
                    Quyền được cấp
                </h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($staff->assignedRole->permissions ?? [] as $permission)
                        <span class="rounded border border-slate-700 bg-slate-800 px-2 py-1 text-xs text-slate-300">
                            {{ $permissionLabels[$permission] ?? str_replace('_', ' ', $permission) }}
                        </span>
                    @empty
                        <span class="text-sm italic text-slate-500">Chưa có quyền riêng.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
                <div class="flex flex-col gap-4 border-b border-slate-700/50 p-6 md:flex-row md:items-center md:justify-between">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-white">
                        <i class="fas fa-history text-teal-400"></i>
                        Lịch sử thao tác
                    </h3>

                    <form method="GET" class="flex flex-wrap items-center gap-3">
                        <select name="action" class="rounded-lg border border-slate-700 bg-slate-900 p-2 text-sm text-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tất cả thao tác</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') == $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border border-slate-700 bg-slate-900 p-2 text-sm text-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="rounded-lg bg-indigo-600 p-2 text-white transition hover:bg-indigo-500" title="Lọc">
                            <i class="fas fa-filter"></i>
                        </button>
                        @if(request()->hasAny(['action', 'date']))
                            <a href="{{ route('admin.staff.show', $staff) }}" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white" title="Xóa bộ lọc">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-slate-800/50 text-xs uppercase tracking-wider text-slate-400">
                                <th class="p-4 font-bold">Thao tác</th>
                                <th class="p-4 font-bold">Nội dung</th>
                                <th class="p-4 font-bold">Địa chỉ IP</th>
                                <th class="p-4 text-right font-bold">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($logs as $log)
                                <tr class="text-sm transition hover:bg-slate-800/30">
                                    <td class="p-4">
                                        <span class="rounded-lg border border-slate-700 bg-slate-800 px-2 py-1 font-mono text-xs text-indigo-300">{{ $log->action }}</span>
                                    </td>
                                    <td class="p-4 text-slate-300">{{ $log->details }}</td>
                                    <td class="p-4 font-mono text-xs text-slate-400">{{ $log->ip_address ?: 'Không rõ' }}</td>
                                    <td class="p-4 text-right text-slate-400">
                                        <div>{{ $log->created_at->diffForHumans() }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-500">Chưa có lịch sử thao tác.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-700/50 p-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
