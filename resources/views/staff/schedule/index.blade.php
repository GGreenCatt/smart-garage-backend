@extends('layouts.staff')

@section('title', 'Lịch Làm Việc')

@section('content')
<div class="h-full flex flex-col">
    <!-- Header with Transparent/Minimal styling -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-4 shrink-0">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-calendar-alt text-teal-500"></i> Lịch Làm Việc
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Quản lý ca làm việc của bạn</p>
        </div>

        <div class="flex items-center gap-2">
             <!-- View Mode Toggle -->
             <div class="flex p-1 bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                <a href="{{ route('staff.schedule.index', ['view' => 'week', 'date' => $baseDate->toDateString()]) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $viewMode === 'week' ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' }}">
                   <i class="fas fa-columns"></i> Tuần
                </a>
                <a href="{{ route('staff.schedule.index', ['view' => 'month', 'date' => $baseDate->toDateString()]) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $viewMode === 'month' ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' }}">
                   <i class="fas fa-calendar"></i> Tháng
                </a>
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center bg-white dark:bg-[#1e293b] rounded-xl p-1 shadow-sm border border-slate-100 dark:border-slate-800">
                @php
                    $prevDate = $viewMode === 'month' ? $baseDate->copy()->subMonth() : $baseDate->copy()->subWeek();
                    $nextDate = $viewMode === 'month' ? $baseDate->copy()->addMonth() : $baseDate->copy()->addWeek();
                    $displayDate = $viewMode === 'month' ? 'Tháng ' . $baseDate->format('m/Y') : 'Tuần ' . $baseDate->startOfWeek()->format('d/m') . ' - ' . $baseDate->endOfWeek()->format('d/m');
                @endphp
                
                <a href="{{ route('staff.schedule.index', ['view' => $viewMode, 'date' => $prevDate->toDateString()]) }}" class="w-8 h-8 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
                
                <span class="px-3 text-xs font-bold text-slate-700 dark:text-slate-200 min-w-[140px] text-center border-x border-slate-100 dark:border-slate-700/50 mx-1">
                    {{ $displayDate }}
                </span>
                
                <a href="{{ route('staff.schedule.index', ['view' => $viewMode, 'date' => $nextDate->toDateString()]) }}" class="w-8 h-8 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <a href="{{ route('staff.schedule.index', ['view' => $viewMode, 'date' => now()->toDateString()]) }}" class="px-3 py-2 bg-white dark:bg-[#1e293b] text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold border border-slate-100 dark:border-slate-800 shadow-sm hover:text-teal-600 dark:hover:text-teal-400 transition">
                Hôm nay
            </a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="flex-1 bg-white dark:bg-[#1e293b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col transition-colors">
        
        <!-- Days Header -->
        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#0B1120]/30 shrink-0">
            @foreach(['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'] as $day)
            <div class="py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                {{ $day }}
            </div>
            @endforeach
        </div>

        <!-- Days Body -->
        <div class="flex-1 p-2 md:p-4 overflow-y-auto">
            <div class="grid grid-cols-7 gap-2 md:gap-4 {{ $viewMode === 'month' ? 'h-full auto-rows-fr' : 'h-auto' }}">
                @foreach($calendar as $day)
                    @php
                        $isToday = $day['date']->isToday();
                        $isCurrentMonth = $day['is_current_month'];
                        $shift = $day['shift'];
                        
                        $opacityClass = ($viewMode === 'month' && !$isCurrentMonth) ? 'opacity-30 grayscale' : '';
                        
                        $borderClass = $isToday 
                            ? 'ring-1 ring-teal-500 border-teal-500 dark:border-teal-500 shadow-[0_0_10px_rgba(20,184,166,0.1)]' 
                            : 'border-slate-100 dark:border-slate-800 hover:border-teal-200 dark:hover:border-teal-800 group-hover:shadow-md';
                            
                        $bgClass = $shift && $shift->status == 'active' 
                            ? 'bg-gradient-to-br from-white to-teal-50/50 dark:from-[#1e293b] dark:to-teal-900/10' 
                            : 'bg-white dark:bg-[#1e293b]';
                            
                        // Fixed height for week view, auto for month view
                        $heightClass = $viewMode === 'month' ? 'min-h-[100px]' : 'h-[400px]';
                    @endphp

                    <div class="relative rounded-2xl border {{ $borderClass }} {{ $bgClass }} {{ $opacityClass }} {{ $heightClass }} flex flex-col transition-all group overflow-hidden">
                         
                         <!-- Highlights for Active Shift -->
                         @if($shift && $shift->status == 'active')
                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-teal-500/10 to-transparent rounded-bl-3xl"></div>
                        @endif

                        <!-- Date Header -->
                        <div class="p-3 flex justify-between items-center border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-lg font-black tracking-tight {{ $isToday ? 'text-teal-600 dark:text-teal-400' : 'text-slate-300 dark:text-slate-600 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition' }}">
                                {{ $day['date']->format('d') }}
                            </span>
                            @if($isToday)
                                <div class="w-1.5 h-1.5 rounded-full bg-teal-500 shadow-[0_0_4px_rgba(20,184,166,0.8)]"></div>
                            @endif
                        </div>

                        <!-- Shift Content Area -->
                        <div class="flex-1 p-2 flex flex-col justify-center gap-2">
                            @if($shift)
                                @if($shift->shift_type == 'Off')
                                    <div class="flex flex-col items-center gap-1 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 dark:group-hover:text-slate-500 transition">
                                        <i class="fas fa-mug-hot text-xs"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wide">Nghỉ</span>
                                    </div>
                                @else
                                    <div class="w-full py-2 px-2 rounded-xl flex flex-col items-center gap-2 border {{ $shift->shift_type == 'Morning' ? 'bg-orange-50 dark:bg-orange-900/10 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-500/20' : 'bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-500/20' }}">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $shift->shift_type == 'Morning' ? 'bg-orange-100 dark:bg-orange-900/40 text-orange-500' : 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-500' }}">
                                            <i class="fas {{ $shift->shift_type == 'Morning' ? 'fa-sun' : 'fa-moon' }}"></i>
                                        </div>
                                        <div class="text-center">
                                            <span class="block text-xs font-bold uppercase mb-0.5">{{ $shift->shift_type == 'Morning' ? 'Ca Sáng' : 'Chiều' }}</span>
                                            <span class="block text-[10px] font-mono opacity-80">{{ $shift->hours }}</span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="h-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="w-8 h-8 rounded-full border border-dashed border-slate-300 dark:border-slate-600 text-slate-300 dark:text-slate-600 flex items-center justify-center text-xs hover:border-teal-400 hover:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Footer Line if Active -->
                        @if($shift && $shift->status == 'active')
                            <div class="h-1 w-full bg-teal-500"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
