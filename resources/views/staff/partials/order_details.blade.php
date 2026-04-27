@if($selectedOrder)
@once
<style>
    @media (max-width: 767px) {
        #order-details-container > div,
        #order-details-container .p-8 {
            padding: 1rem !important;
        }
        #order-details-container h1 {
            font-size: 1.75rem !important;
            line-height: 2.1rem !important;
        }
        #order-details-container .flex {
            min-width: 0;
        }
        #order-details-container table {
            min-width: 680px;
        }
        #order-details-container .grid {
            min-width: 0;
        }
        #order-details-container button,
        #order-details-container a {
            min-height: 40px;
        }
    }
</style>
@endonce
@php
    $statusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra',
        'pending_approval' => 'Chờ khách duyệt',
        'approved' => 'Khách đã duyệt',
        'completed' => $selectedOrder->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán',
        'cancelled' => 'Đã hủy',
    ];
    $isLocked = in_array($selectedOrder->status, ['completed', 'cancelled'], true) || $selectedOrder->payment_status === 'paid';
    $canManageFlow = ! (auth()->user()?->isTechnician() && ! auth()->user()?->isAdmin() && ! auth()->user()?->isManager());
    $canEditIntake = $canManageFlow && $selectedOrder->status === 'in_progress' && ! $isLocked;
    $canWorkTasks = in_array($selectedOrder->status, ['in_progress', 'approved'], true) && ! $isLocked;
    $canCompleteOrder = $canManageFlow && $selectedOrder->status === 'approved' && ! $isLocked;
    $canCreateQuote = $canManageFlow && $selectedOrder->status === 'in_progress' && ! $isLocked;
    $canViewQuote = in_array($selectedOrder->status, ['pending_approval', 'approved', 'completed'], true)
        || $selectedOrder->quote_status === 'rejected';
    $quoteReviewTasks = ($currentTasks ?? collect())
        ->whereNotNull('parent_id')
        ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty());
    $quoteApprovedTasks = $quoteReviewTasks->where('customer_approval_status', 'approved');
    $quoteRejectedTasks = $quoteReviewTasks->where('customer_approval_status', 'rejected');
    $quotePendingTasks = $quoteReviewTasks->filter(fn ($task) => blank($task->customer_approval_status) || $task->customer_approval_status === 'pending');
    $quoteApprovedTotal = $quoteApprovedTasks->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal'));
    $quoteTotal = $quoteReviewTasks->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal'));
