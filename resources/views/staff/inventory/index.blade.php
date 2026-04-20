@extends('layouts.staff')

@section('title', 'Kho Linh Kiện')

@section('content')
<div class="h-full flex flex-col gap-8 pb-10">
    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Items Card -->
        <div class="glass-panel p-6 rounded-3xl border border-white/10 shadow-xl shadow-indigo-500/5 flex items-center gap-5 transition-transform hover:scale-[1.02]">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-500 border border-indigo-500/30">
                <span class="material-icons-round text-3xl">inventory_2</span>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 mb-0.5">Tổng Linh Kiện</p>
                <h3 class="text-3xl font-heading font-black text-slate-800 dark:text-white leading-none">{{ $totalParts }}</h3>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="glass-panel p-6 rounded-3xl border border-white/10 shadow-xl shadow-orange-500/5 flex items-center gap-5 transition-transform hover:scale-[1.02]">
            <div class="w-14 h-14 rounded-2xl {{ $lowStockCount > 0 ? 'bg-orange-500/20 text-orange-500 ring-2 ring-orange-500/20 animate-pulse' : 'bg-slate-500/20 text-slate-500' }} flex items-center justify-center border border-current opacity-80 transition-colors">
                <span class="material-icons-round text-3xl">emergency</span>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 mb-0.5">Sắp Hết Hàng</p>
                <h3 class="text-3xl font-heading font-black {{ $lowStockCount > 0 ? 'text-orange-500' : 'text-slate-800 dark:text-white' }} leading-none">{{ $lowStockCount }}</h3>
            </div>
        </div>

        <!-- Pending Requests Card -->
        <div class="glass-panel p-6 rounded-3xl border border-white/10 shadow-xl shadow-blue-500/5 flex items-center gap-5 transition-transform hover:scale-[1.02]">
            <div class="w-14 h-14 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-500 border border-blue-500/30">
                <span class="material-icons-round text-3xl">history_edu</span>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 mb-0.5">Yêu Cầu Chờ Duyệt</p>
                <h3 class="text-3xl font-heading font-black text-slate-800 dark:text-white leading-none">{{ $pendingRequestsCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Controls & Navigation -->
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Search Bar -->
            <form id="inventorySearchForm" action="{{ route('staff.inventory.index') }}" method="GET" class="relative group w-full lg:w-[450px]">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm theo tên, SKU..." 
                    class="w-full pl-14 pr-6 py-4 bg-white dark:bg-[#0B1120] border border-slate-200 dark:border-white/5 rounded-2xl font-bold text-slate-700 dark:text-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-500 shadow-sm shadow-slate-200/50 dark:shadow-none">
                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <span class="material-icons-round text-2xl">search</span>
                </div>
            </form>

            <!-- Buttons -->
            <div class="flex items-center gap-3 w-full lg:w-auto">
                <a id="lowStockBtn" href="{{ request()->fullUrlWithQuery(['filter' => request('filter') == 'low_stock' ? null : 'low_stock']) }}" 
                    class="flex-1 lg:flex-none h-[58px] px-6 rounded-2xl flex items-center justify-center gap-3 transition-all border {{ request('filter') == 'low_stock' ? 'bg-orange-500 text-white border-orange-400 shadow-lg shadow-orange-500/30' : 'bg-white dark:bg-[#0B1120] border-slate-200 dark:border-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <span class="material-icons-round text-xl">{{ request('filter') == 'low_stock' ? 'filter_list_off' : 'warning_amber' }}</span>
                    <span class="font-black uppercase tracking-wider text-xs">Lọc sắp hết</span>
                </a>
                
                <button onclick="openRequestModal()" class="flex-[2] lg:flex-none h-[58px] px-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 shadow-xl shadow-indigo-500/20 transition-all transform active:scale-95">
                    <span class="material-icons-round text-xl">add_shopping_cart</span>
                    Yêu Cầu Vật Tư
                </button>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="flex items-center gap-3 overflow-x-auto pb-4 no-scrollbar">
            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" 
                class="all-pill px-6 py-2.5 rounded-full transition-all border whitespace-nowrap font-bold text-xs uppercase tracking-widest
                {{ !request('category') ? 'active-pill' : 'inactive-pill' }}">
                Tất cả
            </a>
            @foreach($categories as $cat)
            <a href="{{ request()->fullUrlWithQuery(['category' => $cat]) }}" 
                data-category="{{ $cat }}"
                class="cat-pill px-6 py-2.5 rounded-full transition-all border whitespace-nowrap font-bold text-xs uppercase tracking-widest
                {{ request('category') == $cat ? 'active-pill' : 'inactive-pill' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Processing Indicator -->
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-950/20 backdrop-blur-[2px] z-[80] hidden items-center justify-center transition-all">
        <div class="flex flex-col items-center gap-4 p-8 rounded-[40px] bg-white/10 dark:bg-black/20 border border-white/10 shadow-2xl">
            <div class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
            <p class="text-[10px] font-black text-white uppercase tracking-[0.3em]">Đang tải dữ liệu...</p>
        </div>
    </div>

    <!-- Inventory Grid Container -->
    <div id="inventory-grid-container" class="transition-all duration-500">
        @include('staff.inventory.partials._grid')
    </div>
</div>

<!-- Details Slide-over Panel -->
<div id="detailsPanel" class="fixed inset-y-0 right-0 w-full max-w-lg bg-white dark:bg-[#0B1120] shadow-[-20px_0_100px_rgba(0,0,0,0.3)] z-[100] transform translate-x-full transition-transform duration-700 cubic-bezier(0.4, 0, 0.2, 1) flex flex-col border-l border-white/5">
    <!-- Panel Header -->
    <div class="p-8 h-24 flex items-center justify-between border-b border-slate-100 dark:border-white/5">
        <h3 class="font-heading font-black text-xl uppercase tracking-widest text-slate-800 dark:text-white">Chi Tiết Vật Tư</h3>
        <button onclick="closeDetailsPanel()" class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-500 hover:bg-slate-200 dark:hover:bg-white/10 transition-all flex items-center justify-center group">
            <span class="material-icons-round group-hover:rotate-90 transition-transform">close</span>
        </button>
    </div>

    <!-- Panel Body -->
    <div id="detailsPanelContent" class="flex-1 overflow-y-auto p-10 custom-scrollbar space-y-10">
        <!-- Content will be injected via JS -->
    </div>

    <!-- Panel Footer -->
    <div class="p-10 border-t border-slate-100 dark:border-white/5 bg-gray-50 dark:bg-[#020617]/50">
        <button id="panelRequestBtn" class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[24px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-indigo-600/30 transition-all transform active:scale-[0.98] flex items-center justify-center gap-4 text-sm">
            <span class="material-icons-round text-2xl">shopping_cart_checkout</span>
            Tạo Yêu Cầu Nhập Hàng
        </button>
    </div>
</div>

<!-- Panel Backdrop -->
<div id="panelBackdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-md z-[90] hidden opacity-0 transition-opacity duration-500" onclick="closeDetailsPanel()"></div>

<!-- Refine Material Request Modal -->
<div id="requestModal" class="fixed inset-0 z-[110] hidden items-center justify-center p-6">
    <div class="absolute inset-0 bg-slate-950/90 backdrop-blur-xl opacity-0 transition-opacity duration-500" onclick="closeRequestModal()"></div>
    
    <div class="relative w-full max-w-lg bg-white dark:bg-[#1e293b] rounded-[48px] shadow-2xl p-10 transform scale-95 opacity-0 transition-all duration-500 border border-white/10 overflow-hidden">
        <!-- Decorative bg blobs -->
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-indigo-600/10 rounded-full blur-[80px]"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-teal-600/10 rounded-full blur-[80px]"></div>

        <div class="flex items-center gap-6 mb-8 relative">
            <div class="w-16 h-16 rounded-[22px] bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-2xl shadow-indigo-600/30">
                <span class="material-icons-round text-3xl">post_add</span>
            </div>
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-wider leading-tight">Yêu Cầu Linh Kiện</h3>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-wide mt-1">Hội đồng kỹ thuật / Admin sẽ duyệt yêu cầu này.</p>
            </div>
        </div>
        
        <form id="requestForm" action="{{ route('staff.requests.store') }}" method="POST" class="space-y-8 relative">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Tên Linh Kiện Cần Phụ Trợ</label>
                    <input type="text" name="part_name" id="reqPartName" required 
                           class="w-full px-8 py-5 rounded-[24px] bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-white/5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none text-slate-800 dark:text-white font-bold transition-all placeholder:text-slate-400" 
                           placeholder="VD: Nhớt Motul 300V 10W40...">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Số Lượng</label>
                    <input type="number" name="quantity" required min="1" value="1" 
                           class="w-full px-8 py-5 rounded-[24px] bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-white/5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none text-slate-800 dark:text-white font-bold transition-all">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Mã Phụ Tùng (SKU)</label>
                    <input type="text" name="sku" id="reqPartSku" readonly
                           class="w-full px-8 py-5 rounded-[24px] bg-slate-200/50 dark:bg-slate-800/80 border border-transparent text-slate-500 dark:text-slate-500 font-mono text-sm font-bold transition-all cursor-not-allowed uppercase shadow-inner" 
                           placeholder="SKU-AUTO">
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Ghi chú chi tiết cho Admin</label>
                <textarea name="reason" rows="3" 
                          class="w-full px-8 py-5 rounded-[24px] bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-white/5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none text-slate-800 dark:text-white font-bold transition-all placeholder:text-slate-400" 
                          placeholder="Mô tả mức độ khẩn cấp hoặc khách hàng cần mã này..."></textarea>
            </div>
            
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeRequestModal()" 
                        class="flex-1 py-5 rounded-[24px] bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-black uppercase tracking-[0.2em] text-xs hover:bg-slate-200 dark:hover:bg-white/10 transition-all transform active:scale-[0.98]">
                    Hủy Bỏ
                </button>
                <button type="submit" 
                        class="flex-[2] py-5 bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-[24px] font-black uppercase tracking-[0.2em] text-xs shadow-2xl shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all transform active:scale-[0.98]">
                    Gửi Yêu Cầu
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const panel = document.getElementById('detailsPanel');
    const backdrop = document.getElementById('panelBackdrop');
    const modal = document.getElementById('requestModal');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const gridContainer = document.getElementById('inventory-grid-container');

    // AJAX Filter & Pagination Logic
    async function updateInventory(url, updateUrl = true) {
        gridContainer.style.opacity = '0.5';
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            
            gridContainer.innerHTML = html;
            if (updateUrl) {
                history.pushState(null, '', url);
            }
            
            // Update active state of pills
            updatePpillStates(url);
            
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu:', error);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể tải dữ liệu kho. Vui lòng thử lại.',
                background: '#1e293b',
                color: '#fff'
            });
        } finally {
            gridContainer.style.opacity = '1';
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
        }
    }

    function updatePpillStates(currentUrlString) {
        const url = new URL(currentUrlString);
        const category = url.searchParams.get('category');
        const lowStock = url.searchParams.get('filter') === 'low_stock';
        
        // Update category pills
        document.querySelectorAll('.cat-pill, .all-pill').forEach(pill => {
            const pillCat = pill.dataset.category;
            if ((!category && !pillCat) || (category === pillCat)) {
                pill.classList.add('active-pill');
                pill.classList.remove('inactive-pill');
            } else {
                pill.classList.remove('active-pill');
                pill.classList.add('inactive-pill');
            }
        });

        // Update low stock button
        const lowStockBtn = document.getElementById('lowStockBtn');
        if (lowStockBtn) {
            if (lowStock) {
                lowStockBtn.classList.add('bg-orange-500', 'text-white', 'border-orange-400');
                lowStockBtn.classList.remove('bg-white', 'dark:bg-[#0B1120]', 'text-slate-600');
                lowStockBtn.querySelector('span.material-icons-round').textContent = 'filter_list_off';
            } else {
                lowStockBtn.classList.remove('bg-orange-500', 'text-white', 'border-orange-400');
                lowStockBtn.classList.add('bg-white', 'dark:bg-[#0B1120]', 'text-slate-600');
                lowStockBtn.querySelector('span.material-icons-round').textContent = 'warning_amber';
            }
        }
    }

    // Event Delegation
    document.addEventListener('click', e => {
        const pageLink = e.target.closest('.ajax-pagination a');
        if (pageLink) {
            e.preventDefault();
            updateInventory(pageLink.href);
        }

        const catPill = e.target.closest('.cat-pill, .all-pill');
        if (catPill) {
            e.preventDefault();
            if (catPill.classList.contains('all-pill')) {
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) searchInput.value = '';
            }
            updateInventory(catPill.href);
        }
        
        const lowStockBtn = e.target.closest('#lowStockBtn');
        if (lowStockBtn) {
            e.preventDefault();
            updateInventory(lowStockBtn.href);
        }
    });

    // Handle Search Form
    const searchForm = document.getElementById('inventorySearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);
            const url = `${searchForm.action}?${params.toString()}`;
            updateInventory(url);
        });
    }

    // Handle Browser Back/Forward
    window.addEventListener('popstate', () => {
        updateInventory(window.location.href, false);
    });

    // Details & Modal Functions
    function openDetailsPanel(part) {
        const content = document.getElementById('detailsPanelContent');
        const img = part.image_url || 'https://placehold.co/800x600?text=Parts+Gallery';
        const isLow = part.stock_quantity <= part.min_stock;
        
        content.innerHTML = `
            <div class="rounded-[40px] overflow-hidden aspect-[4/3] bg-slate-100 dark:bg-slate-900 shadow-2xl relative group">
                <img src="${img}" class="w-full h-full object-cover group-hover:scale-110 transition duration-[2s]">
                <div class="absolute inset-x-0 bottom-0 p-10 bg-gradient-to-t from-black/90 via-black/40 to-transparent">
                    <span class="text-xs font-black text-indigo-400 uppercase tracking-[0.3em] mb-3 block">${part.category || 'PHỤ TÙNG CHÍNH HÃNG'}</span>
                    <h2 class="text-3xl font-black text-white leading-tight uppercase tracking-tight">${part.name}</h2>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="p-6 rounded-[28px] bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mã SKU</p>
                    <p class="font-mono text-base font-black text-indigo-500 dark:text-indigo-400 uppercase tracking-wider">${part.sku}</p>
                </div>
                <div class="p-6 rounded-[28px] bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 shadow-sm text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">QR / Barcode</p>
                    <p class="font-mono text-base font-black text-slate-800 dark:text-white uppercase tracking-wider">${part.barcode || 'N/A'}</p>
                </div>
            </div>

            <div class="p-10 rounded-[40px] bg-indigo-600/5 dark:bg-indigo-600/10 border border-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-600/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                <h4 class="text-xs font-black text-slate-800 dark:text-indigo-400 uppercase tracking-[0.3em] flex items-center gap-3 mb-6">
                    <span class="material-icons-round text-xl">speed</span> Trạng Thái Tồn Kho
                </h4>
                
                <div class="flex items-end justify-between gap-8 mb-8">
                    <div class="text-7xl font-heading font-black dark:text-white tracking-tighter leading-none">${part.stock_quantity} <span class="text-sm font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] ml-2">CÁI</span></div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Mức Cảnh Báo</p>
                        <p class="text-xl font-black text-slate-700 dark:text-slate-200">>= ${part.min_stock || 10}</p>
                    </div>
                </div>

                <div class="h-3 w-full bg-slate-200 dark:bg-white/10 rounded-full overflow-hidden shadow-inner flex">
                    <div class="h-full ${isLow ? 'bg-gradient-to-r from-orange-400 to-orange-600' : 'bg-gradient-to-r from-emerald-400 to-emerald-600'} rounded-full transition-all duration-[1.5s] cubic-bezier(0.34, 1.56, 0.64, 1)" 
                         style="width: ${Math.min((part.stock_quantity / (part.min_stock * 2.5)) * 100, 100)}%"></div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between p-8 rounded-[32px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-white/10 shadow-xl shadow-slate-200/50 dark:shadow-none">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                            <span class="material-icons-round">sell</span>
                        </div>
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Giá Bán Lẻ</span>
                    </div>
                    <span class="text-3xl font-heading font-black text-slate-800 dark:text-white">${new Intl.NumberFormat('vi-VN').format(part.selling_price || part.selling_price_suggested)}₫</span>
                </div>
                
                <div class="flex items-center justify-between p-8 rounded-[32px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-white/10">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center text-teal-600">
                            <span class="material-icons-round">storefront</span>
                        </div>
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nhà Cung Cấp</span>
                    </div>
                    <span class="text-sm font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">Hãng Phân Phối</span>
                </div>
            </div>
        `;

        document.getElementById('panelRequestBtn').onclick = () => {
            closeDetailsPanel();
            setTimeout(() => openQuickRequest(part.name, part.sku), 200);
        };

        panel.classList.remove('translate-x-full');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.add('opacity-100'), 50);
    }

    function closeDetailsPanel() {
        panel.classList.add('translate-x-full');
        backdrop.classList.remove('opacity-100');
        setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 500);
    }

    function openRequestModal() {
        document.getElementById('reqPartName').value = '';
        document.getElementById('reqPartSku').value = '';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('opacity-0', 'scale-95');
            modal.querySelector('.bg-slate-950').classList.remove('opacity-0');
        }, 50);
    }

    function openQuickRequest(name, sku) {
        openRequestModal();
        document.getElementById('reqPartName').value = name;
        document.getElementById('reqPartSku').value = sku;
    }

    function closeRequestModal() {
        modal.querySelector('div').classList.add('opacity-0', 'scale-95');
        modal.querySelector('.bg-slate-950').classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 500);
    }
</script>
@endpush

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.15); border-radius: 10px; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.no-click { pointer-events: none; }

.active-pill { background-color: rgb(79 70 229); color: white; border-color: rgb(129 140 248); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2); }
.inactive-pill { background-color: white; color: rgb(100 116 139); border-color: rgb(226 232 240); }
.dark .inactive-pill { background-color: rgb(15 23 42); color: rgb(148 163 184); border-color: rgba(255, 255, 255, 0.05); }
</style>
@endsection
