@extends('layouts.staff')

@section('title', 'Chi tiết Job')

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex justify-between items-center">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.dashboard') }}" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 transition"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">Job #{{ $order->id }}</h1>
                <span class="bg-teal-100 text-teal-700 font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wide">
                    {{ $order->status === 'in_progress' ? 'Đang thực hiện' : ($order->status === 'pending' ? 'Tiếp nhận' : 'Hoàn thành') }}
                </span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 ml-7">{{ $order->vehicle->model }} • <span class="font-mono font-bold">{{ $order->vehicle->license_plate }}</span></p>
        </div>
        <div class="flex gap-3">
             @if(in_array($order->status, ['pending', 'cancelled']))
             <button onclick="deleteOrder()" class="bg-red-50 text-red-600 font-bold py-2 px-4 rounded-xl hover:bg-red-100 transition text-sm">
                <i class="fas fa-trash-alt mr-2"></i>Xóa Đơn
            </button>
            @endif
             <button onclick="requestSupport()" class="bg-indigo-50 text-indigo-600 font-bold py-2 px-4 rounded-xl hover:bg-indigo-100 transition text-sm">
                <i class="fas fa-headset mr-2"></i>Hỗ Trợ
            </button>
            @php
                $hasPendingTasks = $order->tasks->where('status', '!=', 'completed')->count() > 0;
            @endphp

            @if($order->status !== 'completed')
            <button 
                onclick="{{ $hasPendingTasks ? "Swal.fire({icon: 'warning', title: 'Chưa hoàn thành', text: 'Vui lòng hoàn thành tất cả công việc trước khi kết thúc!', confirmButtonColor: '#0f172a'});" : "updateOrderStatus('completed')" }}" 
                class="{{ $hasPendingTasks ? 'bg-slate-300 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-slate-900 text-white hover:bg-slate-800 shadow-lg shadow-slate-900/20' }} font-bold py-2 px-6 rounded-xl transition text-sm"
                {{ $hasPendingTasks ? 'title="Còn công việc chưa hoàn thành"' : '' }}
            >
                <i class="fas fa-check mr-2"></i>Hoàn Thành
            </button>
            @endif

            @if($order->status == 'completed' && $order->payment_status !== 'paid')
                <button onclick="openPaymentModal()" class="bg-teal-600 text-white hover:bg-teal-700 shadow-lg shadow-teal-600/20 font-bold py-2 px-6 rounded-xl transition text-sm">
                    <i class="fas fa-wallet mr-2"></i>Thanh Toán
                </button>
            @endif

            @if($order->payment_status == 'paid')
                <a href="{{ route('staff.order.invoice', $order->id) }}" target="_blank" class="bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/20 font-bold py-2 px-6 rounded-xl transition text-sm flex items-center justify-center">
                    <i class="fas fa-print mr-2"></i>In Hóa Đơn
                </a>
            @endif
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6 h-full overflow-hidden">
        <!-- Main Content (Timeline & Tasks) -->
        <div class="flex-1 flex flex-col gap-6 overflow-y-auto pr-1 pb-safe">
            
            <!-- Service Timeline -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-6 flex items-center gap-2">
                    <i class="fas fa-stream text-teal-500"></i> Tiến Độ Dịch Vụ
                </h3>
                <div class="relative flex justify-between items-center px-4">
                    <div class="absolute left-0 top-1/2 w-full h-1 bg-slate-100 -z-10 rounded-full"></div>
                    
                    <!-- Step 1: Reception -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-teal-500 border-4 border-white shadow-lg flex items-center justify-center text-white text-xs z-10">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="text-xs font-bold text-teal-600">Tiếp Nhận</span>
                    </div>

                    <!-- Step 2: Processing -->
                    <div class="flex flex-col items-center gap-2">
                         <div class="w-8 h-8 rounded-full {{ $order->status != 'pending' ? 'bg-teal-500' : 'bg-slate-200 text-slate-400' }} border-4 border-white shadow-lg flex items-center justify-center text-white text-xs z-10 transition-colors">
                            <i class="fas fa-tools"></i>
                        </div>
                        <span class="text-xs font-bold {{ $order->status != 'pending' ? 'text-teal-600' : 'text-slate-400' }}">Đang Làm</span>
                    </div>

                    <!-- Step 3: Checking/Ready (Completed) -->
                    <div class="flex flex-col items-center gap-2">
                         <div class="w-8 h-8 rounded-full {{ $order->status == 'completed' ? 'bg-teal-500 text-white' : 'bg-slate-200 text-slate-400' }} border-4 border-white shadow-lg flex items-center justify-center text-xs z-10">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <span class="text-xs font-bold {{ $order->status == 'completed' ? 'text-teal-600' : 'text-slate-400' }}">Hoàn Thành</span>
                    </div>

                    <!-- Step 4: Delivery (Maybe future use or same as completed) -->
                    <!-- For now, assuming Completed = Ready for Delivery -->
                    <div class="flex flex-col items-center gap-2">
                         <div class="w-8 h-8 rounded-full bg-slate-200 border-4 border-white shadow-lg flex items-center justify-center text-slate-400 text-xs z-10">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-400">Giao Xe</span>
                    </div>
                </div>
            </div>

            <!-- Tasks Checklist -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                        <i class="fas fa-tasks text-teal-500"></i> Danh Sách Công Việc
                    </h3>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">{{ $order->tasks->where('status', 'completed')->count() }}/{{ $order->tasks->count() }} Hoàn Thành</span>
                </div>

                <div class="space-y-4">
                    @foreach($order->tasks->whereNull('parent_id') as $task)
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
                        <!-- Parent Task Row -->
                        <div class="flex items-center gap-3 p-4 {{ $task->status === 'completed' ? 'bg-slate-50 dark:bg-slate-900/40 opacity-75' : 'bg-white dark:bg-slate-800' }} group">
                            <button onclick="toggleTask(this, {{ $task->id }})" class="w-6 h-6 rounded-lg border-2 {{ $task->status === 'completed' ? 'bg-teal-500 border-teal-500 text-white' : 'border-slate-300 text-transparent' }} flex items-center justify-center transition flex-shrink-0">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                            
                            <div class="flex-1 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-sm {{ $task->status === 'completed' ? 'text-slate-500 dark:text-slate-400 line-through' : 'text-slate-800 dark:text-slate-100' }}">{{ $task->title }}</span>
                                    
                                    @if($task->children->count() === 0 && $task->type !== 'vhc')
                                    <select onchange="assignMechanic({{ $task->id }}, this.value)" class="text-[10px] bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded px-1.5 py-0.5 outline-none focus:border-teal-500 transition-colors cursor-pointer {{ $task->status === 'completed' ? 'hidden' : '' }}">
                                        <option value="">-- Chọn Thợ --</option>
                                        @foreach($mechanics as $mechanic)
                                            <option value="{{ $mechanic->id }}" {{ $task->mechanic_id == $mechanic->id ? 'selected' : '' }}>{{ $mechanic->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($task->status === 'completed' && $task->mechanic)
                                        <span class="text-[10px] text-teal-600 bg-teal-50 px-2 py-0.5 rounded font-medium"><i class="fas fa-wrench mr-1"></i>{{ $task->mechanic->name }}</span>
                                    @endif
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    @if($task->type === 'vhc')
                                        <a href="{{ route('staff.vehicle.inspection', $order->vehicle->id) }}" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-100 transition shadow-sm border border-indigo-100">
                                            <i class="fas fa-cube mr-1"></i>3D View
                                        </a>
                                    @else
                                        <button onclick="addTask({{ $task->id }})" class="text-xs bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 px-3 py-1.5 rounded-lg font-bold hover:bg-slate-100 transition border border-slate-200">
                                            <i class="fas fa-plus mr-1"></i> Thêm Việc
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Children Tasks -->
                        @if($task->children->count() > 0)
                        <div class="bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 p-3 pl-12 space-y-2">
                            @foreach($task->children as $child)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white dark:bg-slate-800 transition group border border-transparent hover:border-slate-100 dark:border-slate-700">
                                <button onclick="toggleTask(this, {{ $child->id }})" class="w-5 h-5 rounded border-2 {{ $child->status === 'completed' ? 'bg-teal-500 border-teal-500 text-white' : 'border-slate-300 text-transparent' }} flex items-center justify-center transition flex-shrink-0">
                                    <i class="fas fa-check text-[10px]"></i>
                                </button>
                                <div class="flex-1 flex justify-between items-center">
                                    <span class="text-xs font-medium {{ $child->status === 'completed' ? 'text-slate-400 line-through' : 'text-slate-600 dark:text-slate-300' }}">{{ $child->title }}</span>
                                    
                                    <div class="flex items-center gap-2">
                                        <select onchange="assignMechanic({{ $child->id }}, this.value)" class="text-[10px] bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded px-1.5 py-0.5 outline-none focus:border-teal-500 transition-colors cursor-pointer {{ $child->status === 'completed' ? 'hidden' : '' }}">
                                            <option value="">-- Chọn Thợ --</option>
                                            @foreach($mechanics as $mechanic)
                                                <option value="{{ $mechanic->id }}" {{ $child->mechanic_id == $mechanic->id ? 'selected' : '' }}>{{ $mechanic->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($child->status === 'completed' && $child->mechanic)
                                            <span class="text-[10px] text-teal-600 bg-teal-50 px-2 py-0.5 rounded font-medium border border-teal-100"><i class="fas fa-user-cog mr-1"></i>{{ $child->mechanic->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                            @if($task->type !== 'vhc')
                            <div class="bg-slate-50/30 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 p-2 text-center">
                                <span class="text-[10px] text-slate-400 italic">Chưa có nhiệm vụ con</span>
                            </div>
                            @endif
                        @endif
                    </div>
                    @endforeach

                    <!-- Manual Add Top-Level Task -->
                    <button onclick="addTask(null)" class="w-full py-3 bg-dashed border-2 border-slate-200 rounded-xl text-slate-400 text-xs font-bold uppercase tracking-wider hover:border-teal-400 hover:text-teal-500 transition">
                        <i class="fas fa-plus mr-2"></i> Thêm Nhiệm Vụ Mới
                    </button>
                </div>
            </div>

            <!-- Parts & Labor (Placeholder) -->
            <!-- Parts & Labor -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                        <i class="fas fa-box-open text-teal-500"></i> Vật Tư & Phụ Tùng
                    </h3>
                    <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="text-xs font-bold text-teal-600 bg-teal-50 px-3 py-1.5 rounded-lg hover:bg-teal-100 transition">+ Thêm</button>
                </div>
                
                @if($order->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th class="px-3 py-2 rounded-l-lg">Hạng Mục</th>
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Đơn Giá</th>
                                <th class="px-3 py-2 rounded-r-lg text-right">Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                            <tr class="group hover:bg-slate-50 dark:bg-slate-800/80 transition">
                                <td class="px-3 py-3 font-medium text-slate-700 dark:text-slate-200">
                                    {{ $item->name }}
                                    @if($item->sku) <span class="text-[10px] text-slate-400 block">{{ $item->sku }}</span> @endif
                                </td>
                                <td class="px-3 py-3">{{ $item->qty }}</td>
                                <td class="px-3 py-3">{{ number_format($item->price) }}đ</td>
                                <td class="px-3 py-3 text-right font-bold text-slate-700 dark:text-slate-200">{{ number_format($item->qty * $item->price) }}đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-right font-bold text-slate-500 dark:text-slate-400">Tổng Cộng</td>
                                <td class="px-3 py-4 text-right font-black text-lg text-teal-600">{{ number_format($order->items->sum('total')) }}đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 dark:bg-slate-800/80">
                    <p class="text-slate-400 text-sm font-medium">Chưa có vật tư được ghi nhận</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="w-full md:w-80 flex flex-col gap-6">
            <!-- Customer Card -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Khách Hàng</h3>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 text-white flex items-center justify-center font-bold text-lg shadow-lg shadow-teal-500/30">
                        {{ substr($order->vehicle->user?->name ?? 'G', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-100">{{ $order->vehicle->user?->name ?? 'Khách Lẻ' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $order->vehicle->owner_phone ?? $order->vehicle->user?->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-2 mb-4">
                     @if($order->vehicle->user?->email)
                        <div class="flex items-center gap-2">
                             <i class="fas fa-envelope w-4 text-center"></i>
                             <span class="truncate">{{ $order->vehicle->user->email }}</span>
                        </div>
                     @endif
                     <div class="flex items-center gap-2">
                         <i class="fas fa-map-marker-alt w-4 text-center"></i>
                         <span class="truncate">{{ $order->vehicle->user?->address ?? 'Chưa cập nhật địa chỉ' }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="flex-1 bg-teal-50 text-teal-600 py-2 rounded-xl text-xs font-bold hover:bg-teal-100 transition"><i class="fas fa-phone mr-1"></i> Gọi</button>
                    <button class="flex-1 bg-indigo-50 text-indigo-600 py-2 rounded-xl text-xs font-bold hover:bg-indigo-100 transition"><i class="fas fa-comment mr-1"></i> Chat</button>
                </div>
            </div>

            <!-- Vehicle Details Card (New) -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Thông Tin Xe</h3>
                <div class="space-y-3">
                     <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Biển Số</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ $order->vehicle->license_plate }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Model</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $order->vehicle->model }} ({{ $order->vehicle->year }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Màu Sắc</span>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full border border-slate-200 shadow-sm" style="background-color: {{ $order->vehicle->color ?? '#cbd5e1' }}"></span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $order->vehicle->color ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">VIN</span>
                        <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $order->vehicle->vin ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Odo tiếp nhận</span>
                        <span class="font-mono font-bold text-teal-600">{{ number_format($order->odometer_reading ?? 0) }} km</span>
                    </div>
                </div>
                
                <a href="{{ route('staff.vehicle.inspection', $order->vehicle->id) }}" class="flex items-center justify-center gap-2 w-full mt-4 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold py-2.5 rounded-xl transition shadow-lg shadow-slate-800/20 group">
                    <i class="fas fa-cube text-teal-400 group-hover:scale-110 transition-transform"></i>
                    Xem Mô Hình 3D
                </a>
            </div>

            <!-- Job Details -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Thông Tin Job</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Cố Vấn</p>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $order->advisor->name ?? 'Chưa phân công' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Thời Gian Tiếp Nhận</p>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $order->created_at ? $order->created_at->format('H:i d/m/Y') : 'N/A' }}</p>
                    </div>
                    <div>
                         <p class="text-[10px] text-slate-400 font-bold uppercase">Trạng Thái Báo Giá</p>
                         <div class="flex items-center gap-2 mt-1">
                             @if($order->quote_status === 'draft')
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 dark:text-slate-400 rounded text-[10px] font-bold uppercase">Nháp</span>
                             @elseif($order->quote_status === 'sent')
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-[10px] font-bold uppercase underline decoration-double">Chờ Duyệt</span>
                             @elseif($order->quote_status === 'approved')
                                <span class="px-2 py-0.5 bg-teal-100 text-teal-600 rounded text-[10px] font-bold uppercase">Đã Duyệt</span>
                             @elseif($order->quote_status === 'rejected')
                                <span class="px-2 py-0.5 bg-red-100 text-red-600 rounded text-[10px] font-bold uppercase">Từ Chối</span>
                             @endif
                         </div>
                    </div>
                    @if($order->status === 'completed')
                    <div>
                         <p class="text-[10px] text-slate-400 font-bold uppercase">Thanh Toán</p>
                         <div class="flex items-center gap-2 mt-1">
                             @if($order->payment_status === 'paid')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase border border-green-200"><i class="fas fa-check-circle mr-1"></i>Đã Thu Tiền ({{ ucfirst($order->payment_method ?? 'Tiền mặt') }})</span>
                             @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-bold uppercase border border-amber-200"><i class="fas fa-hourglass-half mr-1"></i>Chưa Thu Tiền</span>
                             @endif
                         </div>
                    </div>
                    @endif
                </div>
                @if(in_array($order->quote_status, ['draft', 'rejected']))
                <button onclick="sendQuote()" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-3 rounded-xl shadow-lg shadow-indigo-600/20 transition transform active:scale-95">
                    <i class="fas fa-paper-plane mr-1"></i> Gửi Báo Giá Cho Khách
                </button>
                @endif
            </div>

            <!-- Internal Chat Widget -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col h-96 mt-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b pb-2 flex justify-between items-center">
                    Trao Đổi Nội Bộ
                    <span class="bg-slate-100 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded text-[10px]" id="commentCount">0</span>
                </h3>
                
                <div id="chatMessages" class="flex-1 overflow-y-auto space-y-3 mb-4 pr-1 scrollbar-thin">
                    <div class="text-center text-slate-300 text-xs py-4 italic">Đang tải tin nhắn...</div>
                </div>

                <div class="relative border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 rounded-b-2xl">
                    <!-- Reply Context -->
                    <div id="replyContext" class="hidden px-4 py-2 bg-indigo-50 border-b border-indigo-100 text-xs text-indigo-600 flex justify-between items-center">
                        <span>Đang trả lời: <b id="replyName">...</b></span>
                        <button onclick="cancelReply()" class="text-indigo-400 hover:text-indigo-600"><i class="fas fa-times"></i></button>
                    </div>

                    <!-- Input Area -->
                    <div class="flex items-end gap-2 p-2">
                         <label class="cursor-pointer p-2 text-slate-400 hover:text-teal-600 transition">
                            <i class="fas fa-paperclip text-lg"></i>
                            <input type="file" id="chatAttachment" class="hidden" accept="image/*">
                        </label>
                        <textarea id="chatInput" rows="1" onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendComment(); }" class="flex-1 bg-transparent border-0 focus:ring-0 text-sm py-2 px-0 resize-none max-h-20" placeholder="Nhập tin nhắn..."></textarea>
                        <button onclick="sendComment()" class="p-2 text-teal-500 hover:text-teal-600 transition">
                            <i class="fas fa-paper-plane text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl p-6 shadow-2xl relative transform transition-all scale-100">
        <button onclick="document.getElementById('addItemModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="font-bold text-xl text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
            <i class="fas fa-plus-circle text-teal-500"></i> Thêm Vật Tư / Công Thợ
        </h3>
        
        <div class="space-y-5">
            <!-- Type Toggle -->
            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/80 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Loại Vật Tư</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400" id="labelInternal">Trong Kho</span>
                    <button onclick="toggleItemType()" id="btnTypeToggle" class="w-12 h-6 bg-slate-300 rounded-full relative transition-colors duration-300">
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white dark:bg-slate-800 rounded-full shadow-sm transition-transform duration-300" id="toggleCircle"></div>
                    </button>
                    <span class="text-xs font-bold text-slate-400" id="labelExternal">Mua Ngoài</span>
                </div>
            </div>
            
            <input type="hidden" id="isCustom" value="false">

            <!-- Internal Search -->
            <div id="internalSearchBlock">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Tìm Kiếm Phụ Tùng</label>
                <div class="relative">
                     <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                     <input type="text" id="itemNameSearch" oninput="searchParts(this.value)" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 transition" placeholder="Nhập tên phụ tùng hoặc mã...">
                     <div id="suggestions" class="hidden absolute top-full left-0 w-full bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-xl mt-2 max-h-60 overflow-y-auto z-20"></div>
                </div>
            </div>

            <!-- External Name Input (Hidden by default) -->
            <div id="externalNameBlock" class="hidden">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Tên Vật Tư / Công Việc</label>
                <input type="text" id="itemNameCustom" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition" placeholder="VD: Gương chiếu hậu phải Vios...">
            </div>
            
            <input type="hidden" id="itemSku">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Số Lượng</label>
                    <input type="number" id="itemQty" value="1" min="1" oninput="calculatePrice()" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition">
                </div>
                <!-- Logic for Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Giá Bán (VNĐ)</label>
                    <input type="number" id="itemPrice" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition">
                </div>
            </div>

            <!-- Cost & Fee for External (Hidden by default) -->
            <div id="externalCostBlock" class="hidden grid grid-cols-2 gap-4 bg-teal-50/50 p-4 rounded-xl border border-teal-100/50">
                <div>
                    <label class="block text-[10px] font-bold text-teal-600 uppercase mb-1 tracking-wide">Giá Nhập (Gốc)</label>
                    <input type="number" id="costPrice" oninput="calculatePrice()" class="w-full px-3 py-2 border-b border-teal-200 bg-transparent font-bold text-teal-800 text-sm focus:border-teal-500 outline-none placeholder-teal-300">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-teal-600 uppercase mb-1 tracking-wide">Phụ Phí (Lãi)</label>
                    <div class="relative">
                        <input type="number" id="serviceFee" value="50000" oninput="calculatePrice()" class="w-full px-3 py-2 border-b border-teal-200 bg-transparent font-bold text-teal-800 text-sm focus:border-teal-500 outline-none placeholder-teal-300">
                        <span class="absolute right-0 top-2 text-[10px] text-teal-500 font-bold">VNĐ</span>
                    </div>
                </div>
            </div>
            
            <button onclick="saveItem()" class="w-full bg-teal-500 text-white font-bold py-4 rounded-xl hover:bg-teal-600 transition shadow-lg shadow-teal-500/30 flex items-center justify-center gap-2 mt-2">
                <i class="fas fa-save"></i> Thêm Vào Job
            </button>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl p-6 shadow-2xl relative transform transition-all scale-100 flex flex-col gap-6">
        <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="font-black text-2xl text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <i class="fas fa-wallet text-teal-500"></i> Thanh Toán
        </h3>

        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <span class="text-slate-500 dark:text-slate-400 font-medium text-sm">Tổng cộng</span>
            <span class="text-2xl font-black text-teal-600">{{ number_format($order->total_amount) }}đ</span>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phương thức thanh toán</label>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="selectPaymentMethod('cash')" id="btnPmtCash" class="w-full py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2">
                    <i class="fas fa-money-bill-wave"></i> Tiền Mặt
                </button>
                {{-- 
                <button type="button" onclick="selectPaymentMethod('transfer')" id="btnPmtTransfer" class="py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2">
                    <i class="fas fa-qrcode"></i> Chuyển Khoản / QR
                </button>
                --}}
            </div>
            <input type="hidden" id="paymentMethodInput" value="cash">
        </div>

        {{-- 
        <div id="qrPreviewArea" class="hidden text-center bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700 relative min-h-[250px] flex items-center justify-center">
            <div id="qrLoading" class="text-slate-400 flex flex-col items-center">
                <i class="fas fa-circle-notch fa-spin text-3xl mb-2"></i>
                <span class="text-sm">Đang tạo mã QR...</span>
            </div>
            <img id="qrImage" src="" class="hidden w-48 h-48 mx-auto rounded-xl shadow-sm border p-1 bg-white">
        </div>
        --}}

        <button onclick="confirmPayment()" id="btnConfirmPayment" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-teal-600/30 transition text-lg flex items-center justify-center gap-2">
            <i class="fas fa-check-circle"></i> Xác Nhận Đã Thu Khách {{ number_format($order->total_amount) }}đ
        </button>
    </div>
</div>

@push('scripts')
<script>
    function toggleTask(btn, id) {
        // Prepare state
        const isCompleted = btn.classList.contains('bg-teal-500');
        const nextStatus = isCompleted ? 'pending' : 'completed';

        fetch('{{ route("staff.task.update", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ status: nextStatus })
        })
        .then(r => {
             if (!r.ok) {
                 return r.json().then(data => { throw new Error(data.message || 'Lỗi!') });
             }
             return r.json();
        })
        .then(() => {
            // Update UI on success
            if (!isCompleted) {
                // To Completed
                btn.classList.add('bg-teal-500', 'border-teal-500', 'text-white');
                btn.classList.remove('border-slate-300', 'text-transparent');
                // Optional: find text sibling and strike-through
                btn.nextElementSibling.querySelector('span').classList.add('text-slate-500 dark:text-slate-400', 'line-through');
            } else {
                // To Pending
                btn.classList.remove('bg-teal-500', 'border-teal-500', 'text-white');
                btn.classList.add('border-slate-300', 'text-transparent');
                
                btn.nextElementSibling.querySelector('span').classList.remove('text-slate-500 dark:text-slate-400', 'line-through');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa thể hoàn thành!',
                text: err.message,
                confirmButtonColor: '#0f172a'
            });
        });
    }

    function addTask(parentId) {
        Swal.fire({
            title: 'Thêm Nhiệm Vụ',
            input: 'text',
            inputPlaceholder: 'Nhập tên công việc...',
            showCancelButton: true,
            confirmButtonText: 'Thêm',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#0d9488', 
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch('{{ route("staff.order.tasks.store", $order->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ 
                        title: result.value,
                        parent_id: parentId,
                        type: 'general'
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) location.reload();
                });
            }
        });
    }

    function updateOrderStatus(status) {
        Swal.fire({
            title: 'Xác nhận hoàn thành?',
            text: "Bạn có chắc chắn muốn hoàn thành Job này?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("staff.order.update-status", $order->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: status })
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    }
                });
            }
        })
    }

    // Item Logic
    let timeout = null;
    function searchParts(query) {
        if(query.length < 2) {
            document.getElementById('suggestions').classList.add('hidden');
            return;
        }
        
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fetch('{{ route("staff.inventory.search") }}?q=' + query)
                .then(res => res.json())
                .then(data => {
                    if(data.length === 0) {
                        document.getElementById('suggestions').classList.add('hidden');
                        return;
                    }
                    const html = data.map(p => `
                        <div onclick="selectPart('${p.name}', '${p.sku}', ${p.price})" class="p-3 hover:bg-slate-50 dark:bg-slate-800/80 cursor-pointer flex justify-between items-center border-b border-slate-50 last:border-0 transition">
                            <div>
                                <p class="font-bold text-sm text-slate-700 dark:text-slate-200">${p.name}</p>
                                <p class="text-[10px] text-slate-400 font-mono">${p.sku}</p>
                            </div>
                            <span class="text-xs font-bold text-teal-600">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</span>
                        </div>
                    `).join('');
                    const el = document.getElementById('suggestions');
                    el.innerHTML = html;
                    el.classList.remove('hidden');
                });
        }, 300);
    }

    function toggleItemType() {
        const isCustom = document.getElementById('isCustom').value === 'true';
        const toggle = !isCustom;
        
        document.getElementById('isCustom').value = toggle;
        
        // UI Updates
        const circle = document.getElementById('toggleCircle');
        const btn = document.getElementById('btnTypeToggle');
        
        if (toggle) {
            // External Mode
            circle.style.transform = 'translateX(24px)';
            btn.classList.replace('bg-slate-300', 'bg-teal-500');
            document.getElementById('labelInternal').classList.replace('text-slate-700 dark:text-slate-200', 'text-slate-400');
            document.getElementById('labelExternal').classList.replace('text-slate-400', 'text-teal-600');
            
            document.getElementById('internalSearchBlock').classList.add('hidden');
            document.getElementById('externalNameBlock').classList.remove('hidden');
            document.getElementById('externalCostBlock').classList.remove('hidden'); // Show Cost/Fee
            
            document.getElementById('itemPrice').readOnly = true;
            document.getElementById('itemPrice').classList.add('bg-slate-100');
            
            calculatePrice();
        } else {
            // Internal Mode
            circle.style.transform = 'translateX(0)';
            btn.classList.replace('bg-teal-500', 'bg-slate-300');
            document.getElementById('labelInternal').classList.replace('text-slate-400', 'text-slate-700 dark:text-slate-200');
            document.getElementById('labelExternal').classList.replace('text-teal-600', 'text-slate-400');
            
            document.getElementById('internalSearchBlock').classList.remove('hidden');
            document.getElementById('externalNameBlock').classList.add('hidden');
            document.getElementById('externalCostBlock').classList.add('hidden');
            
            document.getElementById('itemPrice').readOnly = false;
            document.getElementById('itemPrice').classList.remove('bg-slate-100');
        }
    }

    function calculatePrice() {
        if(document.getElementById('isCustom').value !== 'true') return;
        
        const cost = parseFloat(document.getElementById('costPrice').value) || 0;
        const fee = parseFloat(document.getElementById('serviceFee').value) || 0;
        
        document.getElementById('itemPrice').value = cost + fee;
    }

    function selectPart(name, sku, price) {
        document.getElementById('itemNameSearch').value = name;
        document.getElementById('itemSku').value = sku;
        document.getElementById('itemPrice').value = price;
        document.getElementById('suggestions').classList.add('hidden');
    }

    function saveItem() {
        const btn = document.querySelector('#addItemModal button[onclick="saveItem()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
        btn.disabled = true;

        const isCustom = document.getElementById('isCustom').value;
        const data = {
            is_custom: isCustom,
            qty: document.getElementById('itemQty').value,
            price: document.getElementById('itemPrice').value,
        };

        if(isCustom === 'true') {
            data.name = document.getElementById('itemNameCustom').value;
            data.cost_price = document.getElementById('costPrice').value;
        } else {
            data.name = document.getElementById('itemNameSearch').value;
            data.sku = document.getElementById('itemSku').value;
        }
        
        fetch('{{ route("staff.order.items.store", $order->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                if(data.pending_approval) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Đã gửi yêu cầu!',
                        text: data.message, // 'Đã gửi yêu cầu vật tư đang chờ duyệt!'
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#0f172a'
                    }).then(() => {
                        document.getElementById('addItemModal').classList.add('hidden');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        // Optional: Clear form
                    });
                } else {
                    location.reload();
                }
            } else {
                Swal.fire('Lỗi!', 'Có lỗi xảy ra khi lưu!', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(() => {
            Swal.fire('Lỗi!', 'Có lỗi xảy ra!', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Close modal on outside click
    document.getElementById('addItemModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    function sendQuote() {
        Swal.fire({
            title: 'Gửi báo giá?',
            text: "Xác nhận gửi báo giá này cho khách hàng?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            confirmButtonText: 'Gửi ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("staff.order.send-quote", $order->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        Swal.fire('Đã gửi!', 'Báo giá đã được gửi thành công.', 'success')
                        .then(() => location.reload());
                    }
                });
            }
        });
    }

    function requestSupport() {
        Swal.fire({
            title: 'Yêu cầu hỗ trợ',
            input: 'text',
            inputLabel: 'Nội dung cần hỗ trợ',
            inputPlaceholder: 'Ví dụ: Cần người phụ tháo hộp số...',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            confirmButtonText: 'Gửi Yêu Cầu',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch('{{ route("staff.order.request-support", $order->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content: result.value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Đã gửi!', 'Yêu cầu hỗ trợ của bạn đã gửi đến toàn hệ thống.', 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể gửi yêu cầu', 'error');
                    }
                });
            }
        });
    }

    function deleteOrder() {
        Swal.fire({
            title: 'Xóa thẻ lệnh này?',
            text: 'Bạn có chắc chắn muốn xóa? Mọi dữ liệu công việc, báo giá và vật tư sẽ bị xóa vĩnh viễn.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#0f172a',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("staff.order.delete", $order->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Đã xóa', 'Thẻ lệnh đã bị xóa.', 'success')
                        .then(() => window.location.href = "{{ route('staff.dashboard') }}");
                    } else {
                        Swal.fire('Lỗi', d.message || 'Không thể xóa', 'error');
                    }
                })
                .catch(() => Swal.fire('Lỗi', 'Không thể xóa thẻ lệnh', 'error'));
            }
        });
    }

    function assignMechanic(taskId, mechanicId) {
        let url = mechanicId ? `/staff/task/${taskId}/assign` : `/staff/task/${taskId}/unassign`;
        let body = mechanicId ? JSON.stringify({ mechanic_id: mechanicId }) : null;
        let headers = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
        if (mechanicId) headers['Content-Type'] = 'application/json';

        fetch(url, { method: 'POST', headers: headers, body: body })
            .then(r => r.json())
            .then(d => {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
                });
                if(d.success) {
                    Toast.fire({ icon: 'success', title: mechanicId ? 'Đã giao việc thành công' : 'Đã hủy giao việc' });
                } else {
                    Toast.fire({ icon: 'error', title: d.message || 'Lỗi!' });
                }
            })
            .catch(() => Swal.fire('Lỗi', 'Không thể đổi thợ!', 'error'));
    }

    // Chat Logic
    let replyId = null;

    function loadComments() {
        const container = document.getElementById('chatMessages');
        if(!container) return;

        fetch('{{ route("staff.order.comments", $order->id) }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('commentCount').innerText = data.length;
                
                if(data.length === 0) {
                    container.innerHTML = '<div class="text-center text-slate-300 text-xs py-4 italic">Chưa có tin nhắn nào</div>';
                    return;
                }

                // Check if user scrolled up
                const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;

                container.innerHTML = data.map(c => `
                    <div class="flex flex-col ${c.user_id === {{ auth()->id() }} ? 'items-end' : 'items-start'} mb-2 group">
                        <div class="max-w-[85%] relative">
                            ${c.parent ? `
                                <div class="text-[10px] text-slate-400 mb-1 pl-2 border-l-2 border-slate-300">
                                    Trả lời <b>${c.parent.user.name}</b>: ${c.parent.content ? c.parent.content.substring(0, 20) + '...' : '<i>[Tệp đính kèm]</i>'}
                                </div>
                            ` : ''}
                            
                            <div class="p-3 rounded-xl text-xs relative ${c.user_id === {{ auth()->id() }} ? 'bg-teal-50 text-teal-800 rounded-br-none' : 'bg-slate-100 text-slate-700 dark:text-slate-200 rounded-bl-none'}">
                                <p class="font-bold mb-1 text-[10px] opacity-75">${c.user.name}</p>
                                ${c.attachment_path ? `<img src="/storage/${c.attachment_path}" class="max-w-full rounded-lg mb-2 border border-black/10">` : ''}
                                ${c.content}
                            </div>
                            
                            <!-- Reply Button -->
                            <button onclick="replyTo(${c.id}, '${c.user.name}')" class="opacity-0 group-hover:opacity-100 absolute ${c.user_id === {{ auth()->id() }} ? 'right-full mr-2' : 'left-full ml-2'} top-2 text-slate-300 hover:text-teal-500 transition text-[10px]">
                                <i class="fas fa-reply"></i>
                            </button>
                        </div>
                        <span class="text-[9px] text-slate-300 mt-1">${new Date(c.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                `).join('');
                
                if(isScrolledToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            });
    }

    function replyTo(id, name) {
        replyId = id;
        document.getElementById('replyName').innerText = name;
        document.getElementById('replyContext').classList.remove('hidden');
        document.getElementById('chatInput').focus();
    }

    function cancelReply() {
        replyId = null;
        document.getElementById('replyContext').classList.add('hidden');
    }

    function sendComment() {
        const input = document.getElementById('chatInput');
        const fileInput = document.getElementById('chatAttachment');
        const content = input.value.trim();
        
        if(!content && fileInput.files.length === 0) return;

        const formData = new FormData();
        formData.append('content', content);
        if(replyId) formData.append('parent_id', replyId);
        if(fileInput.files.length > 0) formData.append('attachment', fileInput.files[0]);

        input.value = '';
        input.disabled = true;
        cancelReply(); // Hide reply context

        fetch('{{ route("staff.order.comments.store", $order->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            input.disabled = false;
            fileInput.value = ''; // Reset file
            input.focus();
            if(data.success) {
                loadComments();
                setTimeout(() => {
                     const container = document.getElementById('chatMessages');
                     container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });
    }
    
    // Auto-resize textarea
    document.getElementById('chatInput').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Load chat on init
    loadComments();
    // Poll every 5 seconds
    setInterval(loadComments, 5000);

    // --- Payment Logic ---
    function openPaymentModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.children[0].classList.remove('scale-95');
        }, 10);
    }

    function selectPaymentMethod(method) {
        document.getElementById('paymentMethodInput').value = method;
        
        const btnCash = document.getElementById('btnPmtCash');
        const btnTransfer = document.getElementById('btnPmtTransfer');
        const qrArea = document.getElementById('qrPreviewArea');
        const qrLoading = document.getElementById('qrLoading');
        const qrImage = document.getElementById('qrImage');
        const btnConfirm = document.getElementById('btnConfirmPayment');
        
        // Reset styles
        btnCash.className = 'py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2';
        btnTransfer.className = 'py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2';
        
        if (method === 'cash') {
            btnCash.className = 'py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2';
            qrArea.classList.add('hidden');
            btnConfirm.innerHTML = '<i class="fas fa-check-circle"></i> Xác Nhận Đã Thu Khách {{ number_format($order->total_amount) }}đ (Tiền Mặt)';
        } else {
            btnTransfer.className = 'py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2';
            qrArea.classList.remove('hidden');
            qrLoading.classList.remove('hidden');
            qrImage.classList.add('hidden');
            btnConfirm.innerHTML = '<i class="fas fa-check-circle"></i> Xác Nhận Đã Nhận Chuyển Khoản';
            
            // Fetch QR Code
            fetch(`{{ route('staff.order.qr', $order->id) }}`)
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        qrImage.src = d.qr_url;
                        qrLoading.classList.add('hidden');
                        qrImage.classList.remove('hidden');
                    } else {
                        qrLoading.innerHTML = '<span class="text-red-500"><i class="fas fa-exclamation-triangle"></i> Lỗi tạo QR</span>';
                    }
                })
                .catch(() => {
                    qrLoading.innerHTML = '<span class="text-red-500"><i class="fas fa-wifi"></i> Lỗi kết nối</span>';
                });
        }
    }

    function confirmPayment() {
        const method = document.getElementById('paymentMethodInput').value;
        const btn = document.getElementById('btnConfirmPayment');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Đang xử lý...';

        fetch(`{{ route('staff.order.pay', $order->id) }}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ payment_method: method })
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thanh Toán Thành Công!',
                    text: 'Hệ thống đã ghi nhận thanh toán cho đơn hàng này.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => location.reload());
            } else {
                Swal.fire('Lỗi', d.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Thử Lại';
            }
        })
        .catch(err => {
            Swal.fire('Lỗi', 'Lỗi kết nối mạng.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Thử Lại';
        });
    }
</script>
@endpush
@endsection
