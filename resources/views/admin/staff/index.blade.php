@extends('layouts.admin')

@section('title', 'Manage Staff')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-white">Đội Ngũ Nhân Sự</h2>
            <p class="text-sm text-slate-400">Quản lý quyền truy cập và vai trò của nhân viên</p>
        </div>
        @can('manage_staff')
        <a href="{{ route('admin.staff.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
            <i class="fas fa-plus"></i> Thêm Nhân Viên
        </a>
        @endcan
    </div>

    <!-- Staff Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($staff as $user)
        <div class="glass-panel p-6 rounded-2xl border border-slate-700/50 hover:border-indigo-500/50 transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ $user->name }}&background={{ $user->role == 'admin' ? '6366f1' : '14b8a6' }}&color=fff" class="w-12 h-12 rounded-xl shadow-lg">
                    <div>
                        <h3 class="font-bold text-white text-lg group-hover:text-indigo-400 transition">
                            <a href="{{ route('admin.staff.show', $user) }}" class="hover:underline">
                                {{ $user->name }}
                            </a>
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase tracking-wider font-bold {{ ($user->assignedRole && $user->assignedRole->slug == 'admin') || $user->role == 'admin' ? 'text-indigo-400' : 'text-teal-400' }}">
                                {{ $user->assignedRole ? $user->assignedRole->name : ucfirst($user->role) }}
                            </span>
                            <span class="w-1 h-1 bg-slate-600 rounded-full"></span>
                            <span class="text-xs text-slate-500">ID: #{{ $user->id }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.staff.edit', $user) }}" class="text-slate-500 hover:text-indigo-400 transition transform hover:scale-110"><i class="fas fa-edit"></i></a>
            </div>

            <div class="space-y-3 mb-6">
                @if($user->tags && is_array($user->tags) && count($user->tags) > 0)
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($user->tags as $tag)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
                <div class="flex items-center gap-3 text-sm text-slate-400">
                    <i class="fas fa-envelope w-5 text-center"></i>
                    <span>{{ $user->email }}</span>
                </div>
                 <div class="flex items-center gap-3 text-sm text-slate-400">
                    <i class="fas fa-phone w-5 text-center"></i>
                    <span>{{ $user->phone ?? 'No Phone' }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700/50 flex justify-between items-center text-xs font-bold">
                <span class="px-2 py-1 rounded bg-green-500/10 text-green-400 border border-green-500/20">Active</span>
                <span class="text-slate-500">Last active: Recently</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
