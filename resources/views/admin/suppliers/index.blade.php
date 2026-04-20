@extends('layouts.admin')

@section('title', 'Supplier Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-slate-900/50 p-4 rounded-xl border border-slate-700">
        <div>
            <h2 class="text-xl font-bold text-white">Suppliers</h2>
            <p class="text-sm text-slate-400">Manage your parts providers</p>
        </div>
        <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition shadow-lg shadow-indigo-500/20">
            <i class="fas fa-plus mr-2"></i> Add Supplier
        </button>
    </div>

    <!-- Supplier Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($suppliers as $supplier)
        <div class="glass-panel p-6 rounded-2xl border border-slate-700/50 hover:border-indigo-500/50 transition">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-800 rounded-lg flex items-center justify-center text-xl text-indigo-400 font-bold border border-slate-700">
                        {{ substr($supplier->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-lg">{{ $supplier->name }}</h3>
                        <div class="text-xs text-slate-500">{{ $supplier->contact_person }}</div>
                    </div>
                </div>
                <div class="relative group">
                    <button class="text-slate-500 hover:text-white"><i class="fas fa-ellipsis-v"></i></button>
                    <!-- Dropdown could go here -->
                </div>
            </div>
            
            <div class="space-y-3 mb-6">
                <div class="flex items-center gap-3 text-sm text-slate-400">
                    <i class="fas fa-phone w-4 text-center"></i> {{ $supplier->phone ?? 'N/A' }}
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-400">
                    <i class="fas fa-envelope w-4 text-center"></i> {{ $supplier->email ?? 'N/A' }}
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-400 truncate">
                    <i class="fas fa-map-marker-alt w-4 text-center"></i> {{ $supplier->address ?? 'No Address' }}
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700/50 flex justify-between items-center">
                <span class="text-sm font-bold text-white">{{ $supplier->parts_count }} <span class="text-slate-500 font-normal">Parts Supplied</span></span>
                <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Delete this supplier?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs uppercase font-bold">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{ $suppliers->links() }}

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                 <h3 class="font-bold text-white text-lg">Add New Supplier</h3>
                 <button onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.suppliers.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Company Name</label>
                    <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Contact Person</label>
                        <input type="text" name="contact_person" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg mt-2">Create Supplier</button>
            </form>
        </div>
    </div>
</div>
@endsection
