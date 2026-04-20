@extends('layouts.admin')

@section('title', 'Chi Tiết RO')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900/50 p-6 rounded-2xl border border-slate-700">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-2xl font-black text-white">{{ $repairOrder->track_id }}</h2>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50',
                        'approved' => 'bg-blue-500/20 text-blue-400 border-blue-500/50',
                        'in_progress' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/50',
                        'completed' => 'bg-green-500/20 text-green-400 border-green-500/50',
                        'cancelled' => 'bg-red-500/20 text-red-400 border-red-500/50',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusColors[$repairOrder->status] ?? 'bg-slate-700 text-slate-300' }} uppercase">
                    {{ str_replace('_', ' ', $repairOrder->status) }}
                </span>
            </div>
            <p class="text-slate-400 text-sm">Tạo lúc {{ $repairOrder->created_at->format('H:i d/m/Y') }} bởi {{ $repairOrder->advisor->name ?? 'Unknown' }}</p>
        </div>
        
        <div class="flex gap-3">
            @if($repairOrder->status !== 'completed' && $repairOrder->status !== 'cancelled')
                <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                    <i class="fas fa-plus"></i> Thêm Hạng Mục
                </button>

                <form action="{{ route('admin.repair_orders.status', $repairOrder) }}" method="POST">
                    @csrf
                    @if($repairOrder->status === 'pending')
                         <input type="hidden" name="status" value="approved">
                         <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition shadow-lg shadow-emerald-500/20">
                            <i class="fas fa-check"></i> Duyệt Báo Giá
                        </button>
                    @elseif($repairOrder->status === 'approved')
                        <input type="hidden" name="status" value="in_progress">
                         <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition shadow-lg shadow-blue-500/20">
                            <i class="fas fa-tools"></i> Tiến Hành Sửa
                        </button>
                    @elseif($repairOrder->status === 'in_progress')
                        <input type="hidden" name="status" value="completed">
                         <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl transition shadow-lg shadow-green-500/20">
                            <i class="fas fa-flag-checkered"></i> Hoàn Thành
                        </button>
                    @endif
                </form>
            @endif
             <a href="{{ route('admin.repair_orders.invoice', $repairOrder) }}" target="_blank" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition flex items-center gap-2">
                <i class="fas fa-print"></i> In Hóa Đơn
            </a>
             <a href="{{ route('admin.repair_orders.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white rounded-xl font-bold transition">Quay Lại</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content: Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Service Timeline -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <h3 class="font-bold text-slate-400 mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <i class="fas fa-stream text-indigo-500"></i> Tiến Độ Dịch Vụ
                </h3>
                <div class="relative flex justify-between items-center px-4">
                    <div class="absolute left-0 top-1/2 w-full h-1 bg-slate-800 -z-10 rounded-full"></div>
                    
                    <!-- Step 1: Reception -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 border-4 border-slate-900 shadow-lg flex items-center justify-center text-white text-xs z-10">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="text-[10px] font-bold text-indigo-400 uppercase">Tiếp Nhận</span>
                    </div>

                    <!-- Step 2: Processing -->
                    <div class="flex flex-col items-center gap-2">
                         <div class="w-8 h-8 rounded-full {{ $repairOrder->status != 'pending' ? 'bg-indigo-500 text-white' : 'bg-slate-700 text-slate-500' }} border-4 border-slate-900 shadow-lg flex items-center justify-center text-xs z-10 transition-colors">
                            <i class="fas fa-tools"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ $repairOrder->status != 'pending' ? 'text-indigo-400' : 'text-slate-500' }} uppercase">Đang Làm</span>
                    </div>

                    <!-- Step 3: Completed -->
                    <div class="flex flex-col items-center gap-2">
                         <div class="w-8 h-8 rounded-full {{ $repairOrder->status == 'completed' ? 'bg-green-500 text-white' : 'bg-slate-700 text-slate-500' }} border-4 border-slate-900 shadow-lg flex items-center justify-center text-xs z-10 transition-colors">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ $repairOrder->status == 'completed' ? 'text-green-400' : 'text-slate-500' }} uppercase">Hoàn Thành</span>
                    </div>
                </div>
            </div>

            <!-- Task List -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-400 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-tasks text-indigo-500"></i> Tiến Độ Công Việc ({{ $repairOrder->tasks->where('status', 'completed')->count() }}/{{ $repairOrder->tasks->count() }})
                    </h3>
                    @if($repairOrder->status !== 'completed' && $repairOrder->status !== 'cancelled')
                        <button onclick="document.getElementById('addTaskModal').classList.remove('hidden')" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-2 border border-slate-700">
                            <i class="fas fa-plus"></i> Thêm Việc
                        </button>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($repairOrder->tasks->whereNull('parent_id') as $task)
                    <div class="bg-slate-800/50 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="flex items-center justify-between p-3">
                            <div class="flex items-center gap-3">
                                <form id="task-form-{{ $task->id }}" action="{{ route('admin.repair_tasks.update', $task) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $task->status === 'completed' ? 'pending' : 'completed' }}">
                                    <button type="button" 
                                        onclick="toggleTask({{ $task->id }}, '{{ $task->status }}', {{ $task->children->where('status', '!=', 'completed')->count() }})"
                                        class="w-5 h-5 rounded border-2 {{ $task->status === 'completed' ? 'bg-green-500 border-green-500 text-white' : 'border-slate-600 hover:border-indigo-500' }} flex items-center justify-center transition">
                                        @if($task->status === 'completed') <i class="fas fa-check text-[10px]"></i> @endif
                                    </button>
                                </form>
                                <span class="font-bold text-sm text-slate-300 {{ $task->status === 'completed' ? 'line-through opacity-50' : '' }}">{{ $task->title }}</span>
                            </div>
                            <!-- Delete/Edit could go here -->
                        </div>
                        
                        <!-- Children -->
                        @if($task->children->count() > 0)
                        <div class="bg-slate-900/30 border-t border-slate-700 p-2 pl-10 space-y-2">
                             @foreach($task->children as $child)
                                <div class="flex items-center gap-3">
                                <form id="task-form-{{ $child->id }}" action="{{ route('admin.repair_tasks.update', $child) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $child->status === 'completed' ? 'pending' : 'completed' }}">
                                    <button type="button" 
                                        onclick="toggleTask({{ $child->id }}, '{{ $child->status }}', 0)"
                                        class="w-4 h-4 rounded border-2 {{ $child->status === 'completed' ? 'bg-green-500 border-green-500 text-white' : 'border-slate-600 hover:border-indigo-500' }} flex items-center justify-center transition">
                                        @if($child->status === 'completed') <i class="fas fa-check text-[8px]"></i> @endif
                                    </button>
                                </form>
                                    <span class="text-xs text-slate-400 {{ $child->status === 'completed' ? 'line-through opacity-50' : '' }}">{{ $child->title }}</span>
                                </div>
                             @endforeach
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-slate-500 text-xs italic py-4">Chưa có công việc nào được tạo.</div>
                    @endforelse
                </div>
            </div>

            <!-- Items Table -->
            <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Chi Tiết Hạng Mục</th>
                            <th class="px-6 py-4 text-center">SL</th>
                            <th class="px-6 py-4 text-right">Đơn Giá</th>
                            <th class="px-6 py-4 text-right">Thành Tiền</th>
                            <th class="px-6 py-4 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($repairOrder->items as $item)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">
                                    {{ $item->itemable->name ?? 'Unknown Item' }}
                                    @if($item->itemable_type === 'App\Models\Service')
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] bg-blue-500/20 text-blue-400 uppercase font-bold">Dịch Vụ</span>
                                    @else
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] bg-orange-500/20 text-orange-400 uppercase font-bold">Vật Tư</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">{{ $item->itemable->code ?? $item->itemable->sku ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-white">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right font-mono">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono text-white font-bold">${{ number_format($item->subtotal, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($repairOrder->status !== 'completed')
                                <form action="{{ route('admin.repair_orders.items.destroy', [$repairOrder, $item]) }}" method="POST" onsubmit="return confirm('Xóa mục này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300"><i class="fas fa-times"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Chưa có hạng mục nào. Nhấn "Thêm Hạng Mục" để tạo báo giá.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-900/80 border-t border-slate-700">
                        <!-- Subtotal -->
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right font-medium text-slate-400">Tạm Tính</td>
                            <td class="px-6 py-3 text-right font-mono text-white">${{ number_format($repairOrder->subtotal, 2) }}</td>
                            <td></td>
                        </tr>
                        
                        <!-- Discount -->
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right font-medium text-slate-400">
                                <div class="flex items-center justify-end gap-2">
                                    <span>Giảm Giá</span>
                                    @if($repairOrder->promotion)
                                        <span class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded border border-green-500/20 font-bold uppercase tracking-wider">{{ $repairOrder->promotion->code }}</span>
                                        <form action="{{ route('admin.repair_orders.coupon.remove', $repairOrder) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-500 hover:text-red-400"><i class="fas fa-times-circle"></i></button>
                                        </form>
                                    @endif
                                </div>
                                @if(!$repairOrder->promotion && $repairOrder->status !== 'completed' && $repairOrder->status !== 'cancelled')
                                    <form action="{{ route('admin.repair_orders.coupon', $repairOrder) }}" method="POST" class="mt-2 flex justify-end gap-2">
                                        @csrf
                                        <input type="text" name="code" placeholder="Mã giảm giá..." class="bg-slate-800 border border-slate-700 rounded text-xs px-2 py-1 text-white focus:border-indigo-500 focus:outline-none uppercase w-32">
                                        <button class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-3 py-1 rounded font-bold">Áp Dụng</button>
                                    </form>
                                    @if($errors->has('coupon'))
                                        <div class="text-red-400 text-xs mt-1">{{ $errors->first('coupon') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-green-400">- ${{ number_format($repairOrder->discount_amount, 2) }}</td>
                            <td></td>
                        </tr>

                        <!-- Tax -->
                         <tr>
                            <td colspan="3" class="px-6 py-3 text-right font-medium text-slate-400">Thuế (0%)</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-300">${{ number_format($repairOrder->tax_amount, 2) }}</td>
                            <td></td>
                        </tr>

                        <!-- Total -->
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-200 uppercase text-xs">Tổng Cộng</td>
                            <td class="px-6 py-4 text-right font-black text-xl text-indigo-400">${{ number_format($repairOrder->total_amount, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Diagnosis Note -->
            <div class="bg-slate-900/30 rounded-xl p-6 border border-slate-800">
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Chẩn Đoán / Yêu Cầu Của Khách</h3>
                <p class="text-slate-300 leading-relaxed">{{ $repairOrder->diagnosis_note }}</p>
            </div>
             <!-- Internal Notes -->
            <div class="bg-slate-900/30 rounded-xl p-6 border border-slate-800">
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Ghi Chú Nội Bộ / Thanh Toán</h3>
                <p class="text-slate-300 leading-relaxed">{{ $repairOrder->notes ?? 'Chưa có ghi chú.' }}</p>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            
            <!-- Payment Card -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-700/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-wallet text-6xl text-white"></i>
                </div>
                <h3 class="text-xs font-bold text-indigo-400 uppercase mb-4 tracking-wider relative z-10">Thanh Toán</h3>
                
                <div class="mb-4 relative z-10">
                    <div class="text-sm text-slate-400 mb-1">Trạng thái</div>
                     @if($repairOrder->payment_status == 'paid')
                        <div class="text-green-400 font-bold flex items-center gap-2"><i class="fas fa-check-circle"></i> Đã Thanh Toán</div>
                    @elseif($repairOrder->payment_status == 'partial')
                         <div class="text-yellow-400 font-bold flex items-center gap-2"><i class="fas fa-adjust"></i> Thanh Toán 1 Phần</div>
                    @else
                         <div class="text-red-400 font-bold flex items-center gap-2"><i class="fas fa-times-circle"></i> Chưa Thanh Toán</div>
                    @endif
                </div>

                @if($repairOrder->payment_method)
                <div class="mb-6 relative z-10">
                    <div class="text-sm text-slate-400 mb-1">Phương thức</div>
                    <div class="text-white font-bold capitalize">{{ $repairOrder->payment_method }}</div>
                </div>
                @endif
                
                <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition relative z-10">
                    Cập Nhật Thanh Toán
                </button>
            </div>

            <!-- Customer Card -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <h3 class="text-xs font-bold text-indigo-400 uppercase mb-4 tracking-wider">Thông Tin Khách Hàng</h3>
                <div class="flex items-center gap-4 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ $repairOrder->customer->name ?? 'Unknown' }}&background=random" class="w-12 h-12 rounded-full border-2 border-slate-700">
                    <div>
                        <div class="font-bold text-white">{{ $repairOrder->customer->name ?? 'Unknown Customer' }}</div>
                        <div class="text-sm text-slate-400">{{ $repairOrder->customer->phone ?? 'No Phone' }}</div>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-500">Email</span>
                        <span class="text-slate-300">{{ $repairOrder->customer->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-500">Thành viên từ</span>
                        <span class="text-slate-300">{{ optional($repairOrder->customer)->created_at ? $repairOrder->customer->created_at->format('M Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Vehicle Card -->
             <div class="glass-panel p-6 rounded-2xl border border-slate-700/50">
                <h3 class="text-xs font-bold text-indigo-400 uppercase mb-4 tracking-wider">Thông Tin Xe</h3>
                <div class="text-center mb-4">
                    <div class="text-xl font-black text-white">
                        {{ $repairOrder->vehicle->make ?? 'Unknown' }} {{ $repairOrder->vehicle->model ?? 'Vehicle' }}
                    </div>
                    <div class="text-sm font-mono text-slate-400 bg-slate-900 inline-block px-3 py-1 rounded mt-1 border border-slate-700">
                        {{ $repairOrder->vehicle->license_plate ?? 'NO-PLATE' }}
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                     <div class="flex justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-500">VIN</span>
                        <span class="text-slate-300 font-mono">{{ $repairOrder->vehicle->vin ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-800 pb-2 pt-1">
                        <span class="text-slate-500">Year</span>
                        <span class="text-slate-300">{{ $repairOrder->vehicle->year ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-500">Odometer</span>
                        <span class="text-slate-300">{{ number_format($repairOrder->odometer_reading) }} km</span>
                    </div>
                </div>
                
                <button onclick="open3DModal('{{ route('admin.vehicles.3d', $repairOrder->vehicle->id) }}', '{{ $repairOrder->vehicle->model }}')" class="flex items-center justify-center gap-2 w-full mt-4 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold py-2.5 rounded-xl transition shadow-lg shadow-slate-800/20 group">
                    <i class="fas fa-cube text-teal-400 group-hover:scale-110 transition-transform"></i>
                    Xem Mô Hình 3D
                </button>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                 <h3 class="font-bold text-white text-lg">Thêm Hạng Mục Vào Báo Giá</h3>
                 <button onclick="document.getElementById('addItemModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6">
                <!-- Tabs -->
                <div class="flex gap-2 mb-6 bg-slate-800 p-1 rounded-lg">
                    <button onclick="switchTab('service')" id="tabService" class="flex-1 py-2 rounded-md text-sm font-bold bg-indigo-600 text-white transition">Dịch Vụ</button>
                    <button onclick="switchTab('part')" id="tabPart" class="flex-1 py-2 rounded-md text-sm font-bold text-slate-400 hover:text-white transition">Vật Tư</button>
                </div>

                <form action="{{ route('admin.repair_orders.items.store', $repairOrder) }}" method="POST" id="addItemForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" id="itemType" value="service">
                    
                    <div id="serviceSelectDiv">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Chọn Dịch Vụ</label>
                        <select name="item_id" id="serviceInput" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Chọn Dịch Vụ --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} (${{ $s->base_price }}) - {{ $s->code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="partSelectDiv" class="hidden">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Chọn Vật Tư</label>
                        <select name="item_id_part" id="partInput" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Chọn Vật Tư --</option>
                            @foreach($parts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (${{ $p->selling_price }}) - Tồn: {{ $p->stock_quantity }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Số Lượng</label>
                        <input type="number" name="quantity" value="1" min="1" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg mt-2">Thêm Vào Đơn</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Task Modal -->
    <div id="addTaskModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                 <h3 class="font-bold text-white text-lg">Thêm Công Việc Mới</h3>
                 <button onclick="document.getElementById('addTaskModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.repair_orders.tasks.store', $repairOrder) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tên Công Việc</label>
                        <input type="text" name="title" required placeholder="Ví dụ: Thay nhớt, Kiểm tra phanh..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mô tả (Tùy chọn)</label>
                        <textarea name="description" rows="3" placeholder="Chi tiết công việc..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg mt-2">Thêm Công Việc</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3D View Modal -->
    <div id="modal3D" class="fixed inset-0 bg-slate-900/90 backdrop-blur-md z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-[95vw] h-[95vh] max-w-none shadow-2xl overflow-hidden flex flex-col relative">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
                 <div class="flex items-center gap-3">
                     <span class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center text-teal-400">
                         <i class="fas fa-cube"></i>
                     </span>
                     <div>
                         <h3 class="font-bold text-white text-lg" id="modal3DTitle">Mô Hình 3D</h3>
                         <p class="text-xs text-slate-500">Xem chi tiết hư hỏng và hiện trạng xe</p>
                     </div>
                 </div>
                 <button onclick="close3DModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                     <i class="fas fa-times"></i>
                 </button>
            </div>
            
            <!-- Iframe Container -->
            <div class="flex-grow bg-black relative">
                <!-- Loader -->
                <div id="loader3D" class="absolute inset-0 flex items-center justify-center text-teal-500">
                    <i class="fas fa-circle-notch fa-spin text-4xl"></i>
                </div>
                <iframe id="iframe3D" src="" class="w-full h-full border-0" onload="document.getElementById('loader3D').classList.add('hidden')"></iframe>
            </div>
        </div>
    </div>

</div>

<script>
function switchTab(type) {
    document.getElementById('itemType').value = type;
    
    // UI Updates
    if (type === 'service') {
        document.getElementById('tabService').classList.add('bg-indigo-600', 'text-white');
        document.getElementById('tabService').classList.remove('text-slate-400');
        document.getElementById('tabPart').classList.remove('bg-indigo-600', 'text-white');
        document.getElementById('tabPart').classList.add('text-slate-400');
        
        document.getElementById('serviceSelectDiv').classList.remove('hidden');
        document.getElementById('partSelectDiv').classList.add('hidden');
        
        document.getElementById('serviceInput').name = 'item_id';
        document.getElementById('partInput').name = 'item_id_disabled'; // Prevent sending
    } else {
        document.getElementById('tabPart').classList.add('bg-indigo-600', 'text-white');
        document.getElementById('tabPart').classList.remove('text-slate-400');
        document.getElementById('tabService').classList.remove('bg-indigo-600', 'text-white');
        document.getElementById('tabService').classList.add('text-slate-400');
         
        document.getElementById('partSelectDiv').classList.remove('hidden');
        document.getElementById('serviceSelectDiv').classList.add('hidden');
        
        document.getElementById('partInput').name = 'item_id';
        document.getElementById('serviceInput').name = 'item_id_disabled';
    }
}

function open3DModal(url, title) {
    const modal = document.getElementById('modal3D');
    const iframe = document.getElementById('iframe3D');
    const loader = document.getElementById('loader3D');
    
    document.getElementById('modal3DTitle').innerText = 'Mô Hình 3D: ' + title;
    
    // Reset state
    loader.classList.remove('hidden');
    modal.classList.remove('hidden');
    
    // Load URL with iframe param
    iframe.src = url + (url.includes('?') ? '&' : '?') + 'iframe=1';
}

function close3DModal() {
    const modal = document.getElementById('modal3D');
    const iframe = document.getElementById('iframe3D');
    
    modal.classList.add('hidden');
    iframe.src = 'about:blank'; // Clear to stop WebGL/resources
}

function toggleTask(id, currentStatus, incompleteChildren) {
    if (currentStatus !== 'completed' && incompleteChildren > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Chưa thể hoàn thành',
            text: 'Vui lòng hoàn thành tất cả các hạng mục con trước!',
            confirmButtonColor: '#3b82f6',
            background: '#1e293b',
            color: '#fff'
        });
        return;
    }

    if (currentStatus !== 'completed') {
        Swal.fire({
            title: 'Xác nhận hoàn thành?',
            text: "Đánh dấu công việc này là đã xong?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Xong!',
            cancelButtonText: 'Hủy',
            background: '#1e293b',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('task-form-' + id).submit();
            }
        });
    } else {
        // Re-opening logic (optional confirmation)
        document.getElementById('task-form-' + id).submit();
    }
}
</script>
@endsection