@endphp
<!-- Hero Header -->
<div class="p-8 pb-4">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $selectedOrder->vehicle->model ?? 'Xe không rõ' }}</h1>
                @if($selectedOrder->status == 'pending')
                    <div class="flex items-center gap-2 block md:flex">
                        <button onclick="startRepair()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all animate-pulse">
                            <span class="material-icons-round">build_circle</span>
                            TIẾP NHẬN XE
                        </button>
                        <button onclick="abandonOrder()" class="px-4 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 text-sm font-bold rounded-lg shadow-sm border border-red-200 dark:border-red-800 flex items-center gap-2 transition-all">
                            <span class="material-icons-round">delete_forever</span>
                            BỎ XE
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-lg text-sm font-bold 
                            {{ $selectedOrder->status == 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 
                               (in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']) ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' : 'bg-gray-100 text-gray-700') }}">
                            {{ $selectedOrder->status == 'completed' ? 'Hoàn thành' : (in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']) ? 'Đang xử lý' : 'Chờ xử lý') }}
                        </span>
                        <span class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            {{ $statusLabels[$selectedOrder->status] ?? ucfirst($selectedOrder->status) }}
                        </span>
                        @if($selectedOrder->status === 'in_progress')
                            <button onclick="cancelRepair()" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 text-sm font-bold rounded-lg shadow-sm flex items-center gap-1 transition-all">
                                <span class="material-icons-round !text-[16px]">undo</span>
                                Hủy nhận sửa
                            </button>
                        @endif
                        @if($selectedOrder->status === 'pending_approval')
                            <span class="px-3 py-1.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-bold rounded-lg">
                                Khóa sửa trong lúc chờ khách duyệt
                            </span>
                        @elseif($selectedOrder->status === 'approved')
                            <span class="px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 text-xs font-bold rounded-lg">
                                Chỉ thi công task đã duyệt
                            </span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-4 md:gap-6 text-sm text-gray-500 dark:text-white/50 font-mono">
                <span class="flex items-center gap-2"><span class="material-icons-round !text-[16px]">badge</span> Biển số: <span class="text-gray-900 dark:text-white font-bold">{{ $selectedOrder->vehicle->license_plate ?? '---' }}</span></span>
                <span class="flex items-center gap-2"><span class="material-icons-round !text-[16px]">fingerprint</span> VIN: {{ Str::limit($selectedOrder->vehicle->vin ?? '---', 10) }}</span>
                <span class="flex items-center gap-2" title="Chủ xe: {{ $selectedOrder->vehicle->owner_name ?? ($selectedOrder->vehicle->user->name ?? '---') }}">
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
        $actionableTasks = $currentTasks->reject(fn ($task) => $task->customer_approval_status === 'rejected');
        $vhcTask = $actionableTasks->firstWhere('title', 'Kiểm tra tổng quát (3D)') ?? $actionableTasks->firstWhere('title', 'Kiểm tra tổng quát (VHC)');
        // If VHC exists and is effectively not done (or if we are just starting)
        // Adjust logic: If VHC is pending, we are in stage 2.
        if ($vhcTask && $vhcTask->status != 'completed') {
             $currentStage = 2;
        } else {
             // If VHC done, check other tasks
             $hasPending = $actionableTasks->where('status', '!=', 'completed')->count() > 0;
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

@if($canViewQuote && $quoteReviewTasks->isNotEmpty())
<div class="px-8 py-5 bg-white dark:bg-[#0f172a] border-b border-gray-200 dark:border-[#1e293b]">
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_auto] gap-4 items-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/70 p-5">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/20">
                    <span class="material-icons-round !text-[15px]">receipt_long</span>
                    Kết quả báo giá
                </span>
                @if($selectedOrder->status === 'pending_approval')
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Đang chờ khách phản hồi</span>
                @elseif($selectedOrder->quote_status === 'rejected')
                    <span class="text-xs font-bold text-red-700 dark:text-red-300">Khách đã từ chối toàn bộ báo giá</span>
                @else
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Khách đã phản hồi, chỉ thi công hạng mục được đồng ý</span>
                @endif
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <div class="text-[10px] uppercase tracking-widest font-black text-slate-500">Tổng hạng mục</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white">{{ $quoteReviewTasks->count() }}</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-500/30 p-3">
                    <div class="text-[10px] uppercase tracking-widest font-black text-emerald-600 dark:text-emerald-300">Đồng ý</div>
                    <div class="text-xl font-black text-emerald-700 dark:text-emerald-300">{{ $quoteApprovedTasks->count() }}</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-slate-800 border border-red-200 dark:border-red-500/30 p-3">
                    <div class="text-[10px] uppercase tracking-widest font-black text-red-600 dark:text-red-300">Từ chối</div>
                    <div class="text-xl font-black text-red-700 dark:text-red-300">{{ $quoteRejectedTasks->count() }}</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-500/30 p-3">
                    <div class="text-[10px] uppercase tracking-widest font-black text-amber-600 dark:text-amber-300">Chờ phản hồi</div>
                    <div class="text-xl font-black text-amber-700 dark:text-amber-300">{{ $quotePendingTasks->count() }}</div>
                </div>
            </div>
        </div>
        <div class="lg:w-64 rounded-2xl bg-slate-900 dark:bg-indigo-600 text-white p-5">
            <div class="text-[10px] uppercase tracking-widest font-black text-slate-300 dark:text-indigo-100">Giá trị khách đồng ý</div>
            <div class="text-2xl font-black mt-1">{{ number_format($quoteApprovedTotal ?: ($selectedOrder->status === 'pending_approval' ? $quoteTotal : 0)) }}đ</div>
            <a href="{{ route('staff.quote.show', $selectedOrder->id) }}" class="mt-4 inline-flex w-full justify-center items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-bold transition">
                <span class="material-icons-round !text-[16px]">visibility</span>
                Xem chi tiết
            </a>
        </div>
    </div>
</div>
@endif

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
                @if($canCreateQuote || $canViewQuote)
                    @if($canCreateQuote)
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
                    $actionableTasks = $currentTasks->reject(fn ($task) => $task->customer_approval_status === 'rejected');
                    $completedCount = $actionableTasks->where('status', 'completed')->count();
                    $totalCount = $actionableTasks->count();
                    $rejectedCount = $currentTasks->where('customer_approval_status', 'rejected')->count();
                    $allCompleted = $totalCount > 0 && $completedCount === $totalCount;
                @endphp
                
                @if(in_array($selectedOrder->status, ['in_progress', 'pending_approval', 'approved']))
                    @if($allCompleted)
                        <button onclick="@if($canCompleteOrder) completeOrder() @else Swal.fire('Thông báo', 'Chỉ hoàn thành đơn sau khi khách đã duyệt báo giá.', 'info') @endif" class="text-sm whitespace-nowrap shrink-0 w-max {{ $canCompleteOrder ? 'bg-green-500 hover:bg-green-600 text-white animate-pulse' : 'bg-gray-200 dark:bg-slate-700 text-gray-500 dark:text-gray-300 cursor-not-allowed' }} font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all transform hover:-translate-y-0.5" title="Xác nhận hoàn thành tất cả công việc">
                            <span class="material-icons-round !text-[16px]">task_alt</span>
                            HOÀN THÀNH ĐƠN
                        </button>
                    @else
                        <span class="text-xs shrink-0 w-max text-gray-500 dark:text-gray-400 font-mono font-bold bg-white dark:bg-[#1e293b] px-2 py-1.5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" title="Cần hoàn thành tất cả công việc để đóng đơn">
                            {{ $completedCount }}/{{ $totalCount }} HOÀN THÀNH
                            @if($rejectedCount > 0)
                                <span class="ml-1 text-red-500">({{ $rejectedCount }} việc khách từ chối)</span>
                            @endif
                        </span>
                    @endif
                @elseif($selectedOrder->status == 'completed')
                    @if($selectedOrder->payment_status !== 'paid')
                        <button onclick="openPaymentModal({{ $selectedOrder->total_amount ?? 0 }})" class="text-sm whitespace-nowrap shrink-0 w-max bg-teal-500 hover:bg-teal-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 transition-all animate-pulse">
                            <span class="material-icons-round !text-[16px]">account_balance_wallet</span>
                            THANH TOÁN
                        </button>
                    @else
                        <a href="{{ route('staff.order.invoice', $selectedOrder->id) }}" target="_blank" class="text-sm whitespace-nowrap shrink-0 w-max bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-md flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-icons-round !text-[16px]">print</span>
                            In hóa đơn
                        </a>
                    @endif
                @endif
            </div>
        </div>
        
                <div class="space-y-3">
                    @forelse($currentTasks->where('parent_id', null) as $task)
                    @php
                        $incompleteChildrenCount = $task->children
                            ->where('status', '!=', 'completed')
                            ->reject(fn ($child) => $child->customer_approval_status === 'rejected')
                            ->count();
                        $parentBlockedByChildren = $task->status !== 'completed' && $incompleteChildrenCount > 0;
                        $taskBlocked = ! $canWorkTasks || $task->customer_approval_status === 'rejected' || $parentBlockedByChildren;
                        $taskBlockedMessage = $parentBlockedByChildren
                            ? "Cần hoàn thành {$incompleteChildrenCount} nhiệm vụ con trước khi hoàn thành nhiệm vụ cha."
                            : 'Công việc này đang bị khóa theo trạng thái đơn hoặc đã bị khách từ chối.';
                    @endphp
                    <!-- Parent Task -->
                    <div onclick="@if($selectedOrder->status == 'pending') Swal.fire('Thông báo', 'Vui lòng \'Tiếp Nhận\' xe để xem và chỉnh sửa.', 'info'); @elseif($task->customer_approval_status === 'rejected') Swal.fire('Thông báo', 'Khách hàng đã từ chối mục này.', 'warning'); @else openTaskDetails('{{ $task->id }}') @endif" class="group bg-white dark:bg-[#1e293b] rounded-xl p-4 transition-all hover:bg-gray-50 dark:hover:bg-[#334155]/20 border border-dash border-gray-100 dark:border-[#334155] hover:border-indigo-200 dark:hover:border-indigo-500/50 cursor-pointer {{ $task->customer_approval_status === 'rejected' ? 'opacity-60 bg-red-50/50' : '' }} {{ $parentBlockedByChildren ? 'opacity-75' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="pt-1">
                                <input type="checkbox" onclick="event.stopPropagation(); @if($taskBlocked) Swal.fire('Thông báo', '{{ $taskBlockedMessage }}', 'info'); @else toggleTask('{{ $task->id }}', '{{ $task->status }}') @endif" {{ $task->status == 'completed' ? 'checked' : '' }} {{ $taskBlocked ? 'disabled' : '' }} title="{{ $parentBlockedByChildren ? $taskBlockedMessage : '' }}" class="w-5 h-5 rounded border-gray-300 dark:border-white/20 text-indigo-600 focus:ring-0 bg-transparent {{ $taskBlocked ? 'opacity-40 cursor-not-allowed grayscale' : 'cursor-pointer' }} transition-colors">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 {{ $task->status == 'completed' || $task->customer_approval_status === 'rejected' ? 'line-through text-gray-400' : '' }}">{{ str_replace(' (VHC)', '', $task->title) }}</h4>
                                    
                                    <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                        @if($task->customer_approval_status === 'rejected')
                                        <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded uppercase">Khách Từ Chối</span>
                                        @elseif($task->customer_approval_status === 'approved')
                                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase border border-green-200">Đã Phê Duyệt</span>
                                        @endif

                                        @if($task->status == 'completed')
                                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase">Hoàn thành</span>
                                        @elseif($parentBlockedByChildren)
                                        <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase border border-amber-200">{{ $incompleteChildrenCount }} việc con chưa xong</span>
                                        @elseif($task->mechanic_id)
                                        <span class="text-[10px] font-bold bg-indigo-600 text-white px-2 py-0.5 rounded uppercase shadow-md shadow-indigo-600/30">Đang làm</span>
                                        @endif

                                        @if($task->title == 'Kiểm tra tổng quát (3D)' || $task->title == 'Kiểm tra tổng quát (VHC)')
                                            <a href="{{ route('staff.vehicle.inspection', ['id' => $selectedOrder->vehicle->id ?? 0, 'fullscreen' => 1, 'order_id' => $selectedOrder->id ?? null]) }}" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 px-2 py-1 rounded font-bold flex items-center gap-1 transition-colors">
                                                <span class="material-icons-round !text-[14px]">view_in_ar</span>
                                                Kiểm tra 3D
                                            </a>
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
                            @php
                                $childBlocked = ! $canWorkTasks || $child->customer_approval_status === 'rejected';
                                $childApprovalLabel = match ($child->customer_approval_status) {
                                    'approved' => 'Khách đồng ý',
                                    'rejected' => 'Khách từ chối',
                                    default => null,
                                };
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-lg {{ $child->status == 'completed' ? 'bg-gray-50 dark:bg-[#1f2937]/30 opacity-75' : 'bg-white dark:bg-[#1f2937]/20 border border-gray-100 dark:border-[#374151]' }} {{ $child->customer_approval_status === 'rejected' ? 'bg-red-50/70 dark:bg-red-950/20 border-red-200 dark:border-red-900/60 opacity-75' : '' }} {{ $child->customer_approval_status === 'approved' ? 'border-green-200 dark:border-green-900/50' : '' }}">
                                <input onclick="event.stopPropagation(); @if($childBlocked) Swal.fire('Thông báo', 'Task này đang bị khóa theo trạng thái đơn hoặc đã bị khách từ chối.', 'info'); @else toggleTask('{{ $child->id }}', '{{ $child->status }}') @endif" {{ $child->status == 'completed' ? 'checked' : '' }} {{ $childBlocked ? 'disabled' : '' }} class="size-4 shrink-0 rounded border-gray-300 dark:border-white/20 text-teal-600 focus:ring-0 bg-transparent {{ $childBlocked ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" type="checkbox">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 {{ $child->status == 'completed' || $child->customer_approval_status === 'rejected' ? 'line-through decoration-gray-400' : '' }} break-words pr-2">{{ $child->title }}</span>
                                        @if($childApprovalLabel)
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded uppercase border {{ $child->customer_approval_status === 'approved' ? 'bg-green-100 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-300 dark:border-green-800' : 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-800' }}">
                                                {{ $childApprovalLabel }}
                                            </span>
                                        @endif
                                        @if($child->status == 'completed')
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded uppercase bg-teal-100 text-teal-700 border border-teal-200 dark:bg-teal-500/10 dark:text-teal-300 dark:border-teal-800">Đã xong</span>
                                        @endif
                                    </div>
                                    @if($child->customer_approval_status === 'rejected')
                                        <p class="text-xs text-red-600 dark:text-red-300 mt-1">Không thi công hạng mục này nếu chưa có xác nhận mới từ khách.</p>
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
                        <button onclick="@if($canEditIntake) addTask(null) @else Swal.fire('Thông báo', 'Chỉ thêm công việc khi đơn đang kiểm tra.', 'info') @endif" class="mt-2 text-indigo-600 font-bold hover:underline {{ ! $canEditIntake ? 'opacity-60 cursor-not-allowed' : '' }}">Thêm công việc ngay</button>
                    </div>
                    @endforelse
                </div>

                <button onclick="@if($canEditIntake) addTask(null) @else Swal.fire('Thông báo', 'Chỉ thêm công việc khi đơn đang kiểm tra, trước khi gửi báo giá.', 'info') @endif" class="mt-6 w-full py-3 border border-dashed border-gray-300 dark:border-white/20 rounded-xl text-gray-500 dark:text-white/40 hover:text-indigo-600 dark:hover:text-white hover:border-indigo-300 dark:hover:border-white/40 hover:bg-indigo-50 dark:hover:bg-white/5 flex items-center justify-center gap-2 transition-all font-medium {{ ! $canEditIntake ? 'opacity-60 cursor-not-allowed' : '' }}">
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
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent z-10"></div>
                    <div class="absolute inset-0 bg-indigo-500/20 mix-blend-overlay z-10 pointer-events-none"></div>
                    <!-- 3D Digital Background -->
                    <div class="h-64 w-full bg-cover bg-center transition-transform duration-1000 group-hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80');">
                        <!-- CSS Data Grid Overlay -->
                        <div class="absolute inset-0 z-0 opacity-30" style="background-image: linear-gradient(rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 30px 30px;"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full p-6 z-20 flex flex-col gap-3">
                        <div>
                            <h4 class="text-white font-bold text-lg text-shadow-lg">Mô hình 3D</h4>
                            <p class="text-indigo-100 font-medium text-xs mt-1">Trực quan hóa linh kiện và overlay hướng dẫn kỹ thuật.</p>
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

                <!-- Activity Timeline -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-[#1e293b] pb-2">
                        <h4 class="text-sm font-bold text-gray-500 dark:text-white/50 uppercase tracking-widest">Lịch sử thao tác</h4>
                    </div>
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @forelse(($orderActivities ?? collect()) as $activity)
                            @php
                                $activityActionLabels = [
                                    'STAFF_ORDER_INTAKE' => 'Tiếp nhận xe',
                                    'STAFF_VHC_SAVED' => 'Lưu kiểm tra VHC',
                                    'STAFF_ORDER_STATUS_UPDATED' => 'Cập nhật trạng thái',
                                    'STAFF_TASK_CREATED' => 'Thêm công việc',
                                    'STAFF_TASK_STATUS_UPDATED' => 'Cập nhật công việc',
                                    'STAFF_TASK_UPDATED' => 'Sửa công việc',
                                    'STAFF_TASK_DELETED' => 'Xóa công việc',
                                    'STAFF_TASK_TOGGLED' => 'Đổi trạng thái công việc',
                                    'STAFF_NOTE_ADDED' => 'Thêm ghi chú',
                                    'STAFF_QUICK_ITEM_ADDED' => 'Thêm vật tư nhanh',
                                    'STAFF_ITEM_ADDED' => 'Thêm vật tư',
                                    'STAFF_MATERIAL_REQUESTED' => 'Yêu cầu vật tư',
                                    'STAFF_SUPPORT_REQUESTED' => 'Yêu cầu hỗ trợ',
                                    'STAFF_QUOTE_SENT' => 'Gửi báo giá',
                                    'CUSTOMER_QUOTE_REVIEWED' => 'Khách phản hồi báo giá',
                                    'STAFF_PAYMENT_RECEIVED' => 'Xác nhận thanh toán',
                                ];

                                $activityDetails = strtr($activity->details ?? '', [
                                    'Order #' => 'Đơn #',
                                    'order #' => 'đơn #',
                                    'pending_approval' => 'đang chờ khách duyệt',
                                    'in_progress' => 'đang kiểm tra/lập báo giá',
                                    'pending' => 'đang chờ tiếp nhận',
                                    'approved' => 'khách đã duyệt',
                                    'completed' => 'đã hoàn thành',
                                    'cancelled' => 'đã hủy',
                                    'paid' => 'đã thanh toán',
                                    'sent' => 'đã gửi',
                                    'draft' => 'bản nháp',
                                ]);
                            @endphp
                            <div class="flex gap-3 text-sm">
                                <div class="mt-1 size-2 rounded-full bg-indigo-500 shrink-0"></div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-gray-800 dark:text-gray-100">{{ $activity->user->name ?? 'System' }}</span>
                                        <span class="text-[10px] uppercase font-bold bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded">{{ $activityActionLabels[$activity->action] ?? $activity->action }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 break-words">{{ $activityDetails }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ $activity->created_at?->format('H:i d/m/Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-400 dark:text-gray-600 italic text-sm">
                                Chưa có lịch sử thao tác cho đơn này.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Parts List Mini -->
                <div class="flex-1">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-[#1e293b] pb-2 mb-4">
                        <h4 class="text-sm font-bold text-gray-500 dark:text-white/50 uppercase tracking-widest">Vật tư đã cấp</h4>
                        <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 transition-colors">
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
