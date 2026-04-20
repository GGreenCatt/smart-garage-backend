@extends('layouts.staff')

@section('content')
<div class="p-6 xl:p-8">
    <!-- Premium Header/Status Card -->
    <div class="bg-white dark:bg-[#1e293b] rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700/60 p-6 md:p-8 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 dark:from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row gap-6 items-start md:items-center w-full">
            <!-- Back Button -->
            <a href="{{ route('staff.dashboard') }}" class="flex-shrink-0 w-12 h-12 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200 dark:border-slate-700 shadow-sm transition-all focus:ring-2 focus:ring-indigo-500/20" title="Quay lại Trang Chủ Dashboard">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>

            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs font-black px-3 py-1 rounded-full tracking-widest uppercase shadow-sm border border-indigo-200/50 dark:border-indigo-500/30">#{{ $order->id }}</span>
                    <span class="text-slate-400 dark:text-slate-500 text-sm font-medium">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-white tracking-tight">Chi Tiết Báo Giá Mới Nhất</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium flex items-center gap-2">
                    <i class="fas fa-car text-slate-400 dark:text-slate-500"></i> {{ $order->vehicle->model ?? 'Xe Khách Hàng' }} 
                    <span class="mx-2 text-slate-300 dark:text-slate-600">|</span> 
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $order->vehicle->license_plate ?? 'N/A' }}</span>
                </p>
            </div>
            
            <div class="relative z-10 shrink-0">
                @if($order->status === 'pending_approval')
                <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 font-bold px-5 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-full flex items-center justify-center animate-pulse">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>Khách Hàng Đang Xem</span>
                </div>
                @elseif($order->status === 'approved' || $order->status === 'in_progress' || $order->status === 'in_service' || $order->status === 'completed')
                <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/60 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold px-5 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Khách Hàng Đã Phê Duyệt</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- LEFT COLUMN: Main Content -->
        <div class="w-full lg:w-2/3 space-y-8">
            
            <!-- 3D Viewer Glass Card -->
            @if($order->include_vhc)
            @php
                $threeDParams = ['id' => $order->vehicle_id, 'readonly' => 1, 'order_id' => $order->id];
                $threeDUrl = URL::signedRoute('guest.vehicle.3d', $threeDParams);
            @endphp
            <div class="bg-white dark:bg-[#1e293b] rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700/60 overflow-hidden relative">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="font-bold text-lg flex items-center gap-3 text-slate-800 dark:text-slate-200">
                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-500/20">
                            <i class="fas fa-cube text-lg"></i>
                        </div>
                        Mô Hình 3D & Tình Trạng
                    </h2>
                    <a href="{!! $threeDUrl !!}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2">
                        Mở toàn màn hình <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <!-- Embedded Viewer -->
                <div class="h-[450px] w-full relative">
                    <iframe src="{!! $threeDUrl !!}" class="w-full h-full border-0 relative z-0"></iframe>
                </div>
            </div>
            @endif

            <!-- Invoice Details (Tasks) -->
            <div class="bg-white dark:bg-[#1e293b] rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h2 class="font-bold text-lg flex items-center gap-3 text-slate-800 dark:text-slate-200">
                        <div class="w-10 h-10 bg-amber-50 dark:bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-600 dark:text-amber-400 border border-amber-100/50 dark:border-amber-500/20">
                            <i class="fas fa-file-invoice-dollar text-lg"></i>
                        </div>
                        Chi Tiết Hạng Mục Đã Báo
                    </h2>
                    <div class="flex items-center gap-2">
                        @if($order->status == 'completed' && $order->payment_status != 'paid')
                        <a href="{{ route('staff.order.invoice', $order->id) }}" target="_blank" class="text-sm bg-white hover:bg-slate-50 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 font-bold px-4 py-2 rounded-xl shadow-sm border border-slate-200 dark:border-slate-500/30 transition-all flex items-center gap-2">
                            <i class="fas fa-print bg-slate-100 dark:bg-slate-700/50 p-1.5 rounded-lg text-slate-500"></i> In Hóa Đơn / QR
                        </a>
                        <button onclick="showQrModal({{ $order->id }})" class="text-sm bg-white hover:bg-slate-50 text-indigo-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-indigo-400 font-bold px-4 py-2 rounded-xl shadow-sm border border-indigo-200 dark:border-indigo-500/30 transition-all flex items-center gap-2">
                            <i class="fas fa-qrcode"></i> Sinh Mã QR
                        </button>
                        <button onclick="processPayment({{ $order->id }})" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl shadow-sm shadow-indigo-500/30 transition-all flex items-center gap-2">
                            <i class="fas fa-credit-card"></i> Thanh Toán
                        </button>
                        @endif
                        @if($order->payment_status == 'paid')
                        <span class="text-sm border border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold px-4 py-2 rounded-xl shadow-sm flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> Đã Thanh Toán
                        </span>
                        @endif
                        @if($order->status !== 'completed')
                        <a href="{{ route('staff.quote.create', $order->id) }}" class="text-sm border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold px-4 py-2 rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-slate-400/20 flex items-center gap-2">
                            <i class="fas fa-pen text-slate-500 dark:text-slate-400"></i> Sửa Báo Giá
                        </a>
                        @endif
                        <button onclick="showCopyModal('{{ URL::signedRoute('guest.quote.show', $order->id) }}')" class="text-sm bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold px-4 py-2 rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-slate-400/20 flex items-center gap-2">
                            <i class="fas fa-link text-slate-500 dark:text-slate-400"></i> Chia sẻ Báo Giá
                        </button>
                    </div>
                </div>
                
                <div class="p-6 space-y-6">
                    @forelse($order->tasks->where('parent_id', null) as $parentTask)
                    <div class="border border-slate-200/70 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
                        <!-- Parent Header -->
                        <div class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200/70 dark:border-slate-700 p-5 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-400 dark:text-slate-300 shadow-sm">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg">{{ $parentTask->title }}</h3>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-700 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-600 uppercase tracking-widest ml-auto shadow-sm">
                                {{ $parentTask->type == 'vhc' ? 'Kiểm tra 3D' : 'Dịch vụ' }}
                            </span>
                        </div>

                        <!-- Children (Proposed Fixes) -->
                        <div class="p-3 space-y-3 bg-white dark:bg-[#1e293b]">
                            @forelse($parentTask->children as $task)
                                @php
                                    $partCost = $task->items->sum('subtotal');
                                    $rowTotal = $task->labor_cost + $partCost;
                                @endphp
                                <div class="group flex flex-col md:flex-row gap-4 justify-between items-start md:items-center p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 rounded-xl transition-all duration-200 task-row border border-slate-100 dark:border-slate-700 shadow-sm" data-task-id="{{ $task->id }}" data-cost="{{ $rowTotal }}">
                                    
                                    <div class="flex-1 space-y-2 w-full">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">{{ $task->title }}</h4>
                                            @if($task->severity == 'high')
                                                <span class="bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 text-red-600 dark:text-red-400 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Nghiêm Trọng</span>
                                            @elseif($task->severity == 'medium')
                                                <span class="bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 text-orange-600 dark:text-orange-400 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lưu Ý</span>
                                            @else
                                                <span class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-600 dark:text-blue-400 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Bình Thường</span>
                                            @endif
                                        </div>
                                        
                                        @if($task->description)
                                            <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 md:line-clamp-none leading-relaxed">{!! nl2br(e($task->description)) !!}</p>
                                        @endif
                                        
                                        <!-- Breakdown block -->
                                        <div class="mt-3 bg-slate-50 dark:bg-slate-800 rounded-lg p-3 text-sm text-slate-600 dark:text-slate-300 space-y-1.5 shadow-inner">
                                            @if(($task->labor_cost ?? 0) > 0)
                                            <div class="flex justify-between">
                                                <span class="text-slate-500 dark:text-slate-400">Tiền công:</span>
                                                <span class="font-semibold">{{ number_format($task->labor_cost ?? 0) }} ₫</span>
                                            </div>
                                            @endif
                                            @foreach($task->items as $item)
                                            <div class="flex justify-between">
                                                <span class="text-slate-500 dark:text-slate-400">Linh kiện ({{ $item->name }}){{ $item->quantity > 1 ? ' x' . $item->quantity : '' }}:</span>
                                                <span class="font-semibold">{{ number_format($item->subtotal) }} ₫</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Action & Price Column -->
                                    <div class="w-full md:w-auto text-right flex flex-col items-end gap-3 shrink-0 p-4 md:p-0 rounded-xl">
                                        <div class="font-mono text-xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($rowTotal) }} ₫</div>
                                        
                                        <div class="mt-1">
                                            @if($task->customer_approval_status === 'approved')
                                                <div class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-4 py-2 rounded-xl text-sm font-bold border border-emerald-100 dark:border-emerald-500/20 flex items-center gap-2 shadow-sm">
                                                    <i class="fas fa-check-circle"></i> Khách Chọn Làm
                                                </div>
                                            @elseif($task->customer_approval_status === 'rejected')
                                                <div class="bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 px-4 py-2 rounded-xl text-sm font-bold border border-slate-200 dark:border-slate-700 flex items-center gap-2 shadow-sm w-full justify-center">
                                                    <i class="fas fa-times"></i> Khách Báo Hủy
                                                </div>
                                            @else
                                                <!-- status === 'pending' -->
                                                <div class="bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 px-4 py-2 rounded-xl text-sm font-bold border border-amber-100 dark:border-amber-500/20 flex items-center gap-2 shadow-sm">
                                                    <i class="fas fa-hourglass-half"></i> Chờ Xác Nhận
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                </div>
                            @empty
                                <div class="p-6 text-center">
                                    <p class="text-slate-400 dark:text-slate-500 italic font-medium">Chưa có đề xuất sửa chữa chi tiết.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                        <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300 dark:text-slate-600 mx-auto mb-4 shadow-sm">
                            <i class="fas fa-box-open text-2xl"></i>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 font-medium tracking-wide">Chưa có hạng mục báo giá nào.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Sticky Sidebar CTA -->
        <div class="w-full lg:w-1/3 space-y-6 lg:sticky lg:top-24">
            
            <!-- Customer Note Block -->
            @if($order->customer_note)
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-3xl shadow-sm border border-amber-200 dark:border-amber-700/50 p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-400/10 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-sm">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3 class="font-bold text-amber-800 dark:text-amber-300">Ghi chú từ Khách hàng</h3>
                </div>
                <div class="bg-white/60 dark:bg-black/20 rounded-xl p-4 text-sm text-slate-700 dark:text-slate-300 italic border border-amber-100 dark:border-amber-800/50 relative z-10 w-full break-words">
                    "{{ $order->customer_note }}"
                </div>
            </div>
            @endif

            <!-- Total Block -->
            <div class="bg-indigo-600 dark:bg-indigo-700 rounded-3xl shadow-xl shadow-indigo-600/20 dark:shadow-indigo-900/40 overflow-hidden relative">
                <!-- Decorative background elements -->
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/50 dark:bg-indigo-400/20 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="p-8 relative z-10">
                    <h3 class="text-indigo-200 dark:text-indigo-300 font-bold uppercase tracking-widest text-sm mb-2">TỔNG KHÁCH CHỌN LÀM</h3>
                    <div class="flex items-end gap-1 mb-6">
                        @php
                            $totalApproved = 0;
                            foreach($order->tasks->where('parent_id', null) as $parentTask) {
                                foreach($parentTask->children as $child) {
                                    if ($child->customer_approval_status === 'approved') {
                                        $totalApproved += $child->labor_cost + $child->items->sum('subtotal');
                                    }
                                }
                            }
                        @endphp
                        <span class="text-4xl lg:text-5xl font-black text-white tracking-tight leading-none">{{ number_format($totalApproved) }}</span>
                        <span class="text-indigo-200 dark:text-indigo-300 font-bold text-xl mb-1 pb-1">₫</span>
                    </div>
                    
                    <div class="h-px w-full bg-indigo-500/50 dark:bg-indigo-500/30 mb-6"></div>
                    
                    <div class="space-y-4">
                        @if($order->status === 'pending_approval')
                            <div class="bg-indigo-800/50 dark:bg-indigo-900/50 p-4 rounded-xl border border-indigo-500/30 dark:border-indigo-400/20">
                                <div class="flex items-center gap-3 mb-2 text-indigo-100">
                                    <i class="fas fa-info-circle text-lg"></i>
                                    <span class="font-bold text-sm">Hướng Dẫn</span>
                                </div>
                                <p class="text-indigo-200/80 dark:text-indigo-300/80 text-sm leading-relaxed">
                                    Khách hàng đang xem báo giá này. Vui lòng chờ khách hàng "Xác nhận báo giá" để chuyển trạng thái sang <strong>Đang Thực Hiện</strong>.
                                </p>
                            </div>
                        @else
                            <div class="bg-emerald-500/20 p-4 rounded-xl border border-emerald-500/30 dark:border-emerald-400/20">
                                <div class="flex items-center gap-3 mb-2 text-emerald-100">
                                    <i class="fas fa-check-circle text-lg"></i>
                                    <span class="font-bold text-sm">Đã Phê Duyệt</span>
                                </div>
                                <p class="text-emerald-200/80 dark:text-emerald-300/80 text-sm leading-relaxed">
                                    Phiếu này đã được chốt. Bạn có thể tiến hành sửa chữa các mục đã được liệt kê.
                                </p>
                            </div>
                        @endif
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection
<!-- Copy Link Modal -->
<div id="copyModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-link"></i>
                </div>
                Chia sẻ Báo Giá
            </h3>
            <button onclick="closeCopyModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-600 dark:text-slate-400">Copy đường link bên dưới để gửi cho khách hàng xem và xác nhận báo giá.</p>
            <div class="relative">
                <input type="text" id="copyUrlInput" readonly class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-mono tracking-tight cursor-text">
                <button onclick="executeCopyInModal()" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg transition-colors" title="Copy">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <div id="copySuccessMsg" class="hidden items-center gap-2 text-emerald-600 dark:text-emerald-400 text-sm font-bold bg-emerald-50 dark:bg-emerald-500/10 px-4 py-2 rounded-lg justify-center border border-emerald-100 dark:border-emerald-500/20">
                <i class="fas fa-check-circle"></i> Đã sao chép vào bộ nhớ đệm!
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end">
             <button onclick="closeCopyModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white bg-white hover:bg-slate-100 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 shadow-sm transition-all focus:ring-2 focus:ring-slate-400/20">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- QR Payment Modal -->
