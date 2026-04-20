@extends('layouts.admin')

@section('title', 'Quản Lý Sửa Chữa')

@section('content')
<!-- Custom Tailwind Config -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#6366f1", // Indigo 500
                    "background-light": "#f3f4f6", // Gray 100
                    "background-dark": "#0f172a", // Slate 900
                    "surface-light": "#ffffff",
                    "surface-dark": "#1e293b", // Slate 800
                    "glass-dark": "rgba(30, 41, 59, 0.7)",
                    "glass-border": "rgba(255, 255, 255, 0.08)",
                    success: "#10b981",
                    danger: "#ef4444",
                    warning: "#f59e0b",
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .light .glass-panel {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    html:not(.dark) .glass-panel {
         background: #fff;
         border: 1px solid #e2e8f0;
         box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
</style>

<div class="font-sans text-gray-100 antialiased min-h-screen">
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="glass-panel p-5 rounded-xl flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                <span class="material-icons-round text-indigo-500">analytics</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Tổng Lệnh</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-xl flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
                <span class="material-icons-round text-warning">hourglass_empty</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Chờ Xử Lý</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending']) }}</p>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-xl flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                <span class="material-icons-round text-blue-500">settings_suggest</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Đang Sửa Chữa</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['in_progress']) }}</p>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-xl flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                <span class="material-icons-round text-success">today</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Hẹn Trả Hôm Nay</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['due_today']) }}</p>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold flex items-center gap-2 text-gray-900 dark:text-white w-full md:w-auto">
            <span class="material-icons-round text-primary">car_repair</span>
            Quản Lý Sửa Chữa
        </h2>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <form action="{{ route('admin.repair_orders.index') }}" method="GET" class="relative group">
                <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors">search</span>
                <input name="search" value="{{ request('search') }}" class="w-full sm:w-64 bg-white dark:bg-surface-dark border border-gray-300 dark:border-gray-600 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-gray-900 dark:text-white placeholder-gray-500 outline-none transition-all" placeholder="Tìm Lệnh #, biển số..." type="text">
            </form>
            
            <a href="{{ route('admin.repair_orders.create') }}" class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                <span class="material-icons-round text-lg">add_circle</span>
                <span>Tiếp Nhận Xe</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl shadow-lg overflow-hidden transition-colors duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-200 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Lệnh Sửa Chữa</th>
                        <th class="px-6 py-4">Khách Hàng</th>
                        <th class="px-6 py-4">Phương Tiện</th>
                        <th class="px-6 py-4 text-center">Tiến Độ</th>
                        <th class="px-6 py-4">Trạng Thái</th>
                        <th class="px-6 py-4 text-right">Tổng Tiền</th>
                        <th class="px-6 py-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($repairOrders as $ro)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer group" onclick="window.location='{{ route('admin.repair_orders.show', $ro) }}'">
                        <td class="px-6 py-4">
                            <span class="font-bold text-primary text-base">{{ $ro->track_id }}</span>
                            <div class="text-xs text-gray-500 mt-1">{{ $ro->created_at->format('d/m H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-200 dark:border-indigo-500/30">
                                    {{ substr($ro->customer->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-medium">{{ $ro->customer->name ?? 'Khách Lẻ' }}</div>
                                    <div class="text-xs text-gray-500">{{ $ro->customer->phone ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                <div class="w-10 h-8 rounded bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                    <span class="material-icons-round text-gray-500 dark:text-gray-400 text-lg">directions_car</span>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-medium">{{ $ro->vehicle->model ?? 'Không xác định' }}</div>
                                    <div class="text-xs font-mono text-blue-400 font-bold bg-blue-500/10 px-1.5 py-0.5 rounded inline-block mt-0.5">{{ $ro->vehicle->license_plate ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                         <!-- Progress -->
                        <td class="px-6 py-4">
                            @php
                                $totalTasks = $ro->tasks()->count();
                                $completedTasks = $ro->tasks()->where('status', 'completed')->count();
                                $percent = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                                $color = $percent == 100 ? 'bg-success' : ($percent > 0 ? 'bg-blue-500' : 'bg-gray-400 dark:bg-gray-600');
                            @endphp
                            <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-1">
                                <div class="h-full {{ $color }} transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="text-xs text-center text-gray-500 dark:text-gray-400 font-medium">{{ $completedTasks }}/{{ $totalTasks }} Hạng mục</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-500/10 text-warning border-amber-500/20',
                                    'in_progress' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'completed' => 'bg-emerald-500/10 text-success border-emerald-500/20',
                                    'cancelled' => 'bg-red-500/10 text-danger border-red-500/20',
                                    'approved' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                ];
                                $statusNames = [
                                    'pending' => 'Chờ Xử Lý',
                                    'in_progress' => 'Đang Sửa',
                                    'completed' => 'Hoàn Thành',
                                    'cancelled' => 'Đã Hủy',
                                    'approved' => 'Đã Duyệt',
                                ];
                                $colorClass = $statusColors[$ro->status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $colorClass }}">
                                {{ $statusNames[$ro->status] ?? $ro->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white text-base">
                            {{ number_format($ro->total_amount, 0, ',', '.') }} ₫
                        </td>
                         <td class="px-6 py-4 text-right">
                            <span class="material-icons-round text-gray-400 group-hover:text-primary transition-colors">arrow_forward_ios</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">
                            Chưa có lệnh sửa chữa nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-800/50 px-6 py-4 border-t border-gray-700">
             {{ $repairOrders->links() }}
        </div>
    </div>
</div>
@endsection
