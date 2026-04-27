@extends('layouts.staff')

@section('title', 'Tạo Báo Giá Sửa Chữa')

@section('main_class', 'p-6 xl:p-10 max-w-7xl mx-auto space-y-6 bg-[#f6f6f8] dark:bg-[#101622] font-display min-h-screen')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(148, 163, 184, 0.28);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .dark .glass {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(51, 65, 85, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.22);
    }
    .quote-surface {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
    }
    .dark .quote-surface {
        background: rgba(15, 23, 42, 0.96);
        border-color: rgba(51, 65, 85, 0.85);
    }
    .field-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: rgb(100 116 139);
    }
    .dark .field-label {
        color: rgb(148 163 184);
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
    @media (max-width: 767px) {
        .quote-surface,
        .glass {
            border-radius: 14px;
        }
        .fix-row {
            padding: 1rem !important;
        }
        .fix-row .grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        .part-item {
            flex-direction: column;
            align-items: stretch;
            gap: .75rem;
        }
        .part-item > div,
        .part-item .w-24,
        .part-item .w-40 {
            width: 100% !important;
        }
        .part-item button {
            width: 100%;
            border-radius: .75rem;
            background: rgba(239, 68, 68, .08);
        }
        #quoteForm .sticky {
            position: static !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $vhcReport = $order->vhcReport;
    $vhcDefects = $vhcReport?->defects ?? collect();
    $quoteStatusLabels = [
        'draft' => 'Bản nháp',
        'sent' => 'Đã gửi cho khách',
        'approved' => 'Khách đã duyệt',
        'rejected' => 'Khách đã từ chối',
    ];
    $orderStatusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra/lập báo giá',
        'pending_approval' => 'Chờ khách duyệt',
        'approved' => 'Khách đã duyệt',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $severityLabels = [
        'low' => 'Nhẹ',
        'minor' => 'Nhẹ',
        'medium' => 'Trung bình',
        'high' => 'Nghiêm trọng',
        'critical' => 'Rất nghiêm trọng',
    ];
@endphp

<div class="quote-surface rounded-lg p-5 md:p-6 mb-6">
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
    <div>
        <h2 class="text-3xl font-bold mb-1 font-display">Tạo Báo Giá Sửa Chữa</h2>
        <div class="flex flex-wrap items-center gap-4 text-slate-600 dark:text-slate-300 text-sm">
            <span class="flex items-center gap-1"><span class="material-icons-round text-xs">fingerprint</span> Mã phiếu: #{{ $order->id }}</span>
            <span class="flex items-center gap-1"><span class="material-icons-round text-xs">directions_car</span> Biển số: {{ $order->vehicle->license_plate ?? 'N/A' }}</span>
            <span class="flex items-center gap-1 text-green-500 font-medium"><span class="material-icons-round text-xs">check_circle</span> Trạng thái: {{ $orderStatusLabels[$order->status] ?? $order->status }}</span>
        </div>
    </div>
    <div class="bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 p-3 rounded-lg flex items-center gap-6">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Đính kèm dữ liệu kiểm tra 3D</span>
            <span class="text-xs text-slate-600 dark:text-slate-300">Khách sẽ xem được các điểm lỗi 3D khi báo giá được gửi</span>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="include3dScan" class="sr-only peer" checked>
            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2b6cee] shadow-[0_0_15px_rgba(43,108,238,0.4)]"></div>
        </label>
    </div>
</div>
</div>

@if(!empty($quoteWarnings))
<div class="glass rounded-lg border border-amber-300 dark:border-amber-800 p-4 mb-8">
    <h3 class="text-sm font-black uppercase tracking-widest text-amber-700 dark:text-amber-300 flex items-center gap-2 mb-3">
        <span class="material-icons-round text-base">warning</span>
        Cần kiểm tra trước khi gửi
    </h3>
    <ul class="space-y-2 text-sm">
        @foreach($quoteWarnings as $warning)
            <li class="flex items-start gap-2 {{ $warning['level'] === 'critical' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }}">
                <span class="material-icons-round text-base">{{ $warning['level'] === 'critical' ? 'error' : 'info' }}</span>
                <span>{{ $warning['message'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endif
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="quote-surface rounded-lg p-4">
        <div class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-widest mb-1">Trạng thái báo giá</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $quoteStatusLabels[$order->quote_status ?? 'draft'] ?? 'Bản nháp' }}</div>
    </div>
    <div class="quote-surface rounded-lg p-4">
        <div class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-widest mb-1">Lỗi ghi nhận từ VHC/3D</div>
        <div class="flex items-center gap-2">
            <span class="text-lg font-bold {{ $vhcDefects->count() > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $vhcDefects->count() }}</span>
            <span class="text-sm text-slate-500 dark:text-slate-400">lỗi cần báo giá</span>
        </div>
    </div>
    <div class="quote-surface rounded-lg p-4 border-l-4 {{ $vhcReport ? 'border-l-green-500' : 'border-l-amber-500' }}">
        <div class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-widest mb-1">Kiểm tra trước khi gửi</div>
        <div class="text-sm font-bold {{ $vhcReport ? 'text-green-700 dark:text-green-300' : 'text-amber-700 dark:text-amber-300' }}">
            {{ $vhcReport ? 'Đã có dữ liệu VHC, khách sẽ xem được sau khi gửi báo giá' : 'Chưa có dữ liệu VHC' }}
        </div>
    </div>
</div>

@if($vhcReport && $vhcDefects->count() > 0)
<div class="glass rounded-lg border border-red-200 dark:border-red-900/60 p-4 mb-8">
    <div class="flex items-center justify-between gap-4 mb-3">
        <h3 class="text-sm font-black uppercase tracking-widest text-red-700 dark:text-red-300 flex items-center gap-2">
            <span class="material-icons-round text-base">fact_check</span>
            Xác nhận lỗi VHC trước khi gửi báo giá
        </h3>
        <a href="{{ route('staff.vehicle.inspection', ['id' => $order->vehicle->id ?? 0, 'fullscreen' => 1, 'order_id' => $order->id]) }}" class="text-xs font-bold text-[#2b6cee] hover:underline">Mở 3D/VHC</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($vhcDefects->take(6) as $defect)
            <div class="rounded-md bg-white/70 dark:bg-slate-900/60 border border-red-100 dark:border-red-900/40 p-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $defect->title }}</span>
                    <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300">{{ $severityLabels[strtolower($defect->severity ?? 'high')] ?? 'Nghiêm trọng' }}</span>
                </div>
                @if($defect->description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $defect->description }}</p>
                @endif
            </div>
        @endforeach
    </div>
    @if($vhcDefects->count() > 6)
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">Còn {{ $vhcDefects->count() - 6 }} lỗi khác trong màn VHC.</p>
    @endif
</div>
@endif

<form id="quoteForm" class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-6 xl:gap-8">
    <!-- Cột trái: danh sách hạng mục kiểm tra -->
    <div class="lg:col-span-2 space-y-8" id="tasksContainer">
        
        <!-- Hạng mục kiểm tra và đề xuất sửa chữa -->
        @forelse($order->tasks->where('parent_id', null) as $index => $task)
        <section class="task-group quote-surface p-5 rounded-lg" data-task-id="{{ $task->id }}">
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
                <button type="button" onclick="addProposedFix({{ $task->id }})" class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-md hover:bg-slate-700 dark:hover:bg-slate-200 transition-all">
                    <span class="material-icons-round text-sm">add</span> {{ $task->type == 'vhc' ? 'Thêm Đề Xuất Khác' : 'Thêm Đề Xuất Sửa Chữa' }}
                </button>
            </div>
            
            <div class="space-y-4 proposed-fixes-list" id="fixes-list-{{ $task->id }}">
                @if($task->type == 'vhc' && $order->vhcReport && $order->vhcReport->defects->count() > 0)
                    <!-- VHC Defects act as pre-filled suggested fixes -->
                    @foreach($order->vhcReport->defects as $defect)
                        <div class="fix-row vhc-defect-row bg-red-50/70 dark:bg-red-950/20 p-5 rounded-lg border border-red-200 dark:border-red-900/70 border-l-4 border-l-red-500 mb-5 transition-all">
                            <div class="flex justify-between items-start mb-4">
                                @php
                                    $childDefectTask = $task->children->where('type', 'defect')->where('title', $defect->title)->first();
                                @endphp
                                <div class="flex items-start gap-4 text-slate-800 dark:text-slate-200">
                                    @if($defect->image_url)
                                        <img src="{{ asset('storage/' . $defect->image_url) }}" alt="Ảnh lỗi" class="w-16 h-16 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                                    @else
                                        <span class="p-3 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg material-icons-round text-2xl shadow-sm">car_crash</span>
                                    @endif
                                    
                                    <div class="pt-1">
                                        <p class="font-bold text-sm flex items-center gap-2">
                                            Mã điểm lỗi: #3D-{{ $defect->id }} 
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
    <div>
        <div class="sticky top-6 space-y-4">
            <!-- Financial Summary Card -->
            <div class="quote-surface rounded-lg overflow-hidden">
                <div class="bg-slate-900 dark:bg-slate-800 px-5 py-4">
                    <h4 class="text-white font-bold flex items-center gap-2 uppercase tracking-widest text-xs">
                        <span class="material-icons-round text-sm">receipt_long</span>
                        Tổng hợp chi phí
                    </h4>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Dynamic Line Items Container -->
                    <div id="costLineItems" class="space-y-3">
                        <div class="flex justify-between items-center text-sm text-slate-500 italic">
                            Chưa có đề xuất nào
                        </div>
                    </div>
                    
                    <div class="h-px bg-slate-200 dark:bg-white/10 my-4"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-slate-900 dark:text-white">Tổng cộng</span>
                        <span class="text-2xl font-black text-[#2b6cee]" id="totalPreview">0 VNĐ</span>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/60 rounded-md p-3 mt-6">
                        <p class="text-[10px] text-blue-700 dark:text-blue-300 font-bold uppercase mb-1 tracking-wider">Lưu ý</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300">Tổng chi phí là tạm tính trước khi khách duyệt. Kiểm tra kỹ công việc, vật tư và dữ liệu VHC trước khi gửi.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="quote-surface rounded-lg p-4">
                <button type="button" onclick="submitQuote()" class="w-full mt-2 flex items-center justify-center gap-2 py-3 bg-[#2b6cee] hover:bg-[#245bc5] text-white font-bold text-sm rounded-lg shadow-lg shadow-blue-600/20 transition-all uppercase tracking-widest">
                    <span class="material-icons-round text-sm">send</span>
                    Gửi báo giá
                </button>
                <button type="button" onclick="window.location.href='{{ route('staff.dashboard') }}?order_id={{ $order->id }}'" class="w-full mt-2 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-white rounded-lg text-xs font-bold transition-all uppercase tracking-widest">
                    Quay về order
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Hidden Template for Proposed Fix Row -->
<template id="fix-template">
    <div class="fix-row quote-surface p-5 rounded-lg border-l-4 border-l-amber-500 mb-5 relative transition-all group">
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
    
    async function submitQuote() {
        // Build Nested JSON Payload
        const payload = { 
            tasks: {},
            include_vhc: document.getElementById('include3dScan').checked
        };
        const taskGroups = document.querySelectorAll('.task-group');
        let isValid = true;
        let liveTasksMissingQuote = 0;

        taskGroups.forEach(group => {
            const taskId = group.getAttribute('data-task-id');
            const fixes = group.querySelectorAll('.fix-row');
            let hasQuoteContent = false;
            
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
                        // Bỏ qua lỗi VHC nếu nhân viên chưa nhập đề xuất và chi phí.
                        if (fix.classList.contains('vhc-defect-row')) return;
                        
                        isValid = false;
                        titleInput.classList.add('border-red-500', 'ring-red-500');
                        return; // exit forEach iteration
                    } else if (!title && fix.classList.contains('vhc-defect-row')) {
                        // Cho phép lỗi VHC không có tên đề xuất nếu đã nhập chi phí.
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
                    hasQuoteContent = true;
                });
            }

            if (!hasQuoteContent) {
                liveTasksMissingQuote++;
            }
        });

        if (!isValid) {
            Swal.fire('Thiếu thông tin', 'Vui lòng chọn Dịch vụ cho tất cả các đề xuất sửa chữa.', 'warning');
            return;
        }

        if (Object.keys(payload.tasks).length === 0) {
           // Có thể gửi riêng dữ liệu VHC/3D nếu chưa có đề xuất sửa chữa cụ thể.
           // Hệ thống vẫn sẽ cảnh báo để nhân viên xác nhận trước khi gửi.
        }
        
        const warnings = (@json($quoteWarnings ?? [])).filter(w => w.code !== 'missing_task_quote');
        if (liveTasksMissingQuote > 0) {
            warnings.push({
                level: 'warning',
                code: 'missing_task_quote',
                message: `${liveTasksMissingQuote} hạng mục kiểm tra chưa có đề xuất sửa chữa hoặc chi phí. Nếu hạng mục này không cần sửa, bạn vẫn có thể gửi báo giá.`
            });
        }
        if (warnings.length > 0) {
            const warningHtml = `
                <div class="text-left space-y-3 mt-2">
                    ${warnings.map(w => `
                        <div class="flex gap-3 rounded-lg border ${w.level === 'critical' ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-800'} px-4 py-3">
                            <span class="material-icons-round text-lg shrink-0">${w.level === 'critical' ? 'error' : 'info'}</span>
                            <div class="text-sm leading-relaxed">${w.message}</div>
                        </div>
                    `).join('')}
                </div>
                <p class="text-xs text-slate-500 mt-4 text-left">
                    Cảnh báo màu vàng vẫn có thể gửi nếu nhân viên đã kiểm tra và xác nhận. Cảnh báo màu đỏ cần xử lý trước.
                </p>
            `;
            const confirmResult = await Swal.fire({
                title: 'Kiểm tra trước khi gửi',
                html: warningHtml,
                icon: warnings.some(w => w.level === 'critical') ? 'error' : 'warning',
                showCancelButton: true,
                width: 620,
                customClass: {
                    popup: 'rounded-xl',
                    htmlContainer: 'text-left'
                },
                confirmButtonText: warnings.some(w => w.level === 'critical') ? 'Đã hiểu' : 'Vẫn gửi',
                cancelButtonText: 'Quay lại'
            });

            if (!confirmResult.isConfirmed || warnings.some(w => w.level === 'critical')) {
                return;
            }
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
