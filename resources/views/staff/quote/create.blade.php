@extends('layouts.staff')

@section('title', 'Smart Repair Inspection & Quote')

@section('main_class', 'p-6 xl:p-10 max-w-7xl mx-auto space-y-6 bg-[#f6f6f8] dark:bg-[#101622] font-display min-h-screen')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass {
        background: rgba(35, 47, 72, 0.4);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .locked-field {
        background-color: rgba(16, 22, 34, 0.5);
        cursor: not-allowed;
    }
    .text-primary { color: #2b6cee; }
    .bg-primary { background-color: #2b6cee; }
    .border-primary { border-color: #2b6cee; }
    .ring-primary { --tw-ring-color: #2b6cee; }
    .focus\:ring-primary:focus { --tw-ring-color: #2b6cee; box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000); }
    .focus\:border-primary:focus { border-color: #2b6cee; }
</style>
@endpush

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
    <div>
        <h2 class="text-3xl font-bold mb-1 font-display">Smart Repair Inspection & Quote</h2>
        <div class="flex flex-wrap items-center gap-4 text-slate-600 dark:text-slate-300 text-sm">
            <span class="flex items-center gap-1"><span class="material-icons-round text-xs">fingerprint</span> MÃ PHIẾU: #{{ $order->id }}</span>
            <span class="flex items-center gap-1"><span class="material-icons-round text-xs">directions_car</span> BX: {{ $order->vehicle->license_plate ?? 'N/A' }}</span>
            <span class="flex items-center gap-1 text-green-500 font-medium"><span class="material-icons-round text-xs">check_circle</span> Status: {{ ucfirst($order->status) }}</span>
        </div>
    </div>
    <div class="glass p-3 rounded-lg flex items-center gap-6">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Đính kèm Dữ liệu Quét 3D</span>
            <span class="text-xs text-slate-600 dark:text-slate-300">Đồng bộ hóa với máy quét tự động</span>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="include3dScan" class="sr-only peer" checked>
            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2b6cee] shadow-[0_0_15px_rgba(43,108,238,0.4)]"></div>
        </label>
    </div>
</div>

<form id="quoteForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Inspection Sections -->
    <div class="lg:col-span-2 space-y-8" id="tasksContainer">
        
        <!-- Dynamic Inspection Tasks & Add Labor -->
        @forelse($order->tasks->where('parent_id', null) as $index => $task)
        <section class="task-group glass p-5 rounded-lg border border-slate-300 dark:border-slate-800" data-task-id="{{ $task->id }}">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-slate-200 dark:border-slate-800/60">
                <div class="flex items-center gap-3">
                    <span class="material-icons-round text-primary">{{ $task->type == 'vhc' ? 'view_in_ar' : 'engineering' }}</span>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ $index + 1 }}. {{ str_replace('(VHC)', '', $task->title) }}</h3>
                    @if($task->type == 'vhc' && $order->vhcReport && $order->vhcReport->defects->count() > 0)
                    <span class="ml-auto text-xs font-bold text-primary animate-pulse flex items-center gap-1">
                        <span class="material-icons-round text-xs">sync</span> ĐÃ ĐỒNG BỘ
                    </span>
                    @endif
                </div>
                <button type="button" onclick="addProposedFix({{ $task->id }})" class="flex items-center justify-center gap-2 px-4 py-2 bg-transparent text-slate-700 dark:text-slate-300 text-sm font-bold rounded-md border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all transition-all">
                    <span class="material-icons-round text-sm">add</span> {{ $task->type == 'vhc' ? 'Thêm Đề Xuất Khác' : 'Thêm Đề Xuất Sửa Chữa' }}
                </button>
            </div>
            
            <div class="space-y-4 proposed-fixes-list" id="fixes-list-{{ $task->id }}">
                @if($task->type == 'vhc' && $order->vhcReport && $order->vhcReport->defects->count() > 0)
                    <!-- VHC Defects act as pre-filled suggested fixes -->
                    @foreach($order->vhcReport->defects as $defect)
                        <div class="fix-row vhc-defect-row glass p-5 rounded-xl border-l-4 border-red-500 shadow-lg shadow-red-500/20 mb-6 transition-all hover:shadow-xl hover:shadow-red-500/30">
                            <div class="flex justify-between items-start mb-4">
                                @php
                                    $childDefectTask = $task->children->where('type', 'defect')->where('title', $defect->title)->first();
                                @endphp
                                <div class="flex items-start gap-4 text-slate-800 dark:text-slate-200">
                                    @if($defect->image_url)
                                        <img src="{{ asset('storage/' . $defect->image_url) }}" alt="Defect Image" class="w-16 h-16 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                                    @else
                                        <span class="p-3 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg material-icons-round text-2xl shadow-sm">car_crash</span>
                                    @endif
                                    
                                    <div class="pt-1">
                                        <p class="font-bold text-sm flex items-center gap-2">
                                            Marker ID: #3D-{{ $defect->id }} 
                                            <span class="text-slate-400 font-normal">|</span> 
                                            <span class="text-red-500 dark:text-red-400 uppercase tracking-widest text-xs font-black">{{ $defect->title }}</span>
                                        </p>
                                        @if($defect->description)
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 bg-slate-50 dark:bg-slate-800/50 p-2 rounded border border-slate-100 dark:border-slate-800">
                                            <strong class="text-slate-700 dark:text-slate-300">Ghi chú lỗi:</strong> {{ $defect->description }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 text-[10px] font-black uppercase rounded shadow-sm border border-red-200 dark:border-red-500/30">Lỗi từ 3D</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5 pt-5 border-t border-slate-100 dark:border-slate-800/60">
                                <div>
                                    <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Đề xuất sửa chữa/thay thế</label>
                                    <input type="text" placeholder="Nhập tên hạng mục cần sửa chữa/thay thế..." class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-title font-display" value="">
                                    <!-- Use desc for part_name as well in payload if needed -->
                                    <input type="hidden" class="fix-part-name" value="Phụ tùng kèm theo">
                                    <input type="hidden" class="fix-desc" value="{{ $defect->description }}">
                                    <input type="hidden" class="fix-task-id" value="{{ $childDefectTask ? $childDefectTask->id : '' }}">
                                    <input type="hidden" class="fix-original-title" value="{{ $defect->title }}">
                                </div>
                                
                                <div>
                                    <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Mức độ nghiêm trọng</label>
                                    <select class="w-full bg-slate-50 dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-severity font-display">
                                        <option value="low" {{ in_array(strtolower($defect->severity ?? ''), ['low', 'minor']) ? 'selected' : '' }}>Thấp (Nhẹ)</option>
                                        <option value="medium" {{ (strtolower($defect->severity ?? '') == 'medium') ? 'selected' : '' }}>Trung bình</option>
                                        <option value="high" {{ in_array(strtolower($defect->severity ?? ''), ['high', 'critical']) || empty($defect->severity) ? 'selected' : '' }}>Cao (Nghiêm trọng)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Phí sửa chữa (VNĐ)</label>
                                    <input type="number" placeholder="0" class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-labor-cost text-right font-mono" value="0" onkeyup="calculateTotal()" onchange="calculateTotal()">
                                </div>
                            </div>
                            
                            <!-- Attached Parts Section -->
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/60 flex flex-col items-start gap-1">
                                <div class="parts-container w-full">
                                    <!-- Dynamic parts will be added here -->
                                </div>
                                <div class="flex items-center justify-between w-full mt-1">
                                    <button type="button" onclick="addPartRow(this)" class="text-sm font-bold text-[#2b6cee] hover:text-[#1e4eb0] flex items-center gap-2 transition-colors px-3 py-1.5 -ml-3 rounded-lg hover:bg-[#2b6cee]/10 group">
                                        <span class="material-icons-round text-[20px] transition-transform group-hover:rotate-90">add</span>
                                        Thêm linh kiện đính kèm
                                    </button>
                                    
                                    <span class="text-xs text-slate-500 font-medium bg-slate-50 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-700">
                                        Tổng linh kiện: <span class="fix-total-part-cost font-bold text-[#2b6cee] text-sm ml-1">0đ</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                <!-- JS injects fixes here -->
            </div>
        </section>
        @empty
            <div class="glass p-10 text-center rounded-lg border border-white/10 text-slate-500 col-span-2">
                <span class="material-icons-round mb-2 text-4xl opacity-50">task</span>
                <p>Chưa có hạng mục kiểm tra nào. Vui lòng thêm từ Dashboard.</p>
            </div>
        @endforelse

    </div>

    <!-- Right Column: Summary Panel -->
    <div class="lg:col-span-1">
        <div class="sticky top-24 space-y-6">
            <!-- Financial Summary Card -->
            <div class="glass rounded-lg overflow-hidden shadow-2xl shadow-[#2b6cee]/5 border border-white/10 dark:border-primary/20">
                <div class="bg-[#2b6cee] px-6 py-4">
                    <h4 class="text-white font-bold flex items-center gap-2 uppercase tracking-widest text-xs">
                        <span class="material-icons-round text-sm">receipt_long</span>
                        Tổng Hợp Chi Phí
                    </h4>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Dynamic Line Items Container -->
                    <div id="costLineItems" class="space-y-3">
                        <div class="flex justify-between items-center text-sm text-slate-500 italic">
                            Chưa có đề xuất nào
                        </div>
                    </div>
                    
                    <div class="h-px bg-slate-200 dark:bg-white/10 my-4"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-slate-900 dark:text-white">Tổng Cộng</span>
                        <span class="text-2xl font-black text-[#2b6cee]" id="totalPreview">0 VNĐ</span>
                    </div>
                    
                    <div class="bg-[#2b6cee]/5 border border-[#2b6cee]/20 rounded-md p-3 mt-6">
                        <p class="text-[10px] text-[#2b6cee] font-bold uppercase mb-1 tracking-wider">Lưu ý Hệ Thống</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300 italic">Tổng chi phí dự kiến tạm tính đã bao gồm chi phí vật liệu. Nhân viên xưởng không nhập tiền công.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="glass rounded-lg p-4 border border-white/10 dark:border-transparent mt-6">
                <!-- Action Buttons from Bottom bar moved here or keep separate footer if preferred -->
                <button type="button" onclick="window.location.href='{{ route('staff.dashboard') }}?order_id={{ $order->id }}'" class="w-full mt-4 py-2 bg-slate-200 dark:bg-white/5 border border-slate-300 dark:border-white/10 hover:bg-slate-300 dark:hover:bg-white/10 text-slate-700 dark:text-white rounded-lg text-xs font-bold transition-all uppercase tracking-widest">
                    Hủy (Quay Về)
                </button>
                <button type="button" onclick="submitQuote()" class="w-full mt-2 flex items-center justify-center gap-2 py-3 bg-[#2b6cee] hover:bg-[#2b6cee]/90 text-white font-bold text-sm rounded-lg shadow-[0_0_15px_rgba(43,108,238,0.3)] transition-all uppercase tracking-widest">
                    <span class="material-icons-round text-sm">send</span>
                    Gửi Báo Giá
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Hidden Template for Proposed Fix Row -->
<template id="fix-template">
    <div class="fix-row glass p-6 rounded-xl border-l-4 border-amber-500 shadow-md shadow-amber-500/10 mb-8 relative transition-all group hover:shadow-lg hover:shadow-amber-500/20">
        <!-- Delete Button -->
        <button type="button" onclick="this.closest('.fix-row').remove(); calculateTotal();" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-lg p-1.5 shadow-lg hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100 z-10 transition-opacity">
            <span class="material-icons-round text-[16px]">close</span>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-2">
            <div>
                <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Đề xuất sửa chữa/thay thế</label>
                <input type="text" placeholder="Nhập công việc/phụ tùng..." class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-title font-display">
            </div>
            
            <div>
                <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Mức độ nghiêm trọng</label>
                <select class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-severity font-display">
                    <option value="low">Thấp (Nhẹ)</option>
                    <option value="medium" selected>Trung bình</option>
                    <option value="high">Cao (Nghiêm trọng)</option>
                </select>
            </div>

            <div>
                <label class="text-[10px] uppercase font-black text-slate-600 dark:text-slate-300 mb-1.5 block tracking-widest">Phí sửa chữa (VNĐ)</label>
                <input type="number" placeholder="0" class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-labor-cost text-right font-mono" value="0" onkeyup="calculateTotal()" onchange="calculateTotal()">
            </div>
        </div>
        
        <!-- Attached Parts Section -->
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/60 flex flex-col items-start gap-1">
            <div class="parts-container w-full">
                <!-- Dynamic parts will be added here -->
            </div>
            <div class="flex items-center justify-between w-full mt-1">
                <button type="button" onclick="addPartRow(this)" class="text-sm font-bold text-amber-600 hover:text-amber-700 flex items-center gap-2 transition-colors px-3 py-1.5 -ml-3 rounded-lg hover:bg-amber-600/10 group">
                    <span class="material-icons-round text-[20px] transition-transform group-hover:rotate-90">add</span>
                    Thêm linh kiện đính kèm
                </button>
                
                <span class="text-xs text-slate-500 font-medium bg-slate-50 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-700">
                    Tổng linh kiện: <span class="fix-total-part-cost font-bold text-amber-600 text-sm ml-1">0đ</span>
                </span>
            </div>
        </div>

        <div class="mt-4">
             <input type="text" placeholder="Ghi chú thêm cho khách hàng hoặc nhập tên phụ tùng..." class="w-full bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded-md py-2.5 text-base text-slate-800 dark:text-slate-200 focus:ring-primary focus:border-primary fix-desc">
             <!-- Use desc for part_name as well in payload if needed -->
             <input type="hidden" class="fix-part-name" value="Phụ tùng kèm theo">
        </div>
    </div>
</template>

<!-- Hidden Template for Part Row -->
<template id="part-row-template">
    <div class="part-item flex gap-4 items-center group relative py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 px-3 -mx-3 rounded-xl transition-colors">
        <div class="flex-1 relative">
            <input type="text" placeholder="Nhập tên linh kiện..." class="w-full bg-transparent border-0 border-b-2 border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-0 px-0 py-2 text-slate-800 dark:text-slate-200 text-base font-medium part-name-input placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-colors" list="system-parts-list" onchange="autoFillPartPrice(this)">
        </div>
        <div class="w-24 relative">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold pointer-events-none">SL</span>
            <input type="number" min="1" value="1" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-right part-qty-input text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono shadow-sm" onchange="calculateTotal()" onkeyup="calculateTotal()">
        </div>
        <div class="w-40 relative">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold pointer-events-none uppercase tracking-widest">VNĐ</span>
            <input type="number" placeholder="Đơn giá" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg pl-3 pr-11 py-2 text-right part-price-input text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono shadow-sm" onchange="calculateTotal()" onkeyup="calculateTotal()">
        </div>
        <button type="button" onclick="removePartRow(this)" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors shrink-0 outline-none" title="Xóa linh kiện">
            <span class="material-icons-round text-xl">remove_circle_outline</span>
        </button>
    </div>
</template>

<!-- Datalist for System Inventory Parts -->
<datalist id="system-parts-list">
    @foreach($parts ?? [] as $part)
        <option value="{{ $part->name }}" data-price="{{ $part->selling_price }}">{{ $part->sku }}</option>
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    function addProposedFix(taskId) {
        const container = document.getElementById(`fixes-list-${taskId}`);
        const template = document.getElementById('fix-template').content.cloneNode(true);
        container.appendChild(template);
        calculateTotal();
    }

    function addPartRow(btn) {
        const container = btn.closest('.border-t').querySelector('.parts-container');
        const template = document.getElementById('part-row-template').content.cloneNode(true);
        container.appendChild(template);
        calculateTotal();
    }

    function removePartRow(btn) {
        btn.closest('.part-item').remove();
        calculateTotal();
    }

    function autoFillPartPrice(input) {
        const val = input.value;
        const options = document.getElementById('system-parts-list').options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                const price = options[i].getAttribute('data-price');
                const priceInput = input.closest('.part-item').querySelector('.part-price-input');
                priceInput.value = price;
                calculateTotal();
                break;
            }
        }
    }

    function calculateTotal() {
        let grandTotalParts = 0;
        const lineItemsContainer = document.getElementById('costLineItems');
        lineItemsContainer.innerHTML = ''; // Clear previous items
        
        const fixRows = document.querySelectorAll('.fix-row');
        let hasItems = false;

        fixRows.forEach(row => {
            const titleInput = row.querySelector('.fix-title');
            
            // Calculate parts total for this specific fix row
            let fixTotalCost = 0;
            const partItems = row.querySelectorAll('.part-item');
            
            partItems.forEach(part => {
                const qty = parseInt(part.querySelector('.part-qty-input').value) || 0;
                const price = parseFloat(part.querySelector('.part-price-input').value) || 0;
                fixTotalCost += (qty * price);
            });
            
            // Update the display for this fix's total parts cost
            const totalDisplay = row.querySelector('.fix-total-part-cost');
            if(totalDisplay) {
                totalDisplay.innerText = new Intl.NumberFormat('vi-VN').format(fixTotalCost) + 'đ';
            }

            const laborCostInput = row.querySelector('.fix-labor-cost');
            const laborCost = laborCostInput ? (parseFloat(laborCostInput.value) || 0) : 0;

            if (titleInput) {
                const title = titleInput.value.trim();
                
                // For display, use a fallback if title is empty but cost is > 0
                const displayTitle = title || 'Hạng mục không tên';
                
                const rowTotal = fixTotalCost + laborCost;
                
                if (rowTotal > 0 || title !== '') {
                    hasItems = true;
                    grandTotalParts += rowTotal;

                    // Add line item to HTML
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'flex justify-between items-start text-sm gap-4';
                    
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'text-slate-600 dark:text-slate-400 font-medium break-words max-w-[70%]';
                    nameSpan.innerText = displayTitle;

                    const priceSpan = document.createElement('span');
                    priceSpan.className = 'font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap';
                    priceSpan.innerText = new Intl.NumberFormat('vi-VN').format(rowTotal) + 'đ';

                    itemDiv.appendChild(nameSpan);
                    itemDiv.appendChild(priceSpan);
                    lineItemsContainer.appendChild(itemDiv);
                }
            }
        });

        if (!hasItems) {
            lineItemsContainer.innerHTML = '<div class="flex justify-between items-center text-sm text-slate-500 italic">Chưa có hạng mục chi phí</div>';
        }

        const grandTotal = grandTotalParts;
        
        document.getElementById('totalPreview').innerText = new Intl.NumberFormat('vi-VN').format(grandTotal) + ' VNĐ';
    }
    
    // Auto calculate on load and bind events to titles
    document.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
        
        // Use event delegation to handle title changes since rows are dynamically added
        document.body.addEventListener('keyup', function(e) {
            if(e.target && e.target.classList.contains('fix-title')) {
                calculateTotal();
            }
        });
    });
    
    function submitQuote() {
        // Build Nested JSON Payload
        const payload = { 
            tasks: {},
            include_vhc: document.getElementById('include3dScan').checked
        };
        const taskGroups = document.querySelectorAll('.task-group');
        let isValid = true;

        taskGroups.forEach(group => {
            const taskId = group.getAttribute('data-task-id');
            const fixes = group.querySelectorAll('.fix-row');
            
            if (fixes.length > 0) {
                payload.tasks[taskId] = { proposed_fixes: [] };
                
                fixes.forEach(fix => {
                    const titleInput = fix.querySelector('.fix-title');
                    let title = titleInput.value.trim();

                    // Calculate total cost for parts to validate if row is empty
                    let fixTotalCost = 0;
                    const partsArr = [];
                    fix.querySelectorAll('.part-item').forEach(part => {
                        const name = part.querySelector('.part-name-input').value.trim();
                        const qty = parseInt(part.querySelector('.part-qty-input').value) || 1;
                        const price = parseFloat(part.querySelector('.part-price-input').value) || 0;
                        
                        // Prevent saving/sending empty dummy parts
                        if (name === '' && price === 0) return;
                        
                        fixTotalCost += (qty * price);
                        partsArr.push({ name: name, qty: qty, price: price });
                    });
                    
                    const laborCostInput = fix.querySelector('.fix-labor-cost');
                    const laborCost = laborCostInput && laborCostInput.value ? parseFloat(laborCostInput.value) : 0;
                    
                    if (!title && partsArr.length === 0 && laborCost === 0) {
                        // Skip completely if it's a VHC defect row without a title AND no cost (they haven't proposed a fix yet)
                        if (fix.classList.contains('vhc-defect-row')) return;
                        
                        isValid = false;
                        titleInput.classList.add('border-red-500', 'ring-red-500');
                        return; // exit forEach iteration
                    } else if (!title && fix.classList.contains('vhc-defect-row')) {
                        // Allow empty title for VHC defects if they at least set a price
                        titleInput.classList.remove('border-red-500', 'ring-red-500');
                        title = '';
                    } else if (!title) {
                        isValid = false;
                        titleInput.classList.add('border-red-500', 'ring-red-500');
                        return;
                    } else {
                        titleInput.classList.remove('border-red-500', 'ring-red-500');
                    }
                    
                    const taskIdInput = fix.querySelector('.fix-task-id');
                    const childTaskId = taskIdInput ? taskIdInput.value : null;
                    
                    const originalTitleInput = fix.querySelector('.fix-original-title');
                    const originalTitle = originalTitleInput ? originalTitleInput.value : null;

                    payload.tasks[taskId].proposed_fixes.push({
                        title: title,
                        task_id: childTaskId,
                        original_title: originalTitle,
                        severity: fix.querySelector('.fix-severity').value,
                        labor_cost: laborCost,
                        parts: partsArr,
                        description: fix.querySelector('.fix-desc')?.value || ''
                    });
                });
            }
        });

        if (!isValid) {
            Swal.fire('Thiếu thông tin', 'Vui lòng chọn Dịch vụ cho tất cả các đề xuất sửa chữa.', 'warning');
            return;
        }

        if (Object.keys(payload.tasks).length === 0) {
           // It's okay to send an empty quote if they just want to send the 3D VHC report without fixes yet.
           // However we can warn them.
        }
        
        // Disable button, show loading
        const btn = document.querySelector('button[onclick="submitQuote()"]');
        const oldContent = btn.innerHTML;
        btn.innerHTML = '<span class="material-icons-round animate-spin">sync</span> Đang gửi...';
        btn.disabled = true;
        
        fetch(`/staff/order/{{ $order->id }}/send-quote`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Đã Gửi Báo Giá',
                    text: 'Báo giá đã được tạo và gửi cho khách hàng.',
                    icon: 'success',
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    window.location.href = `{{ route('staff.dashboard') }}?order_id={{ $order->id }}`;
                });
            } else {
                Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
                btn.innerHTML = oldContent;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Lỗi', 'Không thể kết nối lưu trữ báo giá.', 'error');
            btn.innerHTML = oldContent;
            btn.disabled = false;
        });
    }
</script>
@endpush
