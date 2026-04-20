@extends('layouts.admin')

@section('title', 'Customer Management')

@section('content')
<!-- Custom Tailwind Config for this page's specific colors -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#6366f1", // Indigo 500 - Vibrant accent
                    primary_hover: "#4f46e5",
                    "background-light": "#f3f4f6", // Gray 100
                    "background-dark": "#0B0E14", // Deep Space / Midnight
                    "surface-light": "#ffffff",
                    "surface-dark": "#161b22", // Matching Layout Dark Mode
                    "surface-elevated-dark": "#1C2433",
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                    display: ['Inter', 'sans-serif'],
                },
                boxShadow: {
                    'glow-green': '0 0 10px rgba(16, 185, 129, 0.3)',
                    'glow-primary': '0 0 15px rgba(99, 102, 241, 0.25)',
                }
            },
        },
    };
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 20px; }
    table { border-spacing: 0 0.75rem; border-collapse: separate; }
</style>

<div class="space-y-8 font-sans">
    <!-- Header removed, using Layout's header -->

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Customers -->
        <div class="bg-surface-dark p-6 rounded-xl border border-gray-800 shadow-sm hover:shadow-md transition-shadow hover:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Total Customers</p>
                    <h3 class="text-3xl font-bold mt-2 text-white">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="p-3 bg-blue-900/20 rounded-lg text-blue-400">
                    <span class="material-icons-round">groups</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                 <span class="text-emerald-500 flex items-center font-medium">
                    <span class="material-icons-round text-base mr-1">trending_up</span> +{{ $stats['new_this_month'] }}
                </span>
                <span class="text-gray-400 ml-2">this month</span>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="bg-surface-dark p-6 rounded-xl border border-gray-800 shadow-sm hover:shadow-md transition-shadow hover:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Active Accounts</p>
                    <h3 class="text-3xl font-bold mt-2 text-white">{{ number_format($stats['active']) }}</h3>
                </div>
                <div class="p-3 bg-emerald-900/20 rounded-lg text-emerald-400">
                    <span class="material-icons-round">verified_user</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">Registered Users</span>
            </div>
        </div>

        <!-- New Customers -->
        <div class="bg-surface-dark p-6 rounded-xl border border-gray-800 shadow-sm hover:shadow-md transition-shadow hover:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">New Month</p>
                    <h3 class="text-3xl font-bold mt-2 text-white">{{ number_format($stats['new_this_month']) }}</h3>
                </div>
                <div class="p-3 bg-purple-900/20 rounded-lg text-purple-400">
                    <span class="material-icons-round">person_add</span>
                </div>
            </div>
             <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400 font-medium">Current Month</span>
            </div>
        </div>

        <!-- Loyalty Leaders -->
        <div class="bg-surface-dark p-6 rounded-xl border border-gray-800 shadow-sm hover:shadow-md transition-shadow hover:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Loyalty Leaders</p>
                    <h3 class="text-3xl font-bold mt-2 text-white">{{ number_format($stats['loyalty']) }}</h3>
                </div>
                <div class="p-3 bg-yellow-900/20 rounded-lg text-yellow-400">
                    <span class="material-icons-round">loyalty</span>
                </div>
            </div>
             <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400 font-medium">> 2 Vehicles</span>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-surface-dark p-4 rounded-xl border border-gray-800">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="relative w-full md:w-3/5 lg:w-1/2">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons-round text-gray-400">search</span>
            </div>
            <input name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-12 py-3 bg-surface-elevated-dark border-gray-700 rounded-lg focus:ring-primary focus:border-primary text-gray-100 placeholder-gray-400 sm:text-sm transition-all" placeholder="Search by name, email, phone or vehicle plate..." type="text"/>
        </form>
        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <button class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary_hover text-white rounded-lg text-sm font-medium shadow-glow-primary transition-all transform hover:scale-105">
                <span class="material-icons-round text-lg">add</span>
                <span>Add Customer</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-6 pb-2 font-semibold">Customer</th>
                    <th class="px-6 pb-2 font-semibold">Contact Info</th>
                    <th class="px-6 pb-2 font-semibold text-center">Account Status</th>
                    <th class="px-6 pb-2 font-semibold text-center">Vehicles</th>
                    <th class="px-6 pb-2 font-semibold">Join Date</th>
                    <th class="px-6 pb-2 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="space-y-4">
                @forelse($customers as $customer)
                <tr class="bg-surface-dark hover:bg-surface-elevated-dark transition-all duration-200 group shadow-none hover:shadow-lg rounded-lg cursor-pointer" onclick="window.location='{{ route('admin.customers.show', $customer->id) }}'">
                    <td class="px-6 py-4 rounded-l-xl border-y border-l border-gray-800 group-hover:border-primary/30">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-indigo-900/30 text-indigo-400 flex items-center justify-center font-bold text-lg border border-indigo-700">
                                {{ substr($customer->name, 0, 2) }}
                            </div>
                            <div>
                                <div class="font-bold text-white text-base">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">ID: #{{ $customer->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 border-y border-gray-800 group-hover:border-primary/30">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2 text-sm text-gray-300 hover:text-primary transition-colors group/link">
                                <span class="material-icons-round text-gray-400 group-hover/link:text-primary text-base">call</span>
                                {{ $customer->phone ?? 'N/A' }}
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400 hover:text-primary transition-colors group/link">
                                <span class="material-icons-round text-gray-400 group-hover/link:text-primary text-base">email</span>
                                {{ $customer->email ?? 'N/A' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center border-y border-gray-800 group-hover:border-primary/30">
                        @if(empty($customer->is_guest))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-glow-green">
                            <span class="material-icons-round text-sm">check_circle</span>
                            Registered
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-700/50 text-gray-400 border border-gray-600">
                            <span class="material-icons-round text-sm">person_off</span>
                            No Account
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center border-y border-gray-800 group-hover:border-primary/30">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700">
                            <span class="material-icons-round text-gray-400">directions_car</span>
                            <span class="font-bold text-gray-200">{{ $customer->vehicles_count ?? 0 }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm border-y border-gray-800 group-hover:border-primary/30">
                        {{ optional($customer->created_at)->format('d/m/Y') ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 rounded-r-xl text-right border-y border-r border-gray-800 group-hover:border-primary/30">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="p-2 text-primary hover:bg-primary hover:text-white rounded-lg border border-primary/30 transition-all text-xs font-medium uppercase tracking-wider block text-center">
                                View
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No customers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="p-5 border-t border-gray-800">
        {{ $customers->links() }}
    </div>
</div>
@endsection
