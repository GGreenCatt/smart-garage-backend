@extends('layouts.admin')

@section('title', 'Customer Profile')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.customers.index') }}" class="hover:text-indigo-400 transition">Customers</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">{{ $customer->name }}</span>
    </div>

    <!-- Profile Header -->
    <div class="glass-panel p-8 rounded-2xl border border-slate-700/50 flex flex-col md:flex-row gap-8 items-center md:items-start">
        <img src="https://ui-avatars.com/api/?name={{ $customer->name }}&background=6366f1&color=fff&size=128" class="w-32 h-32 rounded-2xl shadow-2xl border-4 border-slate-800">
        <div class="flex-1 text-center md:text-left space-y-2">
            <h1 class="text-3xl font-bold text-white">{{ $customer->name }}</h1>
            <div class="flex flex-wrap justify-center md:justify-start gap-4 text-slate-400">
                <span class="flex items-center gap-2"><i class="fas fa-envelope text-indigo-500"></i> {{ $customer->email ?? 'No Email' }}</span>
                <span class="flex items-center gap-2"><i class="fas fa-phone text-indigo-500"></i> {{ $customer->phone ?? 'Not Provided' }}</span>
                <span class="flex items-center gap-2"><i class="fas fa-calendar text-indigo-500"></i> Joined {{ $customer->created_at->format('M Y') }}</span>
            </div>
            <div class="pt-4 flex justify-center md:justify-start gap-3">
                 <button class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition border border-slate-600"><i class="fas fa-edit mr-2"></i> Edit Details</button>
                 <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition shadow-lg shadow-indigo-500/20"><i class="fas fa-comment-alt mr-2"></i> Message</button>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
            <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700 text-center">
                <div class="text-2xl font-bold text-white">{{ $customer->vehicles->count() }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Vehicles</div>
            </div>
            <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700 text-center">
                <div class="text-2xl font-bold text-green-400">{{ $customer->vehicles->sum(fn($v) => $v->repairOrders->count()) }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Orders</div>
            </div>
        </div>
    </div>

    <!-- Vehicles & Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vehicles -->
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fas fa-car text-indigo-500"></i> Garage Assets</h2>
            @foreach($customer->vehicles as $vehicle)
            <div class="glass-panel p-5 rounded-xl border border-slate-700/50 hover:border-indigo-500/50 transition group">
                <div class="flex justify-between items-start">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-slate-800 rounded-lg flex items-center justify-center text-2xl">
                             @if($vehicle->type == 'motorcycle') <i class="fas fa-motorcycle text-slate-500"></i>
                             @elseif($vehicle->type == 'truck') <i class="fas fa-truck text-slate-500"></i>
                             @else <i class="fas fa-car text-slate-500"></i> @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-white group-hover:text-indigo-400 transition">{{ $vehicle->model }}</h3>
                            <div class="font-mono text-indigo-300 font-bold bg-indigo-500/10 px-2 py-0.5 rounded inline-block text-xs mt-1 border border-indigo-500/20">{{ $vehicle->license_plate }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="text-slate-500 hover:text-white"><i class="fas fa-external-link-alt"></i></a>
                </div>
                <div class="mt-4 flex justify-between text-xs text-slate-400">
                    <span>VIN: {{ $vehicle->vin ?? 'N/A' }}</span>
                    <span>{{ $vehicle->repairOrders->count() }} Service Records</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Recent Activity -->
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fas fa-history text-indigo-500"></i> Recent Service History</h2>
            <div class="glass-panel rounded-xl border border-slate-700/50 overflow-hidden">
                <table class="w-full text-left text-sm text-slate-400">
                    <tbody class="divide-y divide-slate-800">
                        @foreach($customer->vehicles->flatMap->repairOrders->sortByDesc('created_at')->take(5) as $order)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-white">{{ $order->service_type }}</div>
                                <div class="text-xs text-slate-500">{{ $order->vehicle->license_plate }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="px-2 py-1 rounded text-[10px] uppercase font-bold 
                                    {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-blue-500/10 text-blue-400' }}">
                                    {{ $order->status }}
                                </span>
                                <div class="text-[10px] text-slate-600 mt-1">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