<div id="qrModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300 items-center justify-center bg-slate-900/40 dark:bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300 border border-slate-200 dark:border-slate-700 flex flex-col">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-qrcode text-indigo-500"></i> Mã QR Thanh Toán
            </h3>
            <button onclick="closeQrModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white bg-white hover:bg-slate-100 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg w-8 h-8 flex flex-col items-center justify-center transition-colors border border-slate-200 dark:border-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 md:p-8 flex flex-col items-center justify-center bg-white dark:bg-[#1e293b]">
            <div id="qrLoading" class="flex flex-col items-center text-slate-400 py-8">
                <i class="fas fa-circle-notch fa-spin text-4xl mb-3 text-indigo-500"></i>
                <p class="font-medium text-sm">Đang tạo mã QR...</p>
            </div>
            <div id="qrContent" class="hidden flex-col items-center w-full">
                <div class="bg-white p-2 md:p-4 rounded-xl shadow-sm border border-slate-200 mb-4 inline-block">
                    <img id="qrImage" src="" alt="Mã thanh toán QR" class="w-48 h-48 md:w-64 md:h-64 object-contain">
                </div>
                <div class="text-center space-y-2 w-full">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Ngân hàng hưởng thụ: <span class="text-slate-800 dark:text-slate-200 font-bold">Vietinbank</span></p>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Chủ tài khoản: <span class="text-slate-800 dark:text-slate-200 font-bold uppercase">NGÔ VĂN ĐAN</span></p>
                    <p class="text-xs font-medium text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-3 py-2 rounded-lg break-all">STK: <span class="font-bold text-slate-700 dark:text-slate-300">102875143924</span></p>
                    
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xl font-black text-indigo-600 dark:text-indigo-400" id="qrAmountDisplay"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex flex-col md:flex-row gap-3 justify-end items-center">
            <span class="text-xs text-slate-400 font-medium w-full text-center md:text-left">Được cung cấp bởi VietQR</span>
            <button onclick="processPayment({{ $order->id }})" class="w-full md:w-auto px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all focus:ring-2 focus:ring-indigo-500/20 flex items-center justify-center gap-2 whitespace-nowrap">
                <i class="fas fa-check-circle"></i> Đã Nhận Tiền
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const copyModal = document.getElementById('copyModal');
    const copyModalContent = copyModal.querySelector('div');
    const copyUrlInput = document.getElementById('copyUrlInput');
    const copySuccessMsg = document.getElementById('copySuccessMsg');

    function showCopyModal(url) {
        copyUrlInput.value = url;
        copyModal.classList.remove('hidden');
        copyModal.classList.add('flex');
        
        // Slight delay for transition
        setTimeout(() => {
            copyModal.classList.remove('opacity-0');
            copyModalContent.classList.remove('scale-95');
            copyModalContent.classList.add('scale-100');
            
            // Auto Select text
            copyUrlInput.select();
        }, 10);
    }

    function closeCopyModal() {
        copyModal.classList.add('opacity-0');
        copyModalContent.classList.remove('scale-100');
        copyModalContent.classList.add('scale-95');
        
        setTimeout(() => {
            copyModal.classList.add('hidden');
            copyModal.classList.remove('flex');
            copySuccessMsg.classList.remove('flex');
            copySuccessMsg.classList.add('hidden');
        }, 300);
    }

    function executeCopyInModal() {
        copyUrlInput.select();
        copyUrlInput.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            copySuccessMsg.classList.remove('hidden');
            copySuccessMsg.classList.add('flex');
            
            setTimeout(() => {
                copySuccessMsg.classList.remove('flex');
                copySuccessMsg.classList.add('hidden');
            }, 3000);
        } catch (err) {
            alert('Không thể sao chép tự động. Bạn vui lòng copy thủ công nhé.');
        }
    }

    function processPayment(orderId) {
        Swal.fire({
            title: 'Xác nhận thanh toán?',
            text: "Đánh dấu đơn sửa chữa này là đã thanh toán.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5', // indigo-600
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Đã Nhận Tiền',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/pay`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ payment_method: 'cash' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Đã xác nhận thanh toán đơn sửa chữa.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể thanh toán.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi', 'Có lỗi kết nối, vui lòng thử lại.', 'error');
                });
            }
        });
    }

    const qrModal = document.getElementById('qrModal');
    const qrModalContent = qrModal.querySelector('div');
    const qrImage = document.getElementById('qrImage');
    const qrLoading = document.getElementById('qrLoading');
    const qrContent = document.getElementById('qrContent');
    const qrAmountDisplay = document.getElementById('qrAmountDisplay');

    function showQrModal(orderId) {
        qrModal.classList.remove('hidden');
        qrModal.classList.add('flex');
        
        qrLoading.classList.remove('hidden');
        qrLoading.classList.add('flex');
        qrContent.classList.add('hidden');
        qrContent.classList.remove('flex');
        
        setTimeout(() => {
            qrModal.classList.remove('opacity-0');
            qrModalContent.classList.remove('scale-95');
            qrModalContent.classList.add('scale-100');
        }, 10);

        fetch(`/staff/order/${orderId}/qr`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    qrImage.src = data.qr_url;
                    
                    const amountStr = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.amount);
                    qrAmountDisplay.textContent = amountStr;
                    
                    qrLoading.classList.add('hidden');
                    qrLoading.classList.remove('flex');
                    qrContent.classList.remove('hidden');
                    qrContent.classList.add('flex');
                } else {
                    closeQrModal();
                    Swal.fire('Lỗi', data.message || 'Không thể tạo mã QR.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                closeQrModal();
                Swal.fire('Lỗi', 'Có lỗi kết nối khi tải mã QR.', 'error');
            });
    }

    function closeQrModal() {
        qrModal.classList.add('opacity-0');
        qrModalContent.classList.remove('scale-100');
        qrModalContent.classList.add('scale-95');
        
        setTimeout(() => {
            qrModal.classList.add('hidden');
            qrModal.classList.remove('flex');
        }, 300);
    }
</script>
@endpush
