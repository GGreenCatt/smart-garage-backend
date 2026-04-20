@extends('layouts.admin')

@section('title', 'Smart Garage Admin - Audit Logs')

@section('content')
<!-- Custom Tailwind Config -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#06b6d4",
              "background-light": "#f8fafc",
              "background-dark": "#020617",
              "card-dark": "#0f172a",
              "accent-violet": "#8b5cf6",
            },
            fontFamily: {
              sans: ["Inter", "sans-serif"],
              mono: ["JetBrains Mono", "monospace"],
            },
            borderRadius: {
              DEFAULT: "0.75rem",
            },
          },
        },
      };
</script>
<style>
      .glass {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
      }
      .glow-cyan {
        box-shadow: 0 0 20px -5px rgba(6, 182, 212, 0.4);
      }
      .glow-violet {
        box-shadow: 0 0 20px -5px rgba(139, 92, 246, 0.4);
      }
      .glow-red {
        box-shadow: 0 0 20px -5px rgba(239, 68, 68, 0.4);
      }
      /* Custom Scrollbar */
      .custom-scroll::-webkit-scrollbar {
        width: 6px;
      }
      .custom-scroll::-webkit-scrollbar-track {
        background: transparent;
      }
      .custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
      }
      .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
      }
</style>

<div class="bg-background-dark text-slate-200 font-sans antialiased min-h-screen p-6 custom-scroll">

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass p-6 rounded-2xl flex items-center gap-5 hover:border-primary/30 transition-colors cursor-default">
            <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center text-primary glow-cyan">
                <span class="material-icons-round text-3xl">insights</span>
            </div>
            <div>
                <p class="text-slate-400 text-sm font-medium">Total Activities</p>
                <p class="text-2xl font-bold">{{ $logs->total() }}</p>
            </div>
        </div>
        <div class="glass p-6 rounded-2xl flex items-center gap-5 hover:border-red-500/30 transition-colors cursor-default">
            <div class="w-14 h-14 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 glow-red">
                <span class="material-icons-round text-3xl">gpp_maybe</span>
            </div>
            <div>
                <p class="text-slate-400 text-sm font-medium">Security Alerts</p>
                <p class="text-2xl font-bold">0</p>
            </div>
        </div>
        <div class="glass p-6 rounded-2xl flex items-center gap-5 hover:border-accent-violet/30 transition-colors cursor-default">
            <div class="w-14 h-14 rounded-xl bg-accent-violet/10 flex items-center justify-center text-accent-violet glow-violet">
                <span class="material-icons-round text-3xl">devices_other</span>
            </div>
            <div>
                <p class="text-slate-400 text-sm font-medium">Recent Activities</p>
                <p class="text-2xl font-bold">{{ $logs->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass p-4 rounded-2xl mb-8 flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[240px] relative">
            <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">search</span>
            <input class="w-full bg-slate-800/50 border-white/5 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-primary focus:border-primary text-slate-200" placeholder="Search user or action..." type="text"/>
        </div>
        <div class="flex gap-4">
            <select class="bg-slate-800/50 border-white/5 rounded-lg px-4 py-2 text-sm text-slate-300 focus:ring-primary focus:border-primary outline-none">
                <option>All Action Types</option>
                <option>Login</option>
                <option>Logout</option>
                <option>Update</option>
            </select>
            <button class="bg-primary hover:bg-cyan-500 text-white font-semibold px-6 py-2 rounded-lg transition-all text-sm shadow-lg shadow-primary/20">
                Apply Filters
            </button>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">User</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Action</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Details</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">IP Address</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Time</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                    <tr class="group hover:bg-white/[0.02] transition-colors cursor-pointer" onclick="toggleDetails('details-{{ $log->id }}')">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm ring-2 ring-transparent ring-offset-2 ring-offset-background-dark">
                                         @if($log->user)
                                            {{ substr($log->user->name, 0, 2) }}
                                         @else
                                            SY
                                         @endif
                                    </div>
                                    @if($log->user)
                                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-background-dark rounded-full"></div>
                                    @endif
                                </div>
                                <span class="font-bold text-slate-200">{{ $log->user ? $log->user->name : 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                             @php
                                $colorClass = 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20';
                                if(str_contains(strtolower($log->action), 'delete')) $colorClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                                if(str_contains(strtolower($log->action), 'update')) $colorClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                if(str_contains(strtolower($log->action), 'logout')) $colorClass = 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase {{ $colorClass }} border">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400 truncate max-w-xs">{{ $log->details }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <span class="material-icons-round text-base text-slate-500">public</span>
                                {{ $log->ip_address }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm text-slate-200">{{ $log->created_at->diffForHumans() }}</span>
                                <span class="text-[10px] text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="material-icons-round text-slate-500 group-hover:text-primary transition-all" id="chevron-details-{{ $log->id }}">expand_more</span>
                        </td>
                    </tr>
                    <!-- Expandable Details Row -->
                    <tr class="hidden bg-slate-900/40" id="details-{{ $log->id }}">
                        <td class="px-8 py-6" colspan="6">
                            <div class="bg-black/40 rounded-xl p-4 border border-white/5">
                                <h4 class="text-xs font-mono text-primary mb-3 uppercase tracking-tighter">Full Log Details</h4>
                                <pre class="text-xs font-mono text-cyan-200/70 overflow-x-auto p-2">
{
    "id": {{ $log->id }},
    "user_id": {{ $log->user_id }},
    "action": "{{ $log->action }}",
    "details": "{{ $log->details }}",
    "ip": "{{ $log->ip_address }}",
    "timestamp": "{{ $log->created_at }}"
}
                                </pre>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">No logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-white/10">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
    function toggleDetails(id) {
        const row = document.getElementById(id);
        const chevron = document.getElementById('chevron-' + id);
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
            chevron.classList.add('text-primary');
        } else {
            row.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
            chevron.classList.remove('text-primary');
        }
    }
</script>
@endsection
