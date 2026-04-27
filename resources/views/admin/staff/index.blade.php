@extends('layouts.admin')

@section('title', 'Quản Lý Nhân Sự')

@section('content')
@php
    $statusLabels = [
        'active' => ['label' => 'Đang hoạt động', 'class' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20'],
        'inactive' => ['label' => 'Ngừng hoạt động', 'class' => 'bg-slate-500/10 text-slate-300 border-slate-500/20'],
        'banned' => ['label' => 'Đã khóa', 'class' => 'bg-red-500/10 text-red-300 border-red-500/20'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Đội Ngũ Nhân Sự</h2>
            <p class="text-sm text-slate-400">Quản lý tài khoản nhân viên, chức vụ và trạng thái sử dụng hệ thống.</p>
        </div>
        @can('manage_staff')
            <a href="{{ route('admin.staff.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
                <i class="fas fa-plus"></i>
                Thêm nhân viên
            </a>
        @endcan
    </div>

    @if(session('success') || session('warning') || session('error'))
        <div class="space-y-2">
            @foreach(['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $type => $color)
                @if(session($type))
                    <div class="rounded-xl border border-{{ $color }}-500/20 bg-{{ $color }}-500/10 px-4 py-3 text-sm font-semibold text-{{ $color }}-200">
                        {{ session($type) }}
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($staff as $user)
            @php
                $status = $statusLabels[$user->status ?? 'active'] ?? $statusLabels['inactive'];
                $roleName = $user->assignedRole?->name ?? ucfirst($user->role ?? 'staff');
            @endphp
            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6 transition hover:border-indigo-500/50">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=14b8a6&color=fff" class="h-12 w-12 rounded-xl shadow-lg" alt="{{ $user->name }}">
                        <div class="min-w-0">
                            <a href="{{ route('admin.staff.show', $user) }}" class="block truncate text-lg font-bold text-white transition hover:text-indigo-300">
                                {{ $user->name }}
                            </a>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="font-bold uppercase tracking-wider text-teal-300">{{ $roleName }}</span>
                                <span>ID #{{ $user->id }}</span>
                            </div>
                        </div>
                    </div>
                    @can('manage_staff')
                        <a href="{{ route('admin.staff.edit', $user) }}" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-800 hover:text-indigo-300" title="Chỉnh sửa">
                            <i class="fas fa-pen"></i>
                        </a>
                    @endcan
                </div>

                @if($user->tags && is_array($user->tags) && count($user->tags) > 0)
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($user->tags as $tag)
                            <span class="rounded border border-indigo-500/20 bg-indigo-500/10 px-2 py-1 text-[11px] font-bold text-indigo-300">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-3 text-sm text-slate-400">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope w-5 text-center text-slate-500"></i>
                        <span class="truncate">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-phone w-5 text-center text-slate-500"></i>
                        <span>{{ $user->phone ?: 'Chưa có số điện thoại' }}</span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-700/50 pt-4 text-xs">
                    <span class="rounded border px-2 py-1 font-bold {{ $status['class'] }}">{{ $status['label'] }}</span>
                    <span class="text-slate-500">Tạo: {{ optional($user->created_at)->format('d/m/Y') }}</span>
                </div>
            </div>
        @empty
            <div class="glass-panel col-span-full rounded-2xl border border-slate-700/50 p-10 text-center">
                <p class="font-semibold text-white">Chưa có nhân viên nào.</p>
                <p class="mt-1 text-sm text-slate-400">Hãy tạo tài khoản nhân viên đầu tiên để phân quyền vận hành.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
