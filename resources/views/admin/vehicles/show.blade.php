@extends('layouts.admin')

@section('title', 'Vehicle Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.vehicles.index') }}" class="hover:text-indigo-400 transition">Vehicles</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">{{ $vehicle->license_plate }}</span>
    </div>

    <!-- Vehicle card -->
    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden flex flex-col md:flex-row">
        <!-- Visual -->
        <div class="w-full md:w-1/3 bg-gradient-to-br from-slate-800 to-slate-900 p-8 flex flex-col items-center justify-center border-r border-slate-700/50 relative">
             <div class="w-40 h-40 bg-slate-700/50 rounded-full flex items-center justify-center text-7xl shadow-2xl mb-6">
                 @if($vehicle->type == 'motorcycle') <i class="fas fa-motorcycle text-slate-500"></i>
                 @else <i class="fas fa-car text-slate-500"></i> @endif
             </div>
             <h1 class="text-3xl font-bold text-white text-center">{{ $vehicle->model }}</h1>
             <div class="text-indigo-400 font-mono text-sm mt-1 uppercase tracking-widest">{{ $vehicle->type }}</div>
             
             <div class="absolute top-4 left-4">
                 <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-bold border border-green-500/20">Active</span>
             </div>
        </div>

        <!-- Details -->
        <div class="p-8 flex-1 space-y-8">
            <div class="flex justify-between items-start">
                 <div>
                     <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">License Plate</label>
                     <div class="text-4xl font-black text-white font-mono tracking-wide">{{ $vehicle->license_plate }}</div>
                 </div>
                 <div class="text-right">
                     <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Chassis Number / VIN</label>
                     <div class="text-lg font-mono text-slate-300">{{ $vehicle->vin ?? 'Not Registered' }}</div>
                 </div>
            </div>

            <div class="grid grid-cols-2 gap-8 border-t border-slate-700/50 pt-8">
                 <div>
                     <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Owner Information</label>
                     @if($vehicle->user)
                     <a href="{{ route('admin.customers.show', $vehicle->user->id) }}" class="flex items-center gap-3 group">
                         <img src="https://ui-avatars.com/api/?name={{ $vehicle->user->name }}&background=14b8a6&color=fff" class="w-10 h-10 rounded-lg">
                         <div>
                             <div class="font-bold text-white group-hover:text-indigo-400 transition">{{ $vehicle->user->name }}</div>
                             <div class="text-xs text-slate-500">{{ $vehicle->user->phone }}</div>
                         </div>
                     </a>
                     @else
                     <div class="flex items-center gap-3">
                         <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center"><i class="fas fa-user-slash text-slate-600"></i></div>
                         <div>
                             <div class="font-bold text-slate-400">Guest / Unknown</div>
                             <div class="text-xs text-slate-600">{{ $vehicle->owner_phone ?? 'No contact info' }}</div>
                         </div>
                     </div>
                     @endif
                 </div>

                 <div>
                     <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Service Summary</label>
                     <div class="flex gap-4">
                         <div class="text-center">
                             <div class="text-2xl font-bold text-white">{{ $vehicle->repairOrders->count() }}</div>
                             <div class="text-[10px] text-slate-500 uppercase">Visits</div>
                         </div>
                         <div class="w-px bg-slate-700"></div>
                         <div class="text-center">
                              <div class="text-2xl font-bold text-indigo-400">{{ $vehicle->repairOrders->where('status', 'completed')->count() }}</div>
                             <div class="text-[10px] text-slate-500 uppercase">Completed</div>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Repair Timeline -->
    <div class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fas fa-tools text-indigo-500"></i> Service History</h2>
        
        <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
             @forelse($vehicle->repairOrders->sortByDesc('created_at') as $order)
             <div class="relative pl-8 pb-8 last:pb-0 border-l border-slate-700 last:border-0 group">
                 <div class="absolute left-[-5px] top-1 w-2.5 h-2.5 rounded-full bg-slate-600 group-hover:bg-indigo-500 transition ring-4 ring-slate-900"></div>
                 
                 <div class="flex justify-between items-start">
                     <div>
                         <h3 class="font-bold text-white text-lg group-hover:text-indigo-400 transition">{{ $order->service_type }}</h3>
                         <p class="text-sm text-slate-400 mt-1">Technician: {{ $order->technician->name ?? 'Unassigned' }}</p>
                     </div>
                     <div class="text-right">
                         <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-slate-700 text-slate-300' }}">{{ $order->status }}</span>
                         <div class="text-xs text-slate-500 mt-1">{{ $order->created_at->format('d M Y') }}</div>
                     </div>
                 </div>
             </div>
             @empty
             <div class="text-center py-8 text-slate-500 italic">No service history recorded for this vehicle.</div>
             @endforelse
        </div>
    </div>
</div>
@endsection
