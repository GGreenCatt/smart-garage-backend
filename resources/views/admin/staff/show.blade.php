@extends('layouts.admin')

@section('title', 'Staff Profile: ' . $staff->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.staff.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">{{ $staff->name }}</h2>
            <div class="flex items-center gap-2 text-sm text-slate-400">
                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ ($staff->assignedRole && $staff->assignedRole->slug == 'admin') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-teal-500/10 text-teal-400 border border-teal-500/20' }}">
                    {{ $staff->assignedRole ? $staff->assignedRole->name : ucfirst($staff->role) }}
                </span>
                <span>•</span>
                <span>{{ $staff->email }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <div class="flex flex-col items-center text-center">
                    <img src="https://ui-avatars.com/api/?name={{ $staff->name }}&background={{ $staff->assignedRole && $staff->assignedRole->slug == 'admin' ? '6366f1' : '14b8a6' }}&color=fff&size=128" class="w-32 h-32 rounded-2xl shadow-2xl mb-4">
                    <h3 class="text-xl font-bold text-white">{{ $staff->name }}</h3>
                    <p class="text-slate-400 text-sm mb-4">{{ $staff->assignedRole ? $staff->assignedRole->description : 'Staff Member' }}</p>
                    
                    <div class="w-full grid grid-cols-2 gap-4 border-t border-slate-700 pt-4">
                        <div class="text-center">
                            <span class="block text-xs text-slate-500 uppercase">Joined</span>
                            <span class="block text-white font-bold">{{ $staff->created_at->format('M Y') }}</span>
                        </div>
                         <div class="text-center">
                            <span class="block text-xs text-slate-500 uppercase">Status</span>
                            <span class="block text-green-400 font-bold">Active</span>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Permissions Preview -->
             <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-key text-indigo-400"></i> Assigned Permissions
                </h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($staff->assignedRole->permissions ?? [] as $permission)
                        <span class="px-2 py-1 rounded bg-slate-800 text-xs text-slate-300 border border-slate-700">
                            {{ str_replace('_', ' ', ucfirst($permission)) }}
                        </span>
                    @empty
                        <span class="text-slate-500 italic text-sm">No specific permissions</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="lg:col-span-2">
            <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
                <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-white text-lg flex items-center gap-2">
                        <i class="fas fa-history text-teal-400"></i> Activity History
                    </h3>
                    
                    <form method="GET" class="flex items-center gap-3">
                        <select name="action" class="bg-slate-900 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date" value="{{ request('date') }}" class="bg-slate-900 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2">
                        <button type="submit" class="p-2 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-filter"></i>
                        </button>
                        @if(request()->hasAny(['action', 'date']))
                            <a href="{{ route('admin.staff.show', $staff) }}" class="p-2 text-slate-400 hover:text-white transition" title="Clear Filters">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="p-4 font-bold">Action</th>
                                <th class="p-4 font-bold">Details</th>
                                <th class="p-4 font-bold">IP Address</th>
                                <th class="p-4 font-bold text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-800/30 transition text-sm">
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-lg bg-slate-800 text-indigo-300 border border-slate-700 font-mono text-xs">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-300">{{ Str::limit($log->details, 50) }}</td>
                                <td class="p-4 text-slate-400 font-mono text-xs">{{ $log->ip_address }}</td>
                                <td class="p-4 text-slate-400 text-right">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500">
                                    No activity recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-700/50">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
