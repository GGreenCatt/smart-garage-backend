@extends('layouts.customer')

@section('title', 'Chi tiết Báo Giá Sửa Chữa')

@section('content')
<div class="min-h-screen bg-slate-50 pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Premium Header/Status Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 p-6 md:p-8 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-black px-3 py-1 rounded-full tracking-widest uppercase shadow-sm border border-indigo-200/50">#{{ $order->id }}</span>
                    <span class="text-slate-400 text-sm font-medium">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight">Báo Giá Dịch Vụ</h1>
                <p class="text-slate-500 mt-2 font-medium flex items-center gap-2">
                    <i class="fas fa-car text-slate-400"></i> {{ $order->vehicle->model ?? 'Xe Khách Hàng' }} 
                    <span class="mx-2 text-slate-300">|</span> 
                    <span class="font-bold text-slate-700">{{ $order->vehicle->license_plate ?? 'N/A' }}</span>
                </p>
            </div>
            
            <div class="relative z-10">
                @if($order->status === 'pending_approval')
                <div class="bg-amber-50 border border-amber-200/60 text-amber-700 font-bold px-5 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center animate-pulse">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>Chờ Phê Duyệt</span>
                </div>
                @elseif($order->status === 'approved' || $order->status === 'in_progress' || $order->status === 'completed')
                <div class="bg-emerald-50 border border-emerald-200/60 text-emerald-700 font-bold px-5 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Đã Chấp Thuận</span>
                </div>
                @endif
            </div>
        </div>

        <form id="approvalForm" onsubmit="event.preventDefault(); submitApproval();">
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- LEFT COLUMN: Main Content -->
                <div class="w-full lg:w-2/3 space-y-8">
                    
                    <!-- 3D Viewer Glass Card -->
                    @if($order->include_vhc)
                    @php
                        $threeDParams = ['id' => $order->vehicle_id, 'readonly' => 1, 'order_id' => $order->id];
                        $threeDUrl = request()->routeIs('guest.*') 
                            ? URL::signedRoute('guest.vehicle.3d', $threeDParams) 
                            : route('customer.vehicle.3d', $threeDParams);
                    @endphp
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden relative group">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-white/50">
                            <h2 class="font-bold text-lg flex items-center gap-3 text-slate-800">
                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                                    <i class="fas fa-cube text-lg"></i>
                                </div>
                                Mô Hình 3D & Tình Trạng
                            </h2>
                            <a href="{!! $threeDUrl !!}" target="_blank" class="text-sm text-indigo-600 font-bold hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2">
                                Phóng to <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <!-- Embedded Viewer -->
                        <div class="h-[450px] w-full relative">
                            <iframe src="{!! $threeDUrl !!}" class="w-full h-full border-0 relative z-0"></iframe>
                        </div>
                    </div>
                    @endif

                    <!-- Invoice Details (Tasks) -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="font-bold text-lg flex items-center gap-3 text-slate-800">
                                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 border border-amber-100/50">
                                    <i class="fas fa-file-invoice-dollar text-lg"></i>
                                </div>
                                Chi Tiết Sửa Chữa
                            </h2>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            @forelse($order->tasks->where('parent_id', null) as $parentTask)
                            <div class="border border-slate-200/70 rounded-2xl overflow-hidden hover:border-indigo-300/50 transition-colors duration-300">
                                <!-- Parent Header -->
                                <div class="bg-slate-50 border-b border-slate-200/70 p-5 flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                        <i class="fas fa-wrench"></i>
                                    </div>
                                    <h3 class="font-bold text-slate-800 text-lg">{{ $parentTask->title }}</h3>
                                    <span class="text-[10px] font-bold text-slate-500 bg-white px-2 py-1 rounded-md border border-slate-200 uppercase tracking-widest ml-auto shadow-sm">
                                        {{ $parentTask->type == 'vhc' ? 'Kiểm tra 3D' : 'Dịch vụ' }}
                                    </span>
                                </div>

                                <!-- Children (Proposed Fixes) -->
                                <div class="p-2 space-y-2 bg-white">
                                    @forelse($parentTask->children as $task)
                                        @php
                                            $partCost = $task->items->sum('subtotal');
                                            $rowTotal = $task->labor_cost + $partCost;
                                        @endphp
                                        <div class="group flex flex-col md:flex-row gap-4 justify-between items-start md:items-center p-4 hover:bg-slate-50/80 rounded-xl transition-all duration-200 task-row border border-transparent hover:border-slate-100" data-task-id="{{ $task->id }}" data-cost="{{ $rowTotal }}">
                                            
                                            <div class="flex-1 space-y-2">
                                                <div class="flex items-center gap-3">
                                                    <h4 class="font-bold text-slate-800 text-base">{{ $task->title }}</h4>
                                                    @if($task->severity == 'high')
                                                        <span class="bg-red-50 border border-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Nghiêm Trọng</span>
                                                    @elseif($task->severity == 'medium')
                                                        <span class="bg-orange-50 border border-orange-100 text-orange-600 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lưu Ý</span>
                                                    @else
                                                        <span class="bg-blue-50 border border-blue-100 text-blue-600 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">Bình Thường</span>
                                                    @endif
                                                </div>
                                                
                                                @if($task->description)
                                                    <p class="text-sm text-slate-500 line-clamp-2 md:line-clamp-none leading-relaxed">{!! nl2br(e($task->description)) !!}</p>
                                                @endif
                                                
                                                <!-- Breakdown block (Only show if parts exist) -->
                                                @if($task->items->count() > 0)
                                                <div class="mt-3 bg-white border border-slate-100 rounded-lg p-3 text-sm text-slate-600 space-y-1.5 shadow-sm opacity-80 group-hover:opacity-100 transition-opacity">
                                                    @foreach($task->items as $item)
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                                                        <span class="text-slate-600 font-medium">Linh kiện thay thế: {{ $item->name }}{{ $item->quantity > 1 ? ' (x' . $item->quantity . ')' : '' }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Action & Price Column -->
                                            <div class="w-full md:w-auto text-right flex flex-col items-end gap-4 shrink-0 bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-xl">
                                                <div class="font-mono text-xl font-bold text-slate-800">{{ number_format($rowTotal) }} ₫</div>
                                                
                                                @if($order->status === 'pending_approval')
                                                <!-- Modern Pill Toggles -->
                                                <div class="inline-flex bg-slate-100 p-1 rounded-xl shadow-inner border border-slate-200">
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="tasks[{{ $task->id }}]" value="approved" class="peer sr-only task-radio" checked onchange="calculateTotal()">
                                                        <div class="px-5 py-2 text-sm font-bold text-slate-500 peer-checked:bg-white peer-checked:text-emerald-600 peer-checked:shadow-sm rounded-lg transition-all duration-200 flex items-center justify-center min-w-[90px]">
                                                            Đồng ý
                                                        </div>
                                                    </label>
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="tasks[{{ $task->id }}]" value="rejected" class="peer sr-only task-radio" onchange="calculateTotal()">
                                                        <div class="px-5 py-2 text-sm font-bold text-slate-500 peer-checked:bg-white peer-checked:text-red-500 peer-checked:shadow-sm rounded-lg transition-all duration-200 flex items-center justify-center min-w-[90px]">
                                                            Từ chối
                                                        </div>
                                                    </label>
                                                </div>
                                                @else
                                                    @if($task->customer_approval_status === 'approved')
                                                        <div class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold border border-emerald-100 flex items-center gap-2">
                                                            <i class="fas fa-check-circle"></i> Đã Chọn
                                                        </div>
                                                    @elseif($task->customer_approval_status === 'rejected')
                                                        <div class="bg-slate-50 text-slate-400 px-4 py-2 rounded-xl text-sm font-bold border border-slate-200 flex items-center gap-2 w-full justify-center opacity-70">
                                                            <i class="fas fa-times"></i> Đã Hủy
                                                        </div>
                                                    @else
                                                        <div class="bg-slate-100 text-slate-500 px-4 py-2 rounded-xl text-sm font-bold">Chờ Xử Lý</div>
                                                    @endif
                                                @endif
                                            </div>
                                            
                                        </div>
                                    @empty
                                        <div class="p-6 text-center">
                                            <p class="text-slate-400 italic font-medium">Chưa có đề xuất sửa chữa chi tiết.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @empty
                            <div class="py-12 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4 shadow-sm">
                                    <i class="fas fa-box-open text-2xl"></i>
                                </div>
                                <p class="text-slate-500 font-medium tracking-wide">Chưa có hạng mục báo giá nào.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Sticky Sidebar CTA -->
                <div class="w-full lg:w-1/3 lg:sticky lg:top-28">
                    <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-600/20 overflow-hidden relative">
                        <!-- Decorative background elements -->
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/50 rounded-full blur-2xl pointer-events-none"></div>
                        
                        <div class="p-8 relative z-10">
                            <h3 class="text-indigo-200 font-bold uppercase tracking-widest text-xs mb-2">Tổng Thanh Toán Dự Kiến</h3>
                            <div class="flex items-end gap-1 mb-6">
                                <span id="totalCost" class="text-4xl lg:text-5xl font-black text-white tracking-tight leading-none">0</span>
                                <span class="text-indigo-200 font-bold text-xl mb-1 pb-1">₫</span>
                            </div>
                            
                            <div class="h-px w-full bg-indigo-500/50 mb-6"></div>
                            
                            <ul class="space-y-3 mb-8 text-indigo-100 text-sm font-medium">
                                <li class="flex items-center gap-3">
                                    <i class="fas fa-shield-alt text-indigo-300"></i>
                                    Phụ tùng chính hãng 100%
                                </li>
                                <li class="flex items-center gap-3">
                                    <i class="fas fa-tools text-indigo-300"></i>
                                    Kỹ thuật viên chuyên nghiệp
                                </li>
                                <li class="flex items-center gap-3">
                                    <i class="fas fa-history text-indigo-300"></i>
                                    Bảo hành dịch vụ 6 tháng
                                </li>
                            </ul>
                            
                            @if($order->status === 'pending_approval')
                            
                            <!-- Customer Note Input -->
                            <div class="mb-6 bg-indigo-500/10 p-4 rounded-xl border border-indigo-400/20">
                                <label for="customerNote" class="block text-indigo-100 font-bold mb-2 text-sm">Ghi chú cho Xưởng (Tùy chọn)</label>
                                <textarea id="customerNote" rows="3" class="w-full bg-white/10 border border-indigo-300/30 rounded-lg p-3 text-indigo-50 placeholder-indigo-300/50 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-all text-sm resize-none" placeholder="Nhập lời nhắn, yêu cầu hỗ trợ thêm..."></textarea>
                            </div>

                            <button type="submit" class="w-full bg-white text-indigo-600 hover:bg-slate-50 px-6 py-4 rounded-2xl font-black text-lg shadow-lg transition-transform transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3 group">
                                Xác Nhận Báo Giá 
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                            <p class="text-center text-indigo-200/70 text-xs mt-4">Sau khi xác nhận, garage sẽ bắt đầu sửa chữa.</p>
                            @else
                            <div class="w-full bg-indigo-800/50 text-indigo-200 px-6 py-4 rounded-2xl font-bold text-center border border-indigo-500/50 backdrop-blur-sm">
                                Phiếu Đã Khóa
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.task-row').forEach(row => {
            const radio = row.querySelector('input[type="radio"]:checked');
            if (radio && radio.value === 'approved') {
                total += parseFloat(row.getAttribute('data-cost') || 0);
            } else if (!row.querySelector('input[type="radio"]')) {
                // If past approval phase, check if it was approved
                const statusSpan = row.querySelector('.bg-emerald-50');
                if (statusSpan && statusSpan.innerText.includes('Đã Chọn')) {
                    total += parseFloat(row.getAttribute('data-cost') || 0);
                }
            }
        });
        
        document.getElementById('totalCost').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
    }
    
    document.addEventListener('DOMContentLoaded', calculateTotal);

    @if($order->status === 'pending_approval')
    function submitApproval() {
        const formData = new FormData(document.getElementById('approvalForm'));
        const tasks = [];
        
        document.querySelectorAll('.task-row').forEach(row => {
            const taskId = row.getAttribute('data-task-id');
            const status = formData.get(`tasks[${taskId}]`);
            if (status) {
                tasks.push({ id: taskId, status: status });
            }
        });
        
        Swal.fire({
            title: 'Xác nhận Báo Giá?',
            text: "Gara sẽ tiến hành sửa chữa các hạng mục bạn đã ĐỒNG Ý.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý xác nhận'
        }).then((result) => {
            if (result.isConfirmed) {
                @php
                    $actionUrl = request()->routeIs('guest.*') 
                        ? URL::signedRoute('guest.quote.action', $order->id) 
                        : route('customer.quote.action', $order->id);
                @endphp
                const noteVal = document.getElementById('customerNote') ? document.getElementById('customerNote').value : null;

                fetch(`{!! $actionUrl !!}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        tasks: tasks,
                        customer_note: noteVal 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Thành công!', 'Đã gửi xác nhận tới Garage.', 'success')
                    .then(() => {
                        window.location.reload();
                    });
                });
            }
        });
    }
    @endif
</script>
@endpush
