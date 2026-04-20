@props(['order', 'status'])

<div onclick="window.location='{{ route('staff.order.show', $order->id) }}'" class="hud-card p-5 {{ 'status-'.$status }} cursor-pointer group">
    
    <!-- Top Row: Plate & ID -->
    <div class="flex justify-between items-start mb-4">
        <div>
            <div class="bg-black/40 text-white font-mono font-bold text-lg px-3 py-1 rounded-lg border border-white/10 tracking-widest inline-block shadow-inner">
                {{ $order->vehicle->license_plate }}
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase mt-2 tracking-wide">{{ $order->vehicle->model }}</p>
        </div>
        <div class="text-right">
            @if($status == 'waiting')
                <span class="text-[10px] font-black uppercase text-amber-500 bg-amber-500/10 px-2 py-1 rounded border border-amber-500/20 animate-pulse">Waiting</span>
            @elseif($status == 'doing')
                <span class="text-[10px] font-black uppercase text-teal-400 bg-teal-400/10 px-2 py-1 rounded border border-teal-400/20">Active</span>
            @else
                <span class="text-[10px] font-black uppercase text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded border border-emerald-400/20">Done</span>
            @endif
        </div>
    </div>

    <!-- Active Task Visualization -->
    <div class="mb-4 space-y-2">
        @if($status == 'doing')
            <div class="flex items-center gap-2">
                <i class="fas fa-cog fa-spin text-teal-400 text-xs"></i>
                <span class="text-xs font-mono text-teal-200 truncate">Machine Check</span>
            </div>
            <!-- Progress Bar -->
            <div class="h-1.5 w-full bg-slate-700/50 rounded-full overflow-hidden">
                <div class="h-full bg-teal-500 w-[60%] shadow-[0_0_10px_#2dd4bf]"></div>
            </div>
        @elseif($status == 'waiting')
             <div class="flex items-center gap-2">
                <i class="fas fa-clock text-amber-500 text-xs"></i>
                <span class="text-xs font-mono text-amber-200">Queued for Technician</span>
            </div>
             <div class="h-1.5 w-full bg-slate-700/50 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 w-[10%]"></div>
            </div>
        @endif
        
        <!-- Owner Info (Minimal) -->
        <div class="flex items-center gap-2 pt-2 text-slate-500 text-xs">
             <i class="fas fa-user-circle"></i>
             <span>{{ $order->vehicle->user->name ?? 'Guest Client' }}</span>
        </div>
    </div>

    <!-- Holographic Overlay on Hover -->
    <div class="absolute inset-0 bg-gradient-to-t from-teal-500/10 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 pointer-events-none"></div>
</div>
