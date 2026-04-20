@if($selectedOrder)
<!-- Hero Header -->
<div class="p-8 pb-4">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $selectedOrder->vehicle->model ?? 'Unknown Vehicle' }}</h1>
                @if($selectedOrder->status == 'pending')
                    <button onclick="startRepair()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all animate-pulse">
                        <span class="material-icons-round">build_circle</span>
                        TIẾP NHẬN XE
                    </button>
                @else
                    <span class="px-3 py-1 rounded-lg text-sm font-bold 
                        {{ $selectedOrder->status == 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 
                           (in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']) ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' : 'bg-gray-100 text-gray-700') }}">
                        {{ $selectedOrder->status == 'completed' ? 'Hoàn thành' : (in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']) ? 'Đang xử lý' : 'Chờ xử lý') }}
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap gap-4 md:gap-6 text-sm text-gray-500 dark:text-white/50 font-mono">
                <span class="flex items-center gap-2"><span class="material-icons-round !text-[16px]">badge</span> Biển số: <span class="text-gray-900 dark:text-white font-bold">{{ $selectedOrder->vehicle->license_plate ?? 'N/A' }}</span></span>
                <span class="flex items-center gap-2"><span class="material-icons-round !text-[16px]">fingerprint</span> VIN: {{ Str::limit($selectedOrder->vehicle->vin ?? 'N/A', 10) }}</span>
                <span class="flex items-center gap-2" title="Chủ xe: {{ $selectedOrder->vehicle->owner_name ?? ($selectedOrder->vehicle->user->name ?? 'N/A') }}">
                    <span class="material-icons-round !text-[16px]">person</span> Khách: {{ $selectedOrder->vehicle->owner_name ?? ($selectedOrder->vehicle->user->name ?? 'Khách Lẻ') }}
                </span>
            </div>
        </div>
    </div>
</div>

@php
    $currentStage = 1;
    if ($selectedOrder->status == 'completed') {
        $currentStage = 5;
    } elseif (in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved'])) {
        $vhcTask = $currentTasks->firstWhere('title', 'Kiểm tra tổng quát (3D)') ?? $currentTasks->firstWhere('title', 'Kiểm tra tổng quát (VHC)');
        // If VHC exists and is effectively not done (or if we are just starting)
        // Adjust logic: If VHC is pending, we are in stage 2.
        if ($vhcTask && $vhcTask->status != 'completed') {
             $currentStage = 2;
        } else {
             // If VHC done, check other tasks
             $hasPending = $currentTasks->where('status', '!=', 'completed')->count() > 0;
             if ($hasPending) {
                 $currentStage = 3;
             } else {
                 $currentStage = 4; // QC / Ready to finish
             }
        }
    }
@endphp

<!-- Progress Stepper -->
<div class="px-8 py-6 bg-gray-50 dark:bg-[#0f172a]/50 border-y border-gray-200 dark:border-[#1e293b]">
    <div class="relative flex items-center justify-between w-full max-w-4xl mx-auto md:mx-0">
        <!-- Connecting Line -->
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-0.5 bg-gray-200 dark:bg-[#1e293b] -z-0"></div>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-teal-500 transition-all duration-1000 -z-0" style="width: {{ ($currentStage - 1) * 25 }}%"></div>
        
        <!-- Step 1: Receieved -->
        <div class="relative z-10 flex flex-col items-center gap-2 group cursor-pointer">
            <div class="size-{{ $currentStage == 1 ? '10' : '8' }} rounded-full {{ $currentStage >= 1 ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center font-bold transition-all {{ $currentStage == 1 ? 'ring-4 ring-teal-100 dark:ring-teal-500/20 scale-110' : '' }}">
                @if($currentStage > 1)
                    <span class="material-icons-round !text-[18px]">check</span>
                @else
                    <span class="material-icons-round !text-[20px] animate-pulse">garage</span>
                @endif
            </div>
            <span class="text-{{ $currentStage == 1 ? 'sm' : 'xs' }} font-bold {{ $currentStage >= 1 ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400' }} uppercase tracking-wider hidden md:block">Tiếp Nhận</span>
        </div>

        <!-- Step 2: Diagnosis -->
        <div class="relative z-10 flex flex-col items-center gap-2 group cursor-pointer">
            <div class="size-{{ $currentStage == 2 ? '10' : '8' }} rounded-full {{ $currentStage >= 2 ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' : 'bg-gray-200 dark:bg-[#1e293b] border-2 border-gray-300 dark:border-[#334155] text-gray-400' }} flex items-center justify-center font-bold transition-all {{ $currentStage == 2 ? 'ring-4 ring-teal-100 dark:ring-teal-500/20 scale-110 border-none' : '' }}">
                @if($currentStage > 2)
                    <span class="material-icons-round !text-[18px]">check</span>
                @elseif($currentStage == 2)
                    <span class="material-icons-round !text-[20px] animate-spin">settings_suggest</span>
                @else
                    <span class="text-xs font-mono">02</span>
                @endif
            </div>
            <span class="text-{{ $currentStage == 2 ? 'sm' : 'xs' }} font-bold {{ $currentStage >= 2 ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400' }} uppercase tracking-wider hidden md:block">Kiểm Tra</span>
        </div>

        <!-- Step 3: Repair (Active) -->
        <div class="relative z-10 flex flex-col items-center gap-2">
            <div class="size-{{ $currentStage == 3 ? '10' : '8' }} rounded-full {{ $currentStage >= 3 ? ($currentStage > 3 ? 'bg-teal-500' : 'bg-indigo-600') . ' text-white shadow-lg' : 'bg-gray-200 dark:bg-[#1e293b] border-2 border-gray-300 dark:border-[#334155] text-gray-400' }} flex items-center justify-center transition-all {{ $currentStage == 3 ? 'ring-4 ring-indigo-100 dark:ring-indigo-500/20 scale-110 border-none shadow-indigo-600/40' : '' }}">
                @if($currentStage > 3)
                    <span class="material-icons-round !text-[18px]">check</span>
                @elseif($currentStage == 3)
                    <span class="material-icons-round !text-[20px] animate-pulse">build_circle</span>
                @else
                    <span class="text-xs font-mono">03</span>
                @endif
            </div>
            <span class="text-{{ $currentStage == 3 ? 'sm' : 'xs' }} font-bold {{ $currentStage >= 3 ? ($currentStage > 3 ? 'text-teal-600 dark:text-teal-400' : 'text-indigo-700 dark:text-indigo-400') : 'text-gray-400' }} uppercase tracking-wider">Sửa Chữa</span>
        </div>

        <!-- Step 4: QC -->
        <div class="relative z-10 flex flex-col items-center gap-2 group cursor-pointer">
            <div class="size-{{ $currentStage == 4 ? '10' : '8' }} rounded-full {{ $currentStage >= 4 ? ($currentStage > 4 ? 'bg-teal-500' : 'bg-amber-500') . ' text-white shadow-lg' : 'bg-gray-200 dark:bg-[#1e293b] border-2 border-gray-300 dark:border-[#334155] text-gray-400' }} flex items-center justify-center transition-all {{ $currentStage == 4 ? 'ring-4 ring-amber-100 dark:ring-amber-500/20 scale-110 border-none shadow-amber-500/40' : '' }}">
               @if($currentStage > 4)
                    <span class="material-icons-round !text-[18px]">check</span>
                @elseif($currentStage == 4)
                    <span class="material-icons-round !text-[20px]">fact_check</span>
                @else
                    <span class="text-xs font-mono">04</span>
                @endif
            </div>
            <span class="text-{{ $currentStage == 4 ? 'sm' : 'xs' }} font-bold {{ $currentStage >= 4 ? ($currentStage > 4 ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400') : 'text-gray-400' }} uppercase tracking-wider hidden md:block">KCS / QC</span>
        </div>

        <!-- Step 5: Ready -->
        <div class="relative z-10 flex flex-col items-center gap-2 group cursor-pointer">
            <div class="size-{{ $currentStage == 5 ? '10' : '8' }} rounded-full {{ $currentStage == 5 ? 'bg-green-600 text-white shadow-lg shadow-green-600/40' : 'bg-gray-200 dark:bg-[#1e293b] border-2 border-gray-300 dark:border-[#334155] text-gray-400' }} flex items-center justify-center transition-all {{ $currentStage == 5 ? 'ring-4 ring-green-100 dark:ring-green-500/20 scale-110 border-none' : '' }}">
                @if($currentStage == 5)
                    <span class="material-icons-round !text-[20px]">flag</span>
                @else
                    <span class="text-xs font-mono">05</span>
                @endif
            </div>
            <span class="text-{{ $currentStage == 5 ? 'sm' : 'xs' }} font-bold {{ $currentStage == 5 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }} uppercase tracking-wider hidden md:block">Hoàn Thành</span>
        </div>
    </div>
</div>

<!-- Content Split -->
<div class="flex-1 flex flex-col xl:flex-row">
    <!-- Tasks List -->
    <div class="flex-1 p-8 border-b xl:border-b-0 xl:border-r border-gray-200 dark:border-[#1e293b] bg-gray-50 dark:bg-[#111827]">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-icons-round text-indigo-600">list_alt</span>
                Danh sách công việc
            </h3>
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-end">
                @if(in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved', 'completed']))
                    @if($selectedOrder->status == 'in_progress')
                        <button onclick="window.location.href='{{ route('staff.quote.create', $selectedOrder->id) }}'" class="text-sm whitespace-nowrap shrink-0 w-max bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all transform hover:-translate-y-0.5" title="Tạo Báo Giá gửi Khách Hàng">
                            <span class="material-icons-round !text-[16px]">receipt_long</span>
                            Tạo Báo Giá
                        </button>
                    @else
                        <button onclick="window.location.href='{{ route('staff.quote.show', $selectedOrder->id) }}'" class="text-sm whitespace-nowrap shrink-0 w-max bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all transform hover:-translate-y-0.5" title="Xem chi tiết báo giá dành cho nhân viên">
                            <span class="material-icons-round !text-[16px]">visibility</span>
                            Chi tiết Báo Giá
                        </button>
                        <button onclick="copyGuestLink('{{ URL::signedRoute('guest.quote.show', $selectedOrder->id) }}')" class="text-sm whitespace-nowrap shrink-0 w-max bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all transform hover:-translate-y-0.5" title="Copy link Báo giá để gửi khách (Không cần đăng nhập)">
                            <span class="material-icons-round !text-[16px]">share</span>
                            Copy Link
                        </button>
                    @endif
                @endif
                @php
                    $completedCount = $currentTasks->where('status', 'completed')->count();
                    $totalCount = $currentTasks->count();
                    $allCompleted = $totalCount > 0 && $completedCount === $totalCount;
                @endphp
                
                @if(in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']))
                    @if($allCompleted)
                        <button onclick="completeOrder()" class="text-sm whitespace-nowrap shrink-0 w-max bg-green-500 hover:bg-green-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all transform hover:-translate-y-0.5 animate-pulse" title="Xác nhận hoàn thành tất cả công việc">
                            <span class="material-icons-round !text-[16px]">task_alt</span>
                            HOÀN THÀNH ĐƠN
                        </button>
                    @else
                        <span class="text-xs shrink-0 w-max text-gray-500 dark:text-gray-400 font-mono font-bold bg-white dark:bg-[#1e293b] px-2 py-1.5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" title="Cần hoàn thành tất cả công việc để đóng đơn">
                            {{ $completedCount }}/{{ $totalCount }} HOÀN THÀNH
                        </span>
                    @endif
                @endif
            </div>
        </div>
        
                <div class="space-y-3">
                    @forelse($currentTasks->where('parent_id', null) as $task)
                    <!-- Parent Task -->
                    <div onclick="@if($selectedOrder->status == 'pending') Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe để xem và chỉnh sửa.', 'info'); @elseif($task->customer_approval_status === 'rejected') Swal.fire('Thông báo', 'Khách hàng đã từ chối mục này.', 'warning'); @else openTaskDetails('{{ $task->id }}') @endif" class="group bg-white dark:bg-[#1e293b] rounded-xl p-4 transition-all hover:bg-gray-50 dark:hover:bg-[#334155]/20 border border-dash border-gray-100 dark:border-[#334155] hover:border-indigo-200 dark:hover:border-indigo-500/50 cursor-pointer {{ $task->customer_approval_status === 'rejected' ? 'opacity-60 bg-red-50/50' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="pt-1">
                                <input type="checkbox" onclick="event.stopPropagation(); @if($selectedOrder->status == 'pending') Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe để bắt đầu công việc.', 'info'); @elseif($selectedOrder->status == 'completed') Swal.fire('Thông báo', 'Đơn sửa chữa đã hoàn thành, không thể thay đổi.', 'warning'); @else toggleTask('{{ $task->id }}', '{{ $task->status }}') @endif" {{ $task->status == 'completed' ? 'checked' : '' }} {{ in_array($selectedOrder->status, ['pending', 'completed']) || $task->customer_approval_status === 'rejected' ? 'disabled' : '' }} class="w-5 h-5 rounded border-gray-300 dark:border-white/20 text-indigo-600 focus:ring-0 bg-transparent {{ in_array($selectedOrder->status, ['pending', 'completed']) || $task->customer_approval_status === 'rejected' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} transition-colors">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 {{ $task->status == 'completed' || $task->customer_approval_status === 'rejected' ? 'line-through text-gray-400' : '' }}">{{ $task->title }}</h4>
                                    
                                    <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                        @if($task->customer_approval_status === 'rejected')
                                        <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded uppercase">Khách Từ Chối</span>
                                        @elseif($task->customer_approval_status === 'approved')
                                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase border border-green-200">Đã Phê Duyệt</span>
                                        @endif

                                        @if($task->status == 'completed')
                                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase">Hoàn thành</span>
                                        @elseif($task->mechanic_id)
                                        <span class="text-[10px] font-bold bg-indigo-600 text-white px-2 py-0.5 rounded uppercase shadow-md shadow-indigo-600/30">Đang làm</span>
                                        @endif

                                        @if($task->title == 'Kiểm tra tổng quát (3D)' || $task->title == 'Kiểm tra tổng quát (VHC)')
                                            <a href="{{ route('staff.vehicle.inspection', ['id' => $selectedOrder->vehicle->id ?? 0, 'fullscreen' => 1, 'order_id' => $selectedOrder->id ?? null]) }}" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 px-2 py-1 rounded font-bold flex items-center gap-1 transition-colors">
                                                <span class="material-icons-round !text-[14px]">view_in_ar</span>
                                                Xem 3D
                                            </a>
                                        @endif
                                        
                                        <!-- Assignee Avatar / Indicator -->
                                        @if($task->mechanic_id)
                                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded" title="Người thực hiện: {{ $task->mechanic->name }}">
                                                {{ $task->mechanic_id == auth()->id() ? 'Tôi' : $task->mechanic->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-white/60 block mt-1 line-clamp-2">{{ $task->note ?? 'Chạm để xem chi tiết...' }}</span>
                            </div>
                        </div>

                        <!-- Subtasks -->
                        @if($task->children->count() > 0)
                        <div class="ml-8 pl-4 border-l-2 border-gray-100 dark:border-[#1e293b] mt-2 space-y-2">
                            @foreach($task->children as $child)
                            <div class="flex items-center gap-3 p-3 rounded-lg {{ $child->status == 'completed' ? 'bg-gray-50 dark:bg-[#1f2937]/30 opacity-75' : 'bg-white dark:bg-[#1f2937]/20 border border-gray-100 dark:border-[#374151]' }}">
                                <input onclick="event.stopPropagation(); @if($selectedOrder->status == 'pending') Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe để bắt đầu công việc.', 'info'); @elseif($selectedOrder->status == 'completed') Swal.fire('Thông báo', 'Đơn sửa chữa đã hoàn thành, không thể thay đổi.', 'warning'); @else toggleTask('{{ $child->id }}', '{{ $child->status }}') @endif" {{ $child->status == 'completed' ? 'checked' : '' }} {{ in_array($selectedOrder->status, ['pending', 'completed']) ? 'disabled' : '' }} class="size-4 shrink-0 rounded border-gray-300 dark:border-white/20 text-teal-600 focus:ring-0 bg-transparent {{ in_array($selectedOrder->status, ['pending', 'completed']) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" type="checkbox">
                                <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300 {{ $child->status == 'completed' ? 'line-through decoration-gray-400' : '' }} break-words pr-2">{{ $child->title }}</span>
                                <!-- Child Task Assignment -->
                                <div class="flex items-center gap-1 shrink-0" onclick="event.stopPropagation()">
                                    @if(!$child->mechanic_id)
                                        @if($selectedOrder->status == 'pending')
                                            <button onclick="event.stopPropagation(); Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe để nhận việc.', 'info');" class="whitespace-nowrap text-[10px] bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded text-gray-400 font-medium cursor-not-allowed opacity-60">Nhận làm</button>
                                        @elseif($selectedOrder->status == 'completed')
                                             <span class="whitespace-nowrap text-[10px] text-gray-400 dark:text-gray-500 italic max-w-[60px] truncate">---</span>
                                        @else
                                            <button onclick="assignTask('{{ $child->id }}')" class="whitespace-nowrap text-[10px] bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 px-2 py-1 rounded text-gray-500 dark:text-gray-400 font-medium transition-colors">Nhận làm</button>
                                        @endif
                                    @elseif($child->mechanic_id == auth()->id())
                                        <span class="whitespace-nowrap text-[10px] text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded">Tôi</span>
                                        @if($selectedOrder->status != 'completed')
                                            <button onclick="unassignTask('{{ $child->id }}')" class="text-[10px] text-red-500 hover:text-red-700 px-1"><span class="material-icons-round text-[12px]">close</span></button>
                                        @endif
                                    @else
                                        <span class="whitespace-nowrap text-[10px] text-gray-400 dark:text-gray-500 italic max-w-[60px] truncate">{{ $child->mechanic->name ?? '...' }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-8 text-center border-2 border-dashed border-gray-200 dark:border-[#1e293b] rounded-xl">
                        <p class="text-gray-400">Chưa có công việc nào được gán.</p>
                        <button onclick="addTask(null)" class="mt-2 text-indigo-600 font-bold hover:underline">Thêm công việc ngay</button>
                    </div>
                    @endforelse
                </div>

                <button onclick="@if($selectedOrder->status == 'pending') Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe trước khi thêm công việc.', 'info'); @elseif($selectedOrder->status == 'completed') Swal.fire('Thông báo', 'Đơn đã hoàn thành.', 'warning'); @else addTask(null) @endif" class="mt-6 w-full py-3 border border-dashed border-gray-300 dark:border-white/20 rounded-xl text-gray-500 dark:text-white/40 hover:text-indigo-600 dark:hover:text-white hover:border-indigo-300 dark:hover:border-white/40 hover:bg-indigo-50 dark:hover:bg-white/5 flex items-center justify-center gap-2 transition-all font-medium {{ in_array($selectedOrder->status, ['pending', 'completed']) ? 'opacity-60 cursor-not-allowed' : '' }}">
                    <span class="material-icons-round">add_task</span>
                    <span>Thêm công việc phát sinh</span>
                </button>
            </div>

            <!-- Visualizer & Info Panel -->
            <div class="w-full xl:w-[400px] p-8 bg-gray-50 dark:bg-[#0f172a] flex flex-col gap-6 border-l border-gray-200 dark:border-transparent">
                <!-- Customer Quick View -->
                <div class="bg-white dark:bg-[#1e293b] rounded-xl p-4 shadow-sm border border-gray-200 dark:border-[#334155]">
                    <div class="flex items-center gap-3 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-full text-indigo-600 dark:text-indigo-400">
                            <span class="material-icons-round !text-[20px]">person</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm leading-tight">{{ $selectedOrder->vehicle->owner_name ?? 'Khách vãng lai' }}</h4>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $selectedOrder->vehicle->user ? 'THÀNH VIÊN' : 'KHÁCH LẺ' }}</span>
                        </div>
                        @if($selectedOrder->vehicle->user_id)
                        <a href="{{ route('staff.customers.show', $selectedOrder->vehicle->user_id) }}" class="ml-auto text-xs text-indigo-600 hover:text-indigo-800 font-bold">Chi tiết</a>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm">
                         <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            <span class="material-icons-round !text-[16px] text-gray-400">call</span>
                            <span class="font-mono font-bold">{{ $selectedOrder->vehicle->owner_phone ?? '---' }}</span>
                            <a href="tel:{{ $selectedOrder->vehicle->owner_phone }}" class="ml-auto text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-bold hover:bg-green-200">GỌI</a>
                        </div>
                        @if($selectedOrder->vehicle->user && $selectedOrder->vehicle->user->email)
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            <span class="material-icons-round !text-[16px] text-gray-400">email</span>
                            <span class="truncate">{{ $selectedOrder->vehicle->user->email }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Mechanic Quick View (if assigned) -->
                @php
                    $orderMechanics = isset($currentTasks) ? $currentTasks->pluck('mechanic')->filter()->unique('id') : collect([]);
                @endphp
                @if($orderMechanics->isNotEmpty())
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 shadow-sm border border-indigo-100 dark:border-indigo-800/30">
                    <h4 class="text-xs font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-icons-round !text-[16px]">engineering</span>
                        Nhân viên sửa chữa
                    </h4>
                    <div class="space-y-3">
                        @foreach($orderMechanics as $mech)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-200 flex items-center justify-center font-bold text-sm uppercase">{{ mb_substr($mech->name, 0, 1) }}</div>
                            <div>
                                <div class="text-sm font-bold text-indigo-900 dark:text-indigo-100">{{ $mech->name }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 3D Visualizer Card -->
                <div class="relative rounded-xl overflow-hidden group border border-gray-200 dark:border-[#1e293b] hover:border-indigo-500/50 transition-colors shadow-lg">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent z-10"></div>
                    <!-- Placeholder Img -->
                    <div class="h-64 w-full bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1486262715619-01b8c24545b7?auto=format&fit=crop&q=80&w=1974');"></div>
                    <div class="absolute bottom-0 left-0 w-full p-6 z-20 flex flex-col gap-3">
                        <div>
                            <h4 class="text-white font-bold text-lg">Mô hình 3D (Digital Twin)</h4>
                            <p class="text-gray-300 text-xs text-shadow-sm">Trực quan hóa linh kiện và overlay hướng dẫn kỹ thuật.</p>
                        </div>
                        <a href="{{ route('staff.vehicle.inspection', ['id' => $selectedOrder->vehicle->id ?? 0, 'fullscreen' => 1, 'order_id' => $selectedOrder->id ?? null]) }}" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded flex items-center justify-center gap-2 shadow-lg shadow-indigo-900/20 transition-all transform group-hover:translate-y-[-2px]">
                            <span class="material-icons-round">view_in_ar</span>
                            Mở Visualizer
                        </a>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-[#1e293b] pb-2">
                        <h4 class="text-sm font-bold text-gray-500 dark:text-white/50 uppercase tracking-widest">Ghi chú dịch vụ</h4>
                        <button onclick="addNote()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                            <span class="material-icons-round text-lg">add_circle</span>
                        </button>
                    </div>
                    
                    @if($selectedOrder->notes)
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-[#451a03]/30 border border-amber-200 dark:border-[#451a03]">
                        <div class="flex items-start gap-3">
                            <span class="material-icons-round text-amber-500 mt-1 !text-[20px]">sticky_note_2</span>
                            <div class="flex-1">
                                <p class="text-amber-900 dark:text-amber-100 text-sm leading-relaxed whitespace-pre-line">{{ $selectedOrder->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4 text-gray-400 dark:text-gray-600 italic text-sm">
                        Chưa có ghi chú nào.
                    </div>
                    @endif
                </div>

                <!-- Parts List Mini -->
                <div class="flex-1">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-[#1e293b] pb-2 mb-4">
                        <h4 class="text-sm font-bold text-gray-500 dark:text-white/50 uppercase tracking-widest">Vật tư đã cấp</h4>
                        <button onclick="storeQuickItem()" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 transition-colors">
                            <span class="material-icons-round text-lg">add_circle</span>
                        </button>
                    </div>
                    <ul class="space-y-3">
                        @foreach($selectedOrder->items as $item)
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-700 dark:text-white font-medium">{{ $item->name ?? $item->item_name }}</span>
                            <span class="text-teal-700 dark:text-teal-400 bg-teal-100 dark:bg-teal-500/10 px-2 py-0.5 rounded text-xs font-bold">x{{ $item->quantity ?? 1 }}</span>
                        </li>
                        @endforeach
                    </ul>
                    
                    <!-- Support Button -->
                    <button onclick="requestSupport()" class="mt-6 w-full py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                        <span class="material-icons-round text-gray-500">support_agent</span>
                        Yêu cầu hỗ trợ / Linh kiện
                    </button>
                </div>
            </div>

</div>
@else
<!-- Empty State -->
<div class="flex flex-col items-center justify-center h-full text-center p-8">
    <div class="w-24 h-24 rounded-full bg-gray-100 dark:bg-[#1e293b] flex items-center justify-center mb-4">
        <span class="material-icons-round text-gray-300 dark:text-gray-600 text-5xl">garage</span>
    </div>
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Chưa chọn xe nào</h3>
    <p class="text-gray-500 dark:text-gray-400 max-w-sm">Vui lòng chọn một xe từ danh sách bên trái để xem chi tiết và cập nhật tiến độ.</p>
</div>
@endif

<!-- Copy Quote Link Modal -->
<div id="copyQuoteModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-link"></i>
                </div>
                Chia sẻ Báo Giá
            </h3>
            <button onclick="closeCopyQuoteModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-600 dark:text-slate-400">Copy đường link bên dưới để gửi cho khách hàng xem và xác nhận báo giá.</p>
            <div class="relative">
                <input type="text" id="copyQuoteUrlInput" readonly class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-mono tracking-tight cursor-text">
                <button onclick="executeQuoteCopyInModal()" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg transition-colors" title="Copy">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <div id="copyQuoteSuccessMsg" class="hidden items-center gap-2 text-emerald-600 dark:text-emerald-400 text-sm font-bold bg-emerald-50 dark:bg-emerald-500/10 px-4 py-2 rounded-lg justify-center border border-emerald-100 dark:border-emerald-500/20">
                <i class="fas fa-check-circle"></i> Đã sao chép vào bộ nhớ đệm!
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end">
             <button onclick="closeCopyQuoteModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white bg-white hover:bg-slate-100 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 shadow-sm transition-all focus:ring-2 focus:ring-slate-400/20">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
    function copyGuestLink(url) {
        // Redefined to open modal
        const copyModal = document.getElementById('copyQuoteModal');
        const copyModalContent = copyModal.querySelector('div');
        const copyUrlInput = document.getElementById('copyQuoteUrlInput');
        
        copyUrlInput.value = url;
        copyModal.classList.remove('hidden');
        copyModal.classList.add('flex');
        
        setTimeout(() => {
            copyModal.classList.remove('opacity-0');
            copyModalContent.classList.remove('scale-95');
            copyModalContent.classList.add('scale-100');
            copyUrlInput.select();
        }, 10);
    }

    function closeCopyQuoteModal() {
        const copyModal = document.getElementById('copyQuoteModal');
        const copyModalContent = copyModal.querySelector('div');
        const copySuccessMsg = document.getElementById('copyQuoteSuccessMsg');

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

    function executeQuoteCopyInModal() {
        const copyUrlInput = document.getElementById('copyQuoteUrlInput');
        const copySuccessMsg = document.getElementById('copyQuoteSuccessMsg');

        copyUrlInput.select();
        copyUrlInput.setSelectionRange(0, 99999);
        
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
</script>
