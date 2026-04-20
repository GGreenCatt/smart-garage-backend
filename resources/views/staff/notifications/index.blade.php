@extends('layouts.staff')

@section('title', 'Thông Báo')

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header Controls -->
    <div class="bg-white dark:bg-[#1e293b] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex justify-between items-center transition-colors">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-icons-round text-indigo-500 text-3xl">notifications</span>
                Thông Báo
                @if($notifications->whereNull('read_at')->count() > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-red-500/20 shadow-sm animate-pulse">
                    {{ $notifications->whereNull('read_at')->count() }} mới
                </span>
                @endif
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Cập nhật hoạt động mới nhất từ hệ thống</p>
        </div>
        
        <div class="flex gap-2">
            <form action="{{ route('staff.notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2 transition">
                    <span class="material-icons-round text-[18px]">done_all</span> Đánh dấu đã đọc
                </button>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex-1 overflow-hidden flex flex-col">
        <div class="overflow-y-auto flex-1 p-4 space-y-3 custom-scrollbar">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isRead = !is_null($notification->read_at);
                    // Determine Icon & Color based on type (heuristic)
                    $type = $notification->type; // e.g., App\Notifications\NewOrder
                    $icon = 'notifications';
                    $color = 'indigo';
                    
                    if (str_contains($type, 'Order')) { $icon = 'assignment'; $color = 'emerald'; }
                    if (str_contains($type, 'Vehicle')) { $icon = 'directions_car'; $color = 'blue'; }
                    if (str_contains($type, 'Stock') || str_contains($type, 'Inventory')) { $icon = 'inventory_2'; $color = 'orange'; }
                    if (str_contains($type, 'Task')) { $icon = 'task_alt'; $color = 'purple'; }
                @endphp

                <div id="notif-{{ $notification->id }}" 
                     class="group relative flex items-start gap-4 p-4 rounded-xl border transition-all duration-200 
                            {{ $isRead ? 'bg-white dark:bg-[#1e293b] border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/50' : 'bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-500/20' }}">
                    
                    <!-- Icon -->
                    <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center 
                                {{ $isRead ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' : 'bg-'.$color.'-100 dark:bg-'.$color.'-900/30 text-'.$color.'-600 dark:text-'.$color.'-400' }}">
                        <span class="material-icons-round text-2xl">{{ $icon }}</span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pt-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-slate-800 dark:text-white text-base truncate pr-8">
                                {{ $data['title'] ?? 'Thông báo hệ thống' }}
                            </h4>
                            <span class="text-xs font-mono text-slate-400 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-2 line-clamp-2">
                            {{ $data['message'] ?? ($data['content'] ?? 'Không có nội dung chi tiết.') }}
                        </p>
                        
                        @if(isset($data['action_url']))
                        <a href="{{ $data['action_url'] }}" onclick="markAsRead('{{ $notification->id }}')" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                        </a>
                        @endif
                    </div>

                    <!-- Read Status Dot / Button -->
                    @if(!$isRead)
                    <button onclick="markAsRead('{{ $notification->id }}', true)" title="Đánh dấu đã đọc" class="absolute top-4 right-4 w-8 h-8 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition opacity-0 group-hover:opacity-100">
                        <span class="material-icons-round text-[18px]">done</span>
                    </button>
                    <span id="dot-{{ $notification->id }}" class="absolute top-6 right-6 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-2 ring-white dark:ring-[#1e293b]"></span>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                        <span class="material-icons-round text-5xl opacity-50">notifications_off</span>
                    </div>
                    <p class="text-lg font-medium">Bạn chưa có thông báo nào</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($notifications->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function markAsRead(id, refresh = false) {
        fetch(`/staff/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // UI Update
                const card = document.getElementById(`notif-${id}`);
                const dot = document.getElementById(`dot-${id}`);
                if(card) {
                    card.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-900/10', 'border-indigo-100');
                    card.classList.add('bg-white', 'dark:bg-[#1e293b]', 'border-transparent');
                }
                if(dot) dot.remove();
                
                if (refresh) {
                    // Update header count if needed or simple toast
                }
            }
        });
    }


</script>
@endsection
