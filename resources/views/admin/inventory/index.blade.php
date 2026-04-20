@extends('layouts.admin')

@section('title', 'Quản Lý Kho')

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
                    "background-light": "#f3f4f6",
                    "background-dark": "#0f172a",
                    "surface-light": "#ffffff",
                    "surface-dark": "#1e293b",
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
    /* Hide scrollbar for cleaner look */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="font-sans text-gray-100 antialiased min-h-screen">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Value -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Tổng Giá Trị Kho</p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($totalValue, 0, ',', '.') }} ₫</h3>
                </div>
                <div class="p-2 bg-indigo-500/10 rounded-lg">
                    <span class="material-icons-round text-indigo-500">monetization_on</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <span>Giá trị vốn tồn kho</span>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Sắp Hết Hàng</p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ $lowStockCount }}</h3>
                </div>
                <div class="p-2 bg-danger/10 rounded-lg">
                    <span class="material-icons-round text-danger">production_quantity_limits</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-danger">
                <span>Dưới định mức tối thiểu</span>
            </div>
        </div>

        <!-- Safety Stock -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Cần Nhập Thêm</p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ $safetyStockCount }}</h3>
                </div>
                <div class="p-2 bg-warning/10 rounded-lg">
                    <span class="material-icons-round text-warning">low_priority</span>
                </div>
            </div>
             <div class="mt-4 flex items-center text-xs text-warning">
                <span>Dưới mức an toàn</span>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold flex items-center gap-2 text-white w-full md:w-auto">
            <span class="material-icons-round text-primary">inventory</span>
            Danh Sách Phụ Tùng
        </h2>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Search -->
            <form class="relative group" action="{{ route('admin.inventory.index') }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-icons-round text-gray-400 group-focus-within:text-primary transition-colors">search</span>
                </div>
                <input name="search" value="{{ request('search') }}" class="block w-full sm:w-64 pl-10 pr-3 py-2 border border-gray-600 rounded-lg leading-5 bg-surface-dark/50 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition-all shadow-sm" placeholder="Tìm tên, SKU..." type="text"/>
            </form>
            
            <button onclick="document.getElementById('addPartModal').showModal()" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-indigo-600 text-white font-medium transition-colors shadow-lg shadow-indigo-500/20">
                <span class="material-icons-round text-sm">add</span> Thêm Mới
            </button>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-surface-dark border border-glass-border rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-gray-800/50 text-gray-200 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Sản Phẩm</th>
                        <th class="px-6 py-4">Tồn Kho</th>
                        <th class="px-6 py-4">Giá Nhập / Bán</th>
                        <th class="px-6 py-4">Nhà Cung Cấp</th>
                        <th class="px-6 py-4 text-right">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($parts as $part)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-indigo-900/30 flex items-center justify-center text-primary font-bold">
                                    {{ substr($part->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white text-base">{{ $part->name }}</div>
                                    <div class="text-xs font-mono text-gray-500">SKU: {{ $part->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold {{ $part->stock_quantity <= $part->min_stock ? 'text-danger' : ($part->stock_quantity <= $part->safety_stock ? 'text-warning' : 'text-success') }}">
                                    {{ $part->stock_quantity }}
                                </span>
                                <span class="text-xs text-gray-600">đv</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-300">{{ number_format($part->selling_price, 0, ',', '.') }} ₫</div>
                            <div class="text-xs text-gray-600">Nhập: {{ number_format($part->purchase_price, 0, ',', '.') }} ₫</div>
                        </td>
                         <td class="px-6 py-4">
                            {{ $part->supplier->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex justify-end gap-2">
                                <button onclick="openStockModal({{ $part->id }}, '{{ $part->name }}', 'in')" class="p-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-500 transition-colors" title="Nhập Kho">
                                    <span class="material-icons-round text-lg">login</span>
                                </button>
                                <button onclick="openStockModal({{ $part->id }}, '{{ $part->name }}', 'out')" class="p-2 rounded-lg bg-orange-500/10 hover:bg-orange-500/20 text-orange-500 transition-colors" title="Xuất Kho">
                                    <span class="material-icons-round text-lg">logout</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Chưa có dữ liệu phụ tùng.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parts->hasPages())
        <div class="p-4 border-t border-gray-800">
            {{ $parts->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Part Modal -->
<dialog id="addPartModal" class="bg-surface-dark text-white border border-gray-700 rounded-2xl shadow-2xl p-6 w-[600px] backdrop:bg-black/80">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-xl flex items-center gap-2">
            <span class="material-icons-round text-primary">add_circle</span>
            Thêm Phụ Tùng Mới
        </h3>
        <button onclick="document.getElementById('addPartModal').close()" class="p-2 hover:bg-slate-800 rounded-full transition-colors text-slate-500"><span class="material-icons-round">close</span></button>
    </div>
    
    <form action="{{ route('admin.inventory.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tên Phụ Tùng</label>
                <input type="text" name="name" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all code-font">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Mã SKU</label>
                <input type="text" name="sku" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all font-mono">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Danh Mục</label>
            <input type="text" name="category" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all code-font" placeholder="Ví dụ: Lốp, Nhớt, Phanh...">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Giá Nhập</label>
                <input type="number" name="purchase_price" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Giá Bán</label>
                <input type="number" name="selling_price" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tồn Đầu</label>
                <input type="number" name="stock_quantity" value="0" class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Min Stock</label>
                <input type="number" name="min_stock" value="5" class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Safety Stock</label>
                <input type="number" name="safety_stock" value="10" class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
            </div>
        </div>

        <div>
             <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nhà Cung Cấp</label>
             <select name="supplier_id" class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white transition-all">
                 <option value="">-- Chọn NCC --</option>
                 @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                 @endforeach
             </select>
        </div>

        <button class="w-full bg-primary hover:bg-primary-hover text-white py-3 rounded-xl font-bold mt-4 shadow-lg shadow-indigo-500/30 transition-all">Lưu Phụ Tùng</button>
    </form>
</dialog>

<!-- Stock Update Modal -->
<dialog id="stockModal" class="bg-surface-dark text-white border border-gray-700 rounded-2xl shadow-2xl p-6 w-[400px] backdrop:bg-black/80">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg flex items-center gap-2" id="stockModalTitle">
            Cập Nhật Tồn Kho
        </h3>
        <button onclick="document.getElementById('stockModal').close()" class="p-1 hover:bg-slate-800 rounded-full transition-colors text-slate-500"><span class="material-icons-round">close</span></button>
    </div>
    
    <form id="stockForm" method="POST" class="space-y-4">
        @csrf
        @method('POST')
        <input type="hidden" name="type" id="stockType">
        
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Sản Phẩm</label>
            <div class="text-white font-bold text-lg" id="stockProductName">Product Name</div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Số Lượng</label>
            <input type="number" name="quantity" min="1" required class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white font-bold text-lg text-center">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Ghi Chú</label>
            <textarea name="note" rows="2" class="w-full bg-background-dark border border-gray-600 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary text-sm text-white"></textarea>
        </div>

        <button class="w-full py-3 rounded-xl font-bold mt-2 shadow-lg transition-all text-white" id="stockSubmitBtn">Xác Nhận</button>
    </form>
</dialog>

<script>
    function openStockModal(id, name, type) {
        const modal = document.getElementById('stockModal');
        const form = document.getElementById('stockForm');
        const title = document.getElementById('stockModalTitle');
        const nameEl = document.getElementById('stockProductName');
        const typeInput = document.getElementById('stockType');
        const btn = document.getElementById('stockSubmitBtn');

        form.action = `/admin/inventory/${id}/stock`;
        nameEl.innerText = name;
        typeInput.value = type;

        if (type === 'in') {
            title.innerHTML = '<span class="material-icons-round text-success">login</span> Nhập Kho';
            btn.className = 'w-full py-3 rounded-xl font-bold mt-2 shadow-lg transition-all text-white bg-success hover:bg-emerald-600 shadow-emerald-500/20';
            btn.innerText = 'Nhập Hàng';
        } else {
             title.innerHTML = '<span class="material-icons-round text-danger">logout</span> Xuất Kho';
            btn.className = 'w-full py-3 rounded-xl font-bold mt-2 shadow-lg transition-all text-white bg-danger hover:bg-red-600 shadow-red-500/20';
             btn.innerText = 'Xuất Hàng';
        }

        modal.showModal();
    }
</script>
@endsection
