@extends('layouts.admin')

@section('title', 'Service Catalog')

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-between items-center bg-slate-900/50 p-4 rounded-xl border border-slate-700">
        <form action="{{ route('admin.services.index') }}" method="GET" class="relative w-96">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm Mã, Tên dịch vụ..." class="w-full bg-slate-800 border border-slate-600 rounded-lg pl-10 pr-4 py-2 text-slate-200 focus:outline-none focus:border-indigo-500">
            <i class="fas fa-search absolute left-3 top-3 text-slate-500"></i>
        </form>
        <button onclick="document.getElementById('addServiceModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition shadow-lg shadow-indigo-500/20">
            <i class="fas fa-plus mr-2"></i> Thêm Dịch Vụ
        </button>
    </div>

    <!-- Service Table -->
    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-500">
                <tr>
                    <th class="px-6 py-4">Dịch Vụ</th>
                    <th class="px-6 py-4">Danh Mục</th>
                    <th class="px-6 py-4">Thời Gian (Phút)</th>
                    <th class="px-6 py-4 text-right">Đơn Giá ($)</th>
                    <th class="px-6 py-4 text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($services as $service)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-800 rounded flex items-center justify-center font-mono text-xs text-slate-500 border border-slate-700">{{ substr($service->code, -3) }}</div>
                            <div>
                                <h3 class="font-bold text-white">{{ $service->name }}</h3>
                                <div class="text-[10px] font-mono text-indigo-400">CODE: {{ $service->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-slate-700 rounded text-[10px] uppercase font-bold text-slate-300">{{ $service->category }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                             <i class="far fa-clock text-slate-500"></i> {{ $service->estimated_duration }} mins
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-white">
                        ${{ number_format($service->base_price, 2) }}
                    </td>
                    <td class="px-6 py-4 text-right">
                         <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Xóa dịch vụ này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 transition"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">No services found in catalog.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-800">{{ $services->links() }}</div>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                 <h3 class="font-bold text-white text-lg">Thêm Dịch Vụ Mới</h3>
                 <button onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.services.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                     <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tên Dịch Vụ</label>
                    <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: Thay Nhớt">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mã Dịch Vụ</label>
                        <input type="text" name="code" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono uppercase focus:border-indigo-500 focus:outline-none" placeholder="SVC-001">
                    </div>
                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Danh Mục</label>
                        <select name="category" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                            <option value="maintenance">Bảo Dưỡng</option>
                            <option value="repair">Sửa Chữa</option>
                            <option value="diagnosis">Chẩn Đoán</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Giá Cơ Bản ($)</label>
                        <input type="number" step="0.01" name="base_price" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Thời Gian (Phút)</label>
                        <input type="number" name="estimated_duration" value="60" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>
                 <div>
                     <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mô Tả (Tùy chọn)</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg mt-4">Thêm Vào Danh Mục</button>
            </form>
        </div>
    </div>
</div>
@endsection
