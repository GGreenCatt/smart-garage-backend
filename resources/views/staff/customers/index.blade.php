@extends('layouts.staff')

@section('title', 'Khách Hàng')

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-[#1e293b] p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 transition-colors">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-users text-teal-500"></i> Khách Hàng
        </h1>
        
        <form action="{{ route('staff.customers.index') }}" method="GET" class="flex-1 w-full md:w-auto flex gap-3 justify-end">
            <!-- Search -->
            <div class="relative w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên, email, sđt..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-[#0B1120] border-none rounded-xl font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500/20 outline-none transition placeholder-slate-400 dark:placeholder-slate-500">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
            </div>
            
            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white w-10 h-10 rounded-xl flex items-center justify-center transition shadow-lg shadow-teal-500/20">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <!-- Customers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto pb-safe pr-1">
        @foreach($customers as $customer)
        <a href="{{ route('staff.customers.show', $customer->id) }}" class="bg-white dark:bg-[#1e293b] p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-lg hover:border-teal-200 dark:hover:border-teal-900 transition group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center text-xl font-bold text-slate-500 dark:text-slate-400 group-hover:from-teal-400 group-hover:to-teal-600 group-hover:text-white transition shadow-inner">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-800 dark:text-white truncate group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">{{ $customer->name }}</h3>
                    <p class="text-xs text-slate-400 truncate">Tham gia: {{ $customer->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <i class="fas fa-phone text-slate-300 dark:text-slate-600 w-5 text-center"></i>
                    <span class="font-mono">{{ $customer->phone ?? 'Chưa cập nhật' }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <i class="fas fa-envelope text-slate-300 dark:text-slate-600 w-5 text-center"></i>
                    <span class="truncate">{{ $customer->email }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-50 dark:border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-car text-slate-400 dark:text-slate-600 text-xs"></i>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $customer->vehicles_count }} xe</span>
                </div>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-1 rounded-lg group-hover:bg-teal-100 dark:group-hover:bg-teal-900/50 transition">Xem chi tiết</span>
            </div>
        </a>
        @endforeach
        
        @if($customers->isEmpty())
        <div class="col-span-full text-center py-12 opacity-50">
            <i class="fas fa-users-slash text-4xl text-slate-300 mb-3"></i>
            <p class="text-slate-500 font-medium">Không tìm thấy khách hàng nào</p>
        </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="mt-auto">
        {{ $customers->appends(request()->query())->links() }}
    </div>
</div>
@endsection
