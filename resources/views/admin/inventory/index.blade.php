@extends('layouts.admin')

@section('title', 'Kho & Vật Tư')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Giá trị tồn kho</p>
            <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($totalValue, 0, ',', '.') }}đ</h3>
            <p class="mt-2 text-xs text-slate-500">Tính theo giá nhập</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Sắp hết hàng</p>
            <h3 class="mt-2 text-2xl font-black text-red-300">{{ $lowStockCount }}</h3>
            <p class="mt-2 text-xs text-red-300">Dưới mức tối thiểu</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Cần nhập thêm</p>
            <h3 class="mt-2 text-2xl font-black text-amber-300">{{ $safetyStockCount }}</h3>
            <p class="mt-2 text-xs text-amber-300">Dưới mức an toàn</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Tồn kho ổn</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-300">{{ $healthyStockCount }}</h3>
            <p class="mt-2 text-xs text-slate-500">Trên mức an toàn</p>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-white">
                <span class="material-icons-round text-indigo-400">inventory</span>
                Kho & Vật Tư
            </h2>
            <p class="mt-1 text-sm text-slate-400">Theo dõi tồn kho, nhập kho, xuất kho và cảnh báo vật tư.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.inventory.transactions') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-slate-200 transition hover:bg-slate-700">
                <span class="material-icons-round text-sm">history</span>
                Lịch sử kho
            </a>
            @can('manage_inventory')
                <button onclick="document.getElementById('addPartModal').showModal()" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
                    <span class="material-icons-round text-sm">add</span>
                    Thêm vật tư
                </button>
            @endcan
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.inventory.index') }}" method="GET" class="glass-panel rounded-2xl border border-slate-700/50 p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_220px_auto_auto]">
            <div class="relative">
                <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                <input name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-slate-700 bg-slate-900/70 py-3 pl-10 pr-4 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none" placeholder="Tìm tên, SKU hoặc danh mục">
            </div>
            <select name="stock_status" class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                <option value="all">Tất cả tồn kho</option>
                <option value="low" @selected(request('stock_status') === 'low')>Sắp hết hàng</option>
                <option value="warning" @selected(request('stock_status') === 'warning')>Cần nhập thêm</option>
                <option value="ok" @selected(request('stock_status') === 'ok')>Tồn kho ổn</option>
            </select>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">Lọc</button>
            <a href="{{ route('admin.inventory.index') }}" class="rounded-lg bg-slate-800 px-5 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white">Xóa lọc</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900/60 shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="border-b border-slate-700/50 bg-white/5 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Vật tư</th>
                        <th class="px-6 py-4">Tồn kho</th>
                        <th class="px-6 py-4">Giá nhập / bán</th>
                        <th class="px-6 py-4">Nhà cung cấp</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($parts as $part)
                        @php
                            $stockClass = $part->stock_quantity <= $part->min_stock ? 'text-red-300' : ($part->stock_quantity <= $part->safety_stock ? 'text-amber-300' : 'text-emerald-300');
                            $stockLabel = $part->stock_quantity <= $part->min_stock ? 'Sắp hết' : ($part->stock_quantity <= $part->safety_stock ? 'Cần nhập' : 'Ổn');
                        @endphp
                        <tr class="transition hover:bg-white/[0.04]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/10 font-bold text-indigo-300">{{ mb_substr($part->name, 0, 1) }}</div>
                                    <div>
                                        <div class="font-bold text-white">{{ $part->name }}</div>
                                        <div class="text-xs text-slate-500">SKU: {{ $part->sku }} | {{ $part->category }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-black {{ $stockClass }}">{{ $part->stock_quantity }}</span>
                                    <span class="text-xs text-slate-600">đơn vị</span>
                                </div>
                                <div class="text-xs {{ $stockClass }}">{{ $stockLabel }} | Tối thiểu: {{ $part->min_stock }}, an toàn: {{ $part->safety_stock }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-200">{{ number_format($part->selling_price, 0, ',', '.') }}đ</div>
                                <div class="text-xs text-slate-500">Nhập: {{ number_format($part->purchase_price, 0, ',', '.') }}đ</div>
                            </td>
                            <td class="px-6 py-4">{{ $part->supplier->name ?? 'Chưa có' }}</td>
                            <td class="px-6 py-4 text-right">
                                @can('manage_inventory')
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openStockModal({{ $part->id }}, @js($part->name), 'in')" class="rounded-lg bg-emerald-500/10 p-2 text-emerald-300 transition hover:bg-emerald-500/20" title="Nhập kho">
                                            <span class="material-icons-round text-lg">login</span>
                                        </button>
                                        <button onclick="openStockModal({{ $part->id }}, @js($part->name), 'out')" class="rounded-lg bg-orange-500/10 p-2 text-orange-300 transition hover:bg-orange-500/20" title="Xuất kho">
                                            <span class="material-icons-round text-lg">logout</span>
                                        </button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">Chưa có vật tư phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700/50 px-6 py-4">
            {{ $parts->links() }}
        </div>
    </div>
</div>

<dialog id="addPartModal" class="w-full max-w-2xl rounded-2xl border border-slate-700 bg-slate-900 p-6 text-white shadow-2xl backdrop:bg-black/80">
    <div class="mb-6 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-xl font-bold">
            <span class="material-icons-round text-indigo-400">add_circle</span>
            Thêm vật tư mới
        </h3>
        <button onclick="document.getElementById('addPartModal').close()" class="rounded-full p-2 text-slate-500 transition hover:bg-slate-800 hover:text-white"><span class="material-icons-round">close</span></button>
    </div>

    <form action="{{ route('admin.inventory.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Tên vật tư</label>
                <input name="name" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Mã SKU</label>
                <input name="sku" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 font-mono text-white outline-none focus:border-indigo-500">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Danh mục</label>
            <input name="category" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500" placeholder="VD: Lốp, Nhớt, Phanh">
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Giá nhập</label>
                <input type="number" name="purchase_price" min="0" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Giá bán</label>
                <input type="number" name="selling_price" min="0" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Tồn đầu</label>
                <input type="number" name="stock_quantity" min="0" value="0" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Mức tối thiểu</label>
                <input type="number" name="min_stock" min="0" value="5" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Mức an toàn</label>
                <input type="number" name="safety_stock" min="0" value="10" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Nhà cung cấp</label>
            <select name="supplier_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500">
                <option value="">Chưa chọn</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="w-full rounded-xl bg-indigo-600 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Lưu vật tư</button>
    </form>
</dialog>

<dialog id="stockModal" class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 text-white shadow-2xl backdrop:bg-black/80">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-lg font-bold" id="stockModalTitle">Cập nhật tồn kho</h3>
        <button onclick="document.getElementById('stockModal').close()" class="rounded-full p-1 text-slate-500 transition hover:bg-slate-800 hover:text-white"><span class="material-icons-round">close</span></button>
    </div>
    <form id="stockForm" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="type" id="stockType">
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Vật tư</label>
            <div class="text-lg font-bold text-white" id="stockProductName"></div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Số lượng</label>
            <input type="number" name="quantity" min="1" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-center text-lg font-bold text-white outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Ghi chú</label>
            <textarea name="note" rows="2" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500"></textarea>
        </div>
        <button class="w-full rounded-xl py-3 font-bold text-white transition" id="stockSubmitBtn">Xác nhận</button>
    </form>
</dialog>

<script>
function openStockModal(id, name, type) {
    const modal = document.getElementById('stockModal');
    const form = document.getElementById('stockForm');
    const title = document.getElementById('stockModalTitle');
    const btn = document.getElementById('stockSubmitBtn');
    form.action = `/admin/inventory/${id}/stock`;
    document.getElementById('stockProductName').innerText = name;
    document.getElementById('stockType').value = type;
    if (type === 'in') {
        title.innerHTML = '<span class="material-icons-round text-emerald-300">login</span> Nhập kho';
        btn.className = 'w-full rounded-xl py-3 font-bold text-white transition bg-emerald-600 hover:bg-emerald-500';
        btn.innerText = 'Nhập kho';
    } else {
        title.innerHTML = '<span class="material-icons-round text-orange-300">logout</span> Xuất kho';
        btn.className = 'w-full rounded-xl py-3 font-bold text-white transition bg-orange-600 hover:bg-orange-500';
        btn.innerText = 'Xuất kho';
    }
    modal.showModal();
}
</script>
@endsection
