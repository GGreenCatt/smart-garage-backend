<!-- Inventory Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
    @forelse($parts as $part)
    <div class="bg-white dark:bg-[#1e293b] rounded-3xl border border-slate-200 dark:border-white/10 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group overflow-hidden flex flex-col relative"
         onclick="openDetailsPanel({{ json_encode($part) }})">
        
        <!-- Image Area -->
        <div class="relative h-48 bg-slate-50 dark:bg-slate-900/50 overflow-hidden cursor-pointer">
            <img src="{{ $part->image_url ?? 'https://placehold.co/400x300?text=Part' }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
            
            <!-- Stock Status Pulse Indicator -->
            <div class="absolute top-4 right-4">
                @php
                    $isLow = $part->stock_quantity <= $part->min_stock;
                    $isEmpty = $part->stock_quantity == 0;
                    $colorClass = $isEmpty ? 'bg-red-500 font-bold' : ($isLow ? 'bg-orange-500' : 'bg-emerald-500');
                @endphp
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-black/60 backdrop-blur-md border border-white/10 no-click">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isEmpty ? 'bg-red-500' : ($isLow ? 'bg-orange-500' : 'bg-emerald-500') }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 {{ $isEmpty ? 'bg-red-500' : ($isLow ? 'bg-orange-500' : 'bg-emerald-500') }}"></span>
                    </span>
                    <span class="text-[10px] font-black text-white uppercase tracking-tighter">
                        {{ $isEmpty ? 'Hết hàng' : ($isLow ? 'Sắp hết' : 'Đang có') }}
                    </span>
                </div>
            </div>

            <!-- Hover Overlay -->
            <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/10 transition-colors duration-500"></div>
        </div>

        <!-- Content -->
        <div class="p-5 flex-1 flex flex-col cursor-pointer bg-white dark:bg-[#1e293b]">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.2em]">{{ $part->category ?? 'Phụ tùng' }}</span>
                    <span class="text-[10px] font-mono font-bold text-slate-400 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/5">#{{ $part->sku }}</span>
                </div>
                <h3 class="font-heading font-bold text-slate-800 dark:text-white leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 md:text-base">{{ $part->name }}</h3>
            </div>
            
            <div class="mt-auto flex items-center justify-between pt-4 border-t border-slate-100 dark:border-white/5">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Giá Niêm Yết</p>
                    <span class="text-xl font-heading font-black text-slate-900 dark:text-indigo-400 leading-none">
                        {{ number_format($part->selling_price ?: $part->selling_price_suggested, 0, ',', '.') }}₫
                    </span>
                </div>
                
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Tồn Kho</p>
                    <span class="text-xl font-black {{ $isLow ? 'text-orange-500' : 'text-slate-700 dark:text-white' }} leading-none">{{ $part->stock_quantity }}</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Request Action Button (Slide in from bottom) -->
        <button onclick="event.stopPropagation(); openQuickRequest('{{ $part->name }}', '{{ $part->sku }}')" 
            class="absolute bottom-4 left-1/2 -translate-x-1/2 translate-y-12 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 bg-indigo-600 dark:bg-indigo-600 text-white px-5 py-3 rounded-2xl border border-indigo-400/20 shadow-2xl flex items-center gap-2 whitespace-nowrap z-10 font-bold uppercase text-[10px] tracking-widest">
            <span class="material-icons-round text-lg">add_shopping_cart</span>
            Yêu cầu nhanh
        </button>
    </div>
    @empty
    <div class="col-span-full py-24 bg-white dark:bg-white/5 rounded-[40px] border-2 border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center justify-center opacity-70">
        <div class="w-24 h-24 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center mb-6">
            <span class="material-icons-round text-5xl text-slate-400">inventory_2</span>
        </div>
        <p class="text-2xl font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest">Không tìm thấy vật tư</p>
        <p class="text-sm font-medium text-slate-400 dark:text-slate-500 mt-2">Hãy thử đổi từ khóa tìm kiếm hoặc lọc danh mục khác.</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-12 flex justify-center ajax-pagination">
    {{ $parts->appends(request()->query())->links() }}
</div>
