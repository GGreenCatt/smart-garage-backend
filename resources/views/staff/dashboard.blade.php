@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('main_class', 'flex flex-col h-full p-0 overflow-hidden relative')

@section('full-width-content')
<!-- Main Workspace -->
<div class="flex flex-1 overflow-hidden h-full">
    <!-- Left Panel: Master List -->
    <aside id="leftPanel" class="w-[400px] flex flex-col border-r border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120] z-10 transition-all duration-300 relative">
        <!-- Quick Add & Search Dock -->
        <div class="p-4 border-b border-gray-200 dark:border-[#1e293b] space-y-4 bg-gray-50 dark:bg-[#0f172a]">
            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <input id="quickInput" class="w-full bg-white dark:bg-[#1e293b] border border-gray-300 dark:border-[#1e293b] rounded h-10 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono uppercase transition-all" placeholder="Nhập VIN/Biển số..." type="text">
                </div>
                <button onclick="openAddVehicleModal()" class="h-10 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-sm flex items-center gap-1 transition-colors">
                    <span class="material-icons-round !text-[18px]">add</span>
                    <span>Thêm</span>
                </button>
            </div>
            <div class="relative">
                <span class="material-icons-round absolute left-3 top-2.5 text-gray-400 !text-[20px]">search</span>
                <input class="w-full bg-white dark:bg-[#1e293b] border border-gray-300 dark:border-transparent rounded h-10 pl-10 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-transparent" placeholder="Tìm kiếm biển số, SĐT, Tên KH..." type="text">
            </div>
        </div>

        <!-- Scrollable List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <!-- Section: New Arrivals (Waiting) -->
            <div onclick="toggleSection('new-arrivals')" class="sticky top-0 z-10 bg-gray-100/95 dark:bg-[#0B1120]/95 backdrop-blur-sm px-4 py-2 border-b border-gray-200 dark:border-[#1e293b] flex justify-between items-center group cursor-pointer select-none">
                <h3 class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider flex items-center gap-2">
                    <span id="icon-new-arrivals" class="material-icons-round !text-[16px] transition-transform duration-300">expand_more</span>
                    <span class="size-2 rounded-full bg-teal-500 animate-pulse"></span>
                    Danh Sách Chờ
                </h3>
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $waiting->count() }}</span>
            </div>
            <div id="list-new-arrivals" class="divide-y divide-gray-200 dark:divide-[#1e293b]/50 transition-all duration-300 origin-top">
                @forelse($waiting as $order)
                <div onclick="markAsViewed({{ $order->id }}, this); loadOrder({{ $order->id }}, this, 'waitlist')" class="order-item p-4 pr-12 cursor-pointer transition-colors group border-l-2 relative {{ isset($selectedOrder) && $selectedOrder->id == $order->id ? 'bg-indigo-50 dark:bg-white/10 border-indigo-500' : 'hover:bg-gray-100 dark:hover:bg-white/5 border-transparent hover:border-teal-500' }}">
                    <div class="flex justify-between items-start mb-1 gap-2">
                        <span class="font-bold text-gray-900 dark:text-white text-lg leading-none group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">{{ $order->vehicle->model ?? 'Không rõ' }}</span>
                        <div class="flex items-center gap-2">
                             <span id="badge-moi-{{ $order->id }}" class="badge-moi px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 dark:bg-teal-500/20 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-500/20 uppercase transition-opacity duration-300">Mới</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                        <span class="font-mono bg-gray-100 dark:bg-white/10 px-1.5 rounded text-xs">{{ $order->vehicle->license_plate ?? 'Chưa rõ' }}</span>
                        <span>Chờ kiểm tra</span>
                    </div>
                    <!-- Delete Button inside Card (Hover Reveal) - Moved to top right corner -->
                    <button onclick="event.stopPropagation(); deleteOrder({{ $order->id }})" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-500/10 z-20" title="Xóa xe">
                        <span class="material-icons-round !text-[18px]">delete</span>
                    </button>
                </div>
                @empty
                <div class="p-4 text-center text-sm text-gray-400 italic">Không có xe đang chờ</div>
                @endforelse
            </div>

            <!-- Section: In Progress (In Repair) -->
            <div onclick="toggleSection('in-progress')" class="sticky top-0 z-10 bg-gray-100/95 dark:bg-[#0B1120]/95 backdrop-blur-sm px-4 py-2 border-y border-gray-200 dark:border-[#1e293b] flex justify-between items-center mt-2 cursor-pointer select-none">
                <h3 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-2">
                    <span id="icon-in-progress" class="material-icons-round !text-[16px] transition-transform duration-300">expand_more</span>
                    <span class="material-icons-round !text-[14px]">build</span>
                    Đang Sửa Chữa
                </h3>
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $inProgress->count() }}</span>
            </div>
            <div id="list-in-progress" class="divide-y divide-gray-200 dark:divide-[#1e293b]/50 transition-all duration-300 origin-top">
                @forelse($inProgress as $order)
                <div onclick="loadOrder({{ $order->id }}, this, 'in-progress')" class="order-item p-4 {{ isset($selectedOrder) && $selectedOrder->id == $order->id ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500' : 'hover:bg-gray-100 dark:hover:bg-white/5 border-transparent' }} border-l-4 cursor-pointer relative overflow-hidden transition-colors">
                    <div class="watermark-icon absolute top-0 right-0 p-1 opacity-10 pointer-events-none {{ isset($selectedOrder) && $selectedOrder->id == $order->id ? '' : 'hidden' }}">
                        <span class="material-icons-round text-indigo-500 !text-[64px] -rotate-12 translate-x-2 -translate-y-2">directions_car</span>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-gray-900 dark:text-white text-lg leading-none">{{ $order->vehicle->model ?? 'Không rõ' }}</span>
                            <span class="font-mono text-xs text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-500/20 px-1.5 py-0.5 rounded font-bold">{{ $order->vehicle->license_plate ?? 'Chưa rõ' }}</span>
                        </div>
                        @if(isset($selectedOrder) && $selectedOrder->id == $order->id)
                        <div class="flex items-center gap-2 mt-2">
                            <div class="h-1.5 flex-1 bg-gray-200 dark:bg-[#1e293b] rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 w-[{{ $order->progress_percent ?? 5 }}%]"></div>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $order->progress_percent ?? 5 }}%</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <span class="material-icons-round !text-[14px]">engineering</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">KT: {{ $order->mechanics_display }}</span>
                        </div>
                        @else
                        <div class="mt-2">
                             <div class="flex items-center gap-2">
                                <div class="h-1.5 flex-1 bg-gray-200 dark:bg-[#1e293b] rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $order->progress_percent ?? 5 }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $order->progress_percent ?? 5 }}%</span>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1">
                                    <span class="material-icons-round !text-[12px]">engineering</span>
                                    {{ $order->mechanics_display }}
                                </span>
                                <span class="text-[10px] text-gray-400">{{ $order->advisor->name ?? 'Advisor' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Completed Tab -->
        <div class="border-b border-gray-200 dark:border-[#1e293b] bg-gray-50 dark:bg-[#020617]/50">
            <div class="px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" onclick="toggleSection('list-ready', this)">
                <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <span class="material-icons-round !text-[14px]">done_all</span>
                    <span class="tracking-wider uppercase">HOÀN THÀNH / CHỜ GIAO</span>
                </h3>
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $ready->count() }}</span>
            </div>
            <div id="list-ready" class="divide-y divide-gray-200 dark:divide-[#1e293b]/50 transition-all duration-300 origin-top">
                @foreach($ready as $order)
                <div onclick="loadOrder({{ $order->id }}, this, 'ready')" class="order-item p-4 {{ isset($selectedOrder) && $selectedOrder->id == $order->id ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500' : 'hover:bg-gray-100 dark:hover:bg-white/5 border-transparent' }} border-l-4 cursor-pointer relative overflow-hidden transition-colors">
                    <div class="watermark-icon absolute top-0 right-0 p-1 opacity-10 pointer-events-none {{ isset($selectedOrder) && $selectedOrder->id == $order->id ? '' : 'hidden' }}">
                        <span class="material-icons-round text-indigo-500 !text-[64px] -rotate-12 translate-x-2 -translate-y-2">directions_car</span>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-gray-900 dark:text-white text-lg leading-none">{{ $order->vehicle->model ?? 'Unknown' }}</span>
                            <span class="font-mono text-xs text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-500/20 px-1.5 py-0.5 rounded font-bold">{{ $order->vehicle->license_plate ?? 'N/A' }}</span>
                        </div>
                        <div class="mt-2 text-xs text-green-600 dark:text-green-400 font-bold flex items-center gap-1">
                            <span class="material-icons-round !text-[14px]">check_circle</span>
                            <span>Đã hoàn tất</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </aside>

    <!-- Collapse Button (Mid-left) -->
    <div class="relative z-20 h-full w-0">
        <button onclick="toggleLeftPanel()" class="absolute -left-3 top-[30%] w-6 h-12 bg-white dark:bg-[#0B1120] border border-gray-200 dark:border-[#1e293b] rounded-r-lg flex items-center justify-center cursor-pointer shadow-md hover:text-indigo-600 dark:hover:text-indigo-400 text-gray-400 dark:text-gray-500 transition-all group">
            <span id="leftPanelIcon" class="material-icons-round !text-[16px] transition-transform duration-300 group-hover:scale-110">chevron_left</span>
        </button>
    </div>

    <main id="order-details-container" class="flex-1 flex flex-col bg-white dark:bg-[#020617] relative overflow-y-auto">
        @php
            if(!isset($selectedOrder)) {
                 $selectedOrder = $inProgress->first() ?? $waiting->first() ?? $ready->first();
            }
            if(isset($selectedOrder)) {
                 $currentTasks = $allTasks->where('repair_order_id', $selectedOrder->id);
            }
        @endphp

        @include('staff.partials.order_details', ['selectedOrder' => $selectedOrder, 'currentTasks' => $currentTasks ?? collect([])])
    </main>
</div>
@endsection

@push('scripts')
<script>
    const AUTH_USER_ID = {{ auth()->id() ?? 'null' }};

    function getCurrentOrderId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('order_id');
    }

    // Function to handle "Mới" badges locally
    document.addEventListener('DOMContentLoaded', () => {
        // Hide badges that were previously clicked
        const viewedOrders = JSON.parse(localStorage.getItem('viewedOrders') || '[]');
        viewedOrders.forEach(id => {
            const badge = document.getElementById(`badge-moi-${id}`);
            if (badge) badge.style.display = 'none';
        });
    });

    function markAsViewed(orderId, element) {
        // Instantly hide the badge using the element reference
        if (element) {
            const badge = element.querySelector('.badge-moi');
            if (badge) badge.style.display = 'none';
        } else {
            const badge = document.getElementById(`badge-moi-${orderId}`);
            if (badge) badge.style.display = 'none';
        }
        
        // Save to localStorage so it stays hidden on refresh
        let viewedOrders = JSON.parse(localStorage.getItem('viewedOrders') || '[]');
        if (!viewedOrders.includes(orderId)) {
            viewedOrders.push(orderId);
            localStorage.setItem('viewedOrders', JSON.stringify(viewedOrders));
        }
    }

    // Toggle Section Logic
    function toggleSection(id) {
        const list = document.getElementById(`list-${id}`);
        const icon = document.getElementById(`icon-${id}`);
        
        if (list.classList.contains('hidden')) {
            list.classList.remove('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            list.classList.add('hidden');
            icon.style.transform = 'rotate(-90deg)';
        }
    }

    function toggleLeftPanel() {
        const panel = document.getElementById('leftPanel');
        const icon = document.getElementById('leftPanelIcon');
        
        if (panel.classList.contains('w-[400px]')) {
            // Close
            panel.classList.remove('w-[400px]');
            panel.classList.add('w-0', 'overflow-hidden', 'border-none');
            icon.innerHTML = 'chevron_right';
            icon.parentElement.style.left = '0px'; 
        } else {
            // Open
            panel.classList.add('w-[400px]');
            panel.classList.remove('w-0', 'overflow-hidden', 'border-none');
            icon.innerHTML = 'chevron_left';
            icon.parentElement.style.left = '-12px'; 
        }
    }

    async function loadOrder(orderId, element, type = 'waitlist') {
        // Highlight logic
        document.querySelectorAll('.order-item').forEach(el => {
            el.classList.remove('bg-indigo-50', 'dark:bg-white/10', 'border-indigo-500', 'dark:bg-indigo-500/10');
            el.classList.add('border-transparent');
            
            const watermark = el.querySelector('.watermark-icon');
            if(watermark) watermark.classList.add('hidden');
        });
        
        // Add highlight to clicked
        if (element) {
            element.classList.remove('border-transparent');
            if (type === 'in-progress') {
                element.classList.add('bg-indigo-50', 'dark:bg-indigo-500/10', 'border-indigo-500');
                const watermark = element.querySelector('.watermark-icon');
                if(watermark) watermark.classList.remove('hidden');
            } else {
                // Waitlist
                element.classList.add('bg-indigo-50', 'dark:bg-white/10', 'border-indigo-500');
            }
        }

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('order_id', orderId);
        window.history.pushState({}, '', url);

        // Fetch Data
        try {
            const response = await fetch(`${window.location.pathname}?order_id=${orderId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await response.text();
            document.getElementById('order-details-container').innerHTML = html;
        } catch (error) {
            console.error('Error loading order:', error);
        }
    }

    // Toggle Task Status (Main Dashboard Version)
    function toggleTask(taskId, currentStatus) {
        // This function handles main checkboxes.
        // If called from the Modal context, we might want to skip confirmation or distinct logic?
        // For now, let's keep the confirmation as it's safer.
        
        // Check if event exists (sometimes called programmatically)
        if(window.event) window.event.preventDefault(); 

        const isComplete = currentStatus === 'completed';
        const actionText = isComplete ? "đánh dấu chưa làm" : "hoàn thành";
        const confirmBtnColor = isComplete ? "#d33" : "#4f46e5";

        Swal.fire({
            title: 'Xác nhận?',
            text: `Bạn có chắc muốn ${actionText} công việc này?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Đúng, thực hiện!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/task/${taskId}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Trạng thái công việc đã được cập nhật.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        const currentOrderId = getCurrentOrderId();
                        if (currentOrderId) loadOrder(currentOrderId, null);
                        else location.reload(); 
                    } else {
                        Swal.fire('Không thể hoàn thành', data.message || 'Lỗi cập nhật', 'warning');
                    }
                })
                .catch(err => Swal.fire('Lỗi', 'Lỗi hệ thống.', 'error'));
            }
        });
    }

    // --- Task Detail Modal & Actions (Moved from Partial) ---

    function requestSupport() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Yêu cầu hỗ trợ',
            input: 'textarea',
            inputLabel: 'Nội dung yêu cầu',
            inputPlaceholder: 'Nhập nội dung cần hỗ trợ...',
            inputAttributes: {
                'aria-label': 'Nội dung yêu cầu'
            },
            showCancelButton: true,
            confirmButtonText: 'Gửi yêu cầu'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch(`/staff/order/${orderId}/request-support`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ content: result.value })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        Swal.fire('Đã gửi!', 'Yêu cầu của bạn đã được gửi đến Admin.', 'success');
                        loadOrder(orderId);
                    }
                });
            }
        });
    }

    function openTaskDetails(id) {
        fetch(`/task/${id}`)
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;
            const task = data.task;
            const isAssignedToMe = task.mechanic_id == AUTH_USER_ID;
            const isAssigned = !!task.mechanic_id;
            
            let subtasksHtml = '';
            task.children.forEach(child => {
                // Determine if checked
                const checked = child.status === 'completed' ? 'checked' : '';
                const titleStyle = child.status === 'completed' ? 'line-through text-gray-400' : '';
                
                // Note: using onclick="event.stopPropagation(); toggleTask(...)" might trigger confirmation inside modal.
                // If we want a simple toggle inside modal without confetti/reload loop issues, we need to handle it gracefully.
                // For now, let's allow the standard toggle.
                subtasksHtml += `
                    <div class="flex items-center gap-2 p-2 border-b border-gray-100 dark:border-gray-700">
                        <input type="checkbox" ${checked} onclick="toggleTask(${child.id}, '${child.status}');" class="rounded text-indigo-600 cursor-pointer">
                        <span class="${titleStyle}">${child.title}</span>
                    </div>
                `;
            });

            Swal.fire({
                title: 'Chi tiết công việc',
                html: `
                    <div class="text-left space-y-4">
                        <!-- Title & Status -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="modal-task-status" ${task.status == 'completed' ? 'checked' : ''} class="mt-1.5 w-5 h-5 rounded text-indigo-600">
                            <div class="flex-1">
                                <input id="modal-task-title" class="w-full font-bold text-lg bg-transparent border-b border-gray-200 dark:border-gray-700 focus:border-indigo-500 outline-none" value="${task.title}">
                            </div>
                        </div>

                        <!-- Assignment -->
                        <div class="flex items-center gap-2 text-sm bg-gray-50 dark:bg-gray-800 p-2 rounded">
                            <span class="material-icons-round text-gray-400 text-sm">person</span>
                            <span class="font-bold text-gray-600 dark:text-gray-300">Người thực hiện:</span>
                            ${isAssigned 
                                ? `<span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">${task.mechanic ? task.mechanic.name : 'Unknown'}</span>` 
                                : '<span class="italic text-gray-400">Chưa có</span>'}
                            
                            <div class="ml-auto">
                                ${!isAssigned 
                                    ? `<button onclick="assignTask(${task.id}); Swal.close();" class="text-xs bg-indigo-600 text-white px-2 py-1 rounded">Nhận việc</button>` 
                                    : (isAssignedToMe 
                                        ? `<button onclick="unassignTask(${task.id}); Swal.close();" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded">Hủy nhận</button>` 
                                        : '')}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Mô tả / Ghi chú</label>
                            <textarea id="modal-task-note" class="w-full mt-1 p-2 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 focus:ring-1 focus:ring-indigo-500 outline-none" rows="3">${task.note || ''}</textarea>
                        </div>

                        <!-- Subtasks -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-gray-500 uppercase">Việc cần làm</label>
                                <button onclick="addTask(${task.id}); Swal.close();" class="text-xs text-indigo-600 font-bold hover:underline">+ Thêm</button>
                            </div>
                            <div class="max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                                ${subtasksHtml || '<p class="text-center text-xs text-gray-400 p-2">Không có việc con</p>'}
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                            <button onclick="deleteTask(${task.id})" class="text-red-500 text-sm flex items-center gap-1 hover:text-red-700"><span class="material-icons-round text-sm">delete</span> Xóa việc này</button>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Lưu thay đổi',
                cancelButtonText: 'Đóng',
                width: '600px',
                preConfirm: () => {
                    return {
                        title: document.getElementById('modal-task-title').value,
                        note: document.getElementById('modal-task-note').value,
                        status: document.getElementById('modal-task-status').checked ? 'completed' : 'pending'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update Details
                    fetch(`/task/${id}/details`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            title: result.value.title,
                            note: result.value.note
                        })
                    }).then(() => {
                        // Check if status changed
                        if (task.status !== result.value.status) {
                                toggleTask(id, task.status); 
                        } else {
                            const orderId = getCurrentOrderId();
                            if(orderId) loadOrder(orderId);
                            Swal.fire('Đã lưu', 'Thông tin nhiệm vụ đã được cập nhật.', 'success');
                        }
                    });
                }
            });
        });
    }

    function deleteTask(id) {
        Swal.fire({
            title: 'Xóa nhiệm vụ?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Xóa ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/task/${id}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Đã xóa', 'Nhiệm vụ đã bị xóa.', 'success');
                        const orderId = getCurrentOrderId();
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    // Delete Order function
    function deleteOrder(id) {
        Swal.fire({
            title: 'Xác nhận xóa xe?',
            text: "Hành động này sẽ xóa hoàn toàn thông tin xe khỏi hàng chờ. Không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${id}/delete`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        // Fallback handling if route is missing
                        if (res.status === 404) throw new Error('Route not found - Please implement DELETE in StaffController');
                        throw new Error('Server error');
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công', 'Đã xóa xe khỏi danh sách chờ.', 'success').then(() => {
                            window.location.href = '{{ route("staff.dashboard") }}';
                        });
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể xóa.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi', 'Không thể xóa! Vui lòng đảm bảo tính năng Xóa Đơn Hàng đã được tạo ở Controller.', 'error');
                });
            }
        });
    }

    function assignTask(id) {
        Swal.fire({
            title: 'Xác nhận nhận việc?',
            text: "Bạn sẽ được giao nhiệm vụ này.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5b4bda',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Nhận việc'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/task/${id}/assign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', 'Bạn đã nhận nhiệm vụ.', 'success');
                        const orderId = getCurrentOrderId();
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    function unassignTask(id) {
        Swal.fire({
            title: 'Xác nhận hủy nhận việc?',
            text: "Nhiệm vụ này sẽ không còn được giao cho bạn.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hủy nhận'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/task/${id}/unassign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', 'Bạn đã hủy nhận nhiệm vụ.', 'success');
                        const orderId = getCurrentOrderId();
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    function addTask(parentId) {
        const orderId = getCurrentOrderId();
        
        Swal.fire({
            title: parentId ? 'Thêm nhiệm vụ con' : 'Thêm công việc mới',
            input: 'text',
            inputLabel: 'Tên công việc',
            inputPlaceholder: 'Nhập tên công việc...',
            showCancelButton: true,
            confirmButtonText: 'Thêm',
            cancelButtonText: 'Hủy',
            showLoaderOnConfirm: true,
            preConfirm: (title) => {
                if (!title) {
                    Swal.showValidationMessage('Vui lòng nhập tên công việc');
                    return false;
                }
                return fetch(`/staff/order/${orderId}/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        title: title,
                        parent_id: parentId,
                        type: 'adhoc'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`)
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Thành công!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                if(orderId) loadOrder(orderId);
            }
        })
    }

    // Add Vehicle Modal Logic (Existing)
    function abandonOrder() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Khách đổi ý / Bỏ xe?',
            text: "Để xác nhận, vui lòng nhập 'Xác nhận hủy xe' vào ô bên dưới:",
            input: 'text',
            inputPlaceholder: 'Xác nhận hủy xe',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Hủy đơn',
            cancelButtonText: 'Đóng',
            preConfirm: (inputValue) => {
                if (inputValue !== 'Xác nhận hủy xe') {
                    Swal.showValidationMessage('Bạn phải nhập đúng cụm từ "Xác nhận hủy xe"');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'cancelled' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Đã hủy!', 'Đơn sửa chữa đã được chuyển vào danh sách hủy.', 'success')
                        .then(() => {
                            window.location.href = `{{ route('staff.dashboard') }}`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    function cancelRepair() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Hủy nhận sửa?',
            text: "Xe sẽ quay lại 'Danh sách chờ' và loại bỏ phân công hiện tại.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Xác nhận hủy',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'pending' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!', 
                            text: 'Đã đưa xe về Danh sách chờ.', 
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = `{{ route('staff.dashboard') }}?order_id=${orderId}`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    function startRepair() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Xác nhận tiếp nhận?',
            text: "Xe sẽ được chuyển sang trạng thái 'Đang xử lý'.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5b4bda',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Tiếp nhận ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'in_progress' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!', 
                            text: 'Đã tiếp nhận xe vào sửa chữa.', 
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to move vehicle from Waitlist to In Progress
                            window.location.href = `{{ route('staff.dashboard') }}?order_id=${orderId}`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    function completeOrder() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Hoàn thành Đơn sửa chữa?',
            text: "Xác nhận xe đã đủ tiêu chuẩn xuất xưởng và chuyển sang trạng thái chờ giao xe/thanh toán.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#10b981', // green-500
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Xác nhận Hoàn Thành',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'completed' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Đơn sửa chữa đã được hoàn thành.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = `{{ route('staff.dashboard') }}?order_id=${orderId}`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    function addNote() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Thêm ghi chú',
            input: 'textarea',
            inputPlaceholder: 'Nhập nội dung ghi chú...',
            showCancelButton: true,
            confirmButtonText: 'Lưu',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch(`/staff/order/${orderId}/add-note`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ note: result.value })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        loadOrder(orderId);
                    }
                });
            }
        });
    }

    function storeQuickItem() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Thêm vật tư nhanh',
            html: `
                <input id="swal-item-name" class="swal2-input" placeholder="Tên vật tư / linh kiện">
                <input id="swal-item-qty" type="number" class="swal2-input" placeholder="Số lượng" value="1">
            `,
            showCancelButton: true,
            confirmButtonText: 'Thêm',
            preConfirm: () => {
                return {
                    name: document.getElementById('swal-item-name').value,
                    quantity: document.getElementById('swal-item-qty').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/order/${orderId}/quick-item`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(result.value)
                }).then(res => res.json()).then(data => {
                    if(data.success) loadOrder(orderId);
                });
            }
        });
    }



    function requestSupport() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: 'Yêu cầu hỗ trợ',
            input: 'text',
            inputLabel: 'Nội dung (Ví dụ: Xin nhập lốp ngoài...)',
            showCancelButton: true,
            confirmButtonText: 'Gửi yêu cầu'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch(`/staff/order/${orderId}/request-support`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ content: result.value })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        Swal.fire('Đã gửi!', 'Yêu cầu của bạn đã được gửi đến Admin.', 'success');
                        loadOrder(orderId);
                    }
                });
            }
        });
    }

    function openTaskDetails(id) {
        const orderId = getCurrentOrderId();
        fetch(`/staff/task/${id}`)
        .then(res => res.json())
        .then(data => {
            if(!data.success) {
                Swal.fire('Lỗi', data.message || 'Không thể tải chi tiết công việc.', 'error');
                return;
            }
            const task = data.task;
            const isAssignedToMe = task.mechanic_id == AUTH_USER_ID;
            const isAssigned = !!task.mechanic_id;
            
            let subtasksHtml = '';
            task.children.forEach(child => {
                subtasksHtml += `
                    <div class="group flex items-center justify-between p-2 border-b border-gray-100 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors rounded-lg mb-1">
                        <div class="flex items-center gap-2">
                             <input type="checkbox" ${child.status == 'completed' ? 'checked' : ''} onclick="toggleTask(${child.id}, '${child.status}'); this.closest('.swal2-container').querySelector('#refresh-btn').click()" class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer accent-indigo-600 w-4 h-4">
                             <span class="${child.status == 'completed' ? 'line-through text-gray-400 dark:text-gray-600' : 'text-slate-700 dark:text-slate-300'} text-sm font-medium transition-colors select-none cursor-pointer" onclick="this.previousElementSibling.click()">${child.title}</span>
                        </div>
                        <button onclick="deleteTask(${child.id})" class="text-gray-400 hover:text-red-500 dark:text-slate-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 transform active:scale-95" title="Xóa">
                            <span class="material-icons-round text-lg">delete_outline</span>
                        </button>
                    </div>
                `;
            });

            Swal.fire({
                title: '',
                html: `
                    <div class="text-left font-['Plus_Jakarta_Sans',sans-serif]">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 ring-1 ring-indigo-100 dark:ring-indigo-700/50">
                                    <span class="material-icons-round text-xl">task_alt</span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide">Chi tiết công việc</h3>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">ID: #${task.id}</span>
                                </div>
                            </div>
                            <!-- Status Toggle -->
                            <div class="flex items-center gap-2 bg-gray-100/80 dark:bg-slate-800 rounded-full px-3 py-1 cursor-pointer transition-colors hover:bg-gray-200 dark:hover:bg-slate-700" onclick="document.getElementById('modal-task-status').click()">
                                <input type="checkbox" id="modal-task-status" ${task.status == 'completed' ? 'checked' : ''} class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer accent-indigo-600">
                                <span class="text-xs font-bold ${task.status == 'completed' ? 'text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300'} select-none" id="modal-status-text">${task.status == 'completed' ? 'Đã hoàn thành' : 'Đang thực hiện'}</span>
                            </div>
                        </div>

                        <!-- Title Input -->
                        <div class="mb-6 group">
                            <label class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase mb-1 group-focus-within:text-indigo-500 dark:group-focus-within:text-indigo-400 transition-colors">Tên công việc</label>
                            <input id="modal-task-title" class="w-full text-xl font-bold bg-transparent border-b-2 border-gray-100 dark:border-slate-800 focus:border-indigo-500 dark:focus:border-indigo-500 outline-none text-slate-900 dark:text-white py-2 transition-all placeholder-gray-300 dark:placeholder-slate-600" value="${task.title}" placeholder="Nhập tên công việc...">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Assignment Section -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase mb-2">Người thực hiện</label>
                                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-900/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full ${isAssigned ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-gray-200 dark:bg-slate-800 text-gray-500 dark:text-slate-400'} flex items-center justify-center font-bold text-xs ring-2 ring-white dark:ring-slate-950">
                                            ${isAssigned && task.mechanic ? task.mechanic.name.charAt(0) : '?'}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                                ${isAssigned ? (task.mechanic ? task.mechanic.name : 'Unknown') : 'Chưa giao việc'}
                                            </div>
                                            <div class="text-[10px] text-gray-500 dark:text-slate-400">
                                                ${isAssigned ? 'Kỹ thuật viên' : 'Sẵn sàng nhận'}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    ${!isAssigned 
                                        ? `<button onclick="assignTask(${task.id}); Swal.close();" class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg shadow-lg shadow-indigo-500/20 dark:shadow-none transition-all transform active:scale-95">Nhận việc</button>` 
                                        : (isAssignedToMe 
                                            ? `<button onclick="unassignTask(${task.id}); Swal.close();" class="text-xs font-bold bg-white dark:bg-slate-800 text-red-500 dark:text-red-400 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">Hủy</button>` 
                                            : '')}
                                </div>
                            </div>

                             <!-- Note Section -->
                             <div>
                                <label class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase mb-2">Ghi chú</label>
                                <div class="relative">
                                    <textarea id="modal-task-note" class="w-full text-sm bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-800 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-500 outline-none text-slate-700 dark:text-slate-200 resize-none transition-all placeholder-gray-400 dark:placeholder-slate-600" rows="3" placeholder="Thêm ghi chú...">${task.note || ''}</textarea>
                                    <div class="absolute bottom-2 right-2 text-gray-400 dark:text-slate-600 pointer-events-none">
                                        <span class="material-icons-round text-sm">edit_note</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subtasks Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-end mb-3">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase">Việc cần làm</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-slate-500">Tiến độ chi tiết</p>
                                </div>
                                <button onclick="addTask(${task.id}); Swal.close();" class="text-xs flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                    <span class="material-icons-round text-sm">add</span> Thêm việc
                                </button>
                            </div>
                            <div class="bg-gray-50 dark:bg-slate-900/30 border border-gray-100 dark:border-slate-800/50 rounded-xl overflow-hidden">
                                <div class="max-h-[200px] overflow-y-auto custom-scrollbar">
                                    ${subtasksHtml || `
                                        <div class="flex flex-col items-center justify-center p-6 text-gray-400 dark:text-gray-600">
                                            <span class="material-icons-round text-3xl mb-1 text-gray-300 dark:text-gray-700">checklist</span>
                                            <p class="text-xs">Chưa có công việc con nào</p>
                                        </div>
                                    `}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer Actions -->
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button onclick="deleteTask(${task.id})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs font-bold flex items-center gap-1 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                <span class="material-icons-round text-sm">delete_outline</span> Xóa việc này
                            </button>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 italic">Thay đổi được lưu tự động khi ấn 'Lưu'</span>
                        </div>
                        
                        <!-- HIDDEN REFRESH BUTTON Triggered by subtask toggle -->
                        <button id="refresh-btn" class="hidden" onclick="loadOrder(${orderId})"></button>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Lưu thay đổi',
                cancelButtonText: 'Đóng',
                confirmButtonColor: '#4f46e5', // Indigo-600
                cancelButtonColor: '#9ca3af',   // Gray-400
                width: '650px',
                padding: '0',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-gray-100 dark:border-slate-800 p-0 overflow-hidden bg-white dark:bg-slate-950',
                    htmlContainer: '!mx-0 !mt-0 !p-6 !text-left text-slate-900 dark:text-slate-200',
                    actions: 'bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 !m-0 !py-4 !w-full !justify-end !pr-6 gap-3',
                    confirmButton: '!rounded-xl !px-6 !py-3 !text-sm !font-bold !shadow-md !shadow-indigo-500/20 dark:!shadow-none !bg-indigo-600 hover:!bg-indigo-700',
                    cancelButton: '!rounded-xl !px-5 !py-3 !text-sm !font-bold !bg-white dark:!bg-transparent !text-slate-600 dark:!text-slate-400 !border !border-gray-200 dark:!border-slate-700 hover:!bg-gray-50 dark:hover:!bg-white/5'
                },
                didOpen: () => {
                    // Updates status text when checkbox changes
                    const checkbox = document.getElementById('modal-task-status');
                    const textSpan = document.getElementById('modal-status-text');
                    checkbox.addEventListener('change', (e) => {
                        textSpan.textContent = e.target.checked ? 'Đã hoàn thành' : 'Đang thực hiện';
                        textSpan.className = `text-xs font-bold select-none ${e.target.checked ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300'}`;
                    });
                },
                preConfirm: () => {
                    return {
                        title: document.getElementById('modal-task-title').value,
                        note: document.getElementById('modal-task-note').value,
                        status: document.getElementById('modal-task-status').checked ? 'completed' : 'pending'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update Details
                    fetch(`/staff/task/${id}/details`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            title: result.value.title,
                            note: result.value.note
                        })
                    }).then(() => {
                        // Check if status changed
                        if (task.status !== result.value.status) {
                             toggleTask(id); // Use existing toggle logic
                        } else {
                            if(orderId) loadOrder(orderId);
                            Swal.fire('Đã lưu', 'Thông tin nhiệm vụ đã được cập nhật.', 'success');
                        }
                    });
                }
            });
        });
    }

    function deleteTask(id) {
        const orderId = getCurrentOrderId();
        Swal.fire({
            title: 'Xóa nhiệm vụ?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Xóa ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/task/${id}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Đã xóa', 'Nhiệm vụ đã bị xóa.', 'success');
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    function assignTask(id) {
        const orderId = getCurrentOrderId();
        Swal.fire({
            title: 'Xác nhận nhận việc?',
            text: "Bạn sẽ được giao nhiệm vụ này.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5b4bda',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Nhận việc'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/task/${id}/assign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', 'Bạn đã nhận nhiệm vụ.', 'success');
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    function unassignTask(id) {
        const orderId = getCurrentOrderId();
        Swal.fire({
            title: 'Xác nhận hủy nhận việc?',
            text: "Nhiệm vụ này sẽ không còn được giao cho bạn.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hủy nhận'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/task/${id}/unassign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', 'Bạn đã hủy nhận nhiệm vụ.', 'success');
                        if(orderId) loadOrder(orderId);
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                });
            }
        });
    }

    function toggleTask(id) {
        const orderId = getCurrentOrderId();
        fetch(`/staff/task/${id}/toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                if(orderId) loadOrder(orderId);
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        });
    }

    function addTask(parentId) {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        Swal.fire({
            title: parentId ? 'Thêm nhiệm vụ con' : 'Thêm công việc mới',
            input: 'text',
            inputLabel: 'Tên công việc',
            inputPlaceholder: 'Nhập tên công việc...',
            showCancelButton: true,
            confirmButtonText: 'Thêm',
            cancelButtonText: 'Hủy',
            showLoaderOnConfirm: true,
            preConfirm: (title) => {
                if (!title) {
                    Swal.showValidationMessage('Vui lòng nhập tên công việc');
                    return false;
                }
                return fetch(`/staff/order/${orderId}/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        title: title,
                        parent_id: parentId,
                        type: 'adhoc'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`)
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Thành công!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadOrder(orderId);
            }
        })
    }

    function openAddVehicleModal() {
        const quickVal = document.getElementById('quickInput').value.trim().toUpperCase() || '';
        const isDark = document.documentElement.classList.contains('dark');
        
        // Logic check: Plate vs VIN
        let detectedType = '';
        let detectedLabel = '';
        const plateRegex = /^[0-9]{2}[A-Z]/;
        
        if (quickVal) {
            if (plateRegex.test(quickVal)) {
                detectedType = 'plate';
                detectedLabel = '<span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 uppercase tracking-wide">Phát hiện Biển số</span>';
            } else if (quickVal.length === 17) {
                detectedType = 'vin';
                detectedLabel = '<span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 uppercase tracking-wide">Phát hiện số VIN</span>';
            }
        }

        Swal.fire({
            title: '', 
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#ffffff' : '#111827',
            html: `
                <div class="text-left font-['Plus_Jakarta_Sans',sans-serif]">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-icons-round text-[#5b4bda]">add_circle</span>
                                Tiếp nhận xe mới
                            </h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Nhập thông tin chi tiết để bắt đầu quy trình sửa chữa và bảo dưỡng.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 max-h-[65vh] overflow-y-auto pr-2 custom-scrollbar">
                        <!-- Vehicle Info -->
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-[#5b4bda]/10 p-1.5 rounded-lg">
                                    <span class="material-icons-round text-[#5b4bda] text-sm">directions_car</span>
                                </span>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Thông tin xe</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" for="license">Biển số xe / VIN ${detectedLabel}</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-round text-gray-400 text-[20px]">pin</span>
                                        </div>
                                        <input class="block w-full pl-10 pr-3 py-2.5 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#5b4bda]/20 focus:border-[#5b4bda] transition-all font-semibold tracking-wide placeholder:text-gray-400 dark:placeholder:text-gray-600 uppercase" id="swal-plate" type="text" value="${quickVal}" placeholder="29A-123.45"/>
                                    </div>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" for="model">Hãng & Mẫu xe</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-round text-gray-400 text-[20px]">directions_car</span>
                                        </div>
                                        <input class="block w-full pl-10 pr-3 py-2.5 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#5b4bda]/20 focus:border-[#5b4bda] transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600" id="swal-model" placeholder="Ví dụ: Toyota Vios" type="text"/>
                                    </div>
                                </div>
                            </div>
                            <!-- Vehicle Type Selection -->
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Loại xe</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div class="relative cursor-pointer group">
                                        <input class="peer sr-only" id="type_sedan" name="vehicle_type" type="radio" value="sedan" checked/>
                                        <label class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 transition-all hover:border-[#5b4bda] hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-[#5b4bda] peer-checked:bg-[#5b4bda]/5 peer-checked:text-[#5b4bda] h-full text-gray-500 dark:text-gray-400" for="type_sedan">
                                            <span class="material-icons-round text-4xl mb-2 group-hover:text-[#5b4bda] transition-colors">directions_car</span>
                                            <span class="text-sm font-semibold group-hover:text-[#5b4bda] transition-colors">Sedan</span>
                                        </label>
                                    </div>
                                    <div class="relative cursor-pointer group">
                                        <input class="peer sr-only" id="type_suv" name="vehicle_type" type="radio" value="suv"/>
                                        <label class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 transition-all hover:border-[#5b4bda] hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-[#5b4bda] peer-checked:bg-[#5b4bda]/5 peer-checked:text-[#5b4bda] h-full text-gray-500 dark:text-gray-400" for="type_suv">
                                            <span class="material-icons-round text-4xl mb-2 group-hover:text-[#5b4bda] transition-colors">airport_shuttle</span>
                                            <span class="text-sm font-medium group-hover:text-[#5b4bda] transition-colors">SUV / CUV</span>
                                        </label>
                                    </div>
                                    <div class="relative cursor-pointer group">
                                        <input class="peer sr-only" id="type_hatch" name="vehicle_type" type="radio" value="hatchback"/>
                                        <label class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 transition-all hover:border-[#5b4bda] hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-[#5b4bda] peer-checked:bg-[#5b4bda]/5 peer-checked:text-[#5b4bda] h-full text-gray-500 dark:text-gray-400" for="type_hatch">
                                            <span class="material-icons-round text-4xl mb-2 group-hover:text-[#5b4bda] transition-colors">time_to_leave</span>
                                            <span class="text-sm font-medium group-hover:text-[#5b4bda] transition-colors">Hatchback</span>
                                        </label>
                                    </div>
                                     <div class="relative cursor-pointer group">
                                        <input class="peer sr-only" id="type_pickup" name="vehicle_type" type="radio" value="pickup"/>
                                        <label class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 transition-all hover:border-[#5b4bda] hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-[#5b4bda] peer-checked:bg-[#5b4bda]/5 peer-checked:text-[#5b4bda] h-full text-gray-500 dark:text-gray-400" for="type_pickup">
                                            <span class="material-icons-round text-4xl mb-2 group-hover:text-[#5b4bda] transition-colors">local_shipping</span>
                                            <span class="text-sm font-medium group-hover:text-[#5b4bda] transition-colors">Bán tải</span>
                                        </label>
                                    </div>
                                    <div class="relative cursor-pointer group">
                                        <input class="peer sr-only" id="type_mpv" name="vehicle_type" type="radio" value="mpv"/>
                                        <label class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 transition-all hover:border-[#5b4bda] hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-[#5b4bda] peer-checked:bg-[#5b4bda]/5 peer-checked:text-[#5b4bda] h-full text-gray-500 dark:text-gray-400" for="type_mpv">
                                            <span class="material-icons-round text-4xl mb-2 group-hover:text-[#5b4bda] transition-colors">airline_seat_recline_normal</span>
                                            <span class="text-sm font-medium group-hover:text-[#5b4bda] transition-colors">MPV 7 chỗ</span>
                                        </label>
                                    </div>
                                </div>
                            <!-- Inspection Options -->
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Lưu ý kiểm tra (Tạo nhiệm vụ ban đầu)</label>
                                <div class="space-y-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" id="chk_inspect_general" checked class="w-5 h-5 rounded border-gray-300 text-[#5b4bda] focus:ring-[#5b4bda] transition-colors bg-white dark:bg-gray-700">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Kiểm tra tổng quát</span>
                                    </label>
                                    <div class="pl-8 flex items-center gap-2">
                                        <span class="text-xs text-gray-500">Tùy chọn:</span>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="chk_inspect_3d" checked class="w-4 h-4 rounded border-gray-300 text-indigo-500 focus:ring-indigo-500 transition-colors">
                                            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Sử dụng 3D Visualizer</span>
                                        </label>
                                    </div>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" id="chk_inspect_cabin" checked class="w-5 h-5 rounded border-gray-300 text-[#5b4bda] focus:ring-[#5b4bda] transition-colors bg-white dark:bg-gray-700">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Kiểm tra bên trong khoang lái</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" id="chk_inspect_engine" checked class="w-5 h-5 rounded border-gray-300 text-[#5b4bda] focus:ring-[#5b4bda] transition-colors bg-white dark:bg-gray-700">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Kiểm tra động cơ</span>
                                    </label>
                                </div>
                            </div>
                            <style>
                                .custom-scrollbar::-webkit-scrollbar { display: none; }
                                .custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                            </style>
                        </section>

                        <hr class="border-gray-100 dark:border-gray-700"/>

                        <!-- Customer Info -->
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-500/10 p-1.5 rounded-lg">
                                    <span class="material-icons-round text-blue-500 text-sm">person</span>
                                </span>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Thông tin khách hàng</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" for="phone">Số điện thoại <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-round text-gray-400 text-[20px]">call</span>
                                        </div>
                                        <input class="block w-full pl-10 pr-3 py-2.5 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#5b4bda]/20 focus:border-[#5b4bda] transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600 font-bold" id="swal-phone" placeholder="Nhập SĐT (10 số)..." type="tel" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
                                        <div id="phone-loader" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                            <span class="material-icons-round animate-spin text-[#5b4bda] text-sm">hourglass_empty</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 italic" id="phone-hint">Nhập SĐT để mở khóa tên khách hàng</p>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" for="cust_name">Tên khách hàng <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-round text-gray-400 text-[20px]">badge</span>
                                        </div>
                                        <input class="block w-full pl-10 pr-3 py-2.5 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#5b4bda]/20 focus:border-[#5b4bda] transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600 disabled:bg-gray-100 dark:disabled:bg-gray-700/50 disabled:text-gray-500" id="swal-name" placeholder="Tên khách hàng" type="text" disabled/>
                                    </div>
                                </div>
                                <div class="group md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" for="email">Email</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-round text-gray-400 text-[20px]">mail</span>
                                        </div>
                                        <input class="block w-full pl-10 pr-3 py-2.5 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#5b4bda]/20 focus:border-[#5b4bda] transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600" id="swal-email" placeholder="email@example.com" type="email"/>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Tiếp nhận ngay',
            cancelButtonText: 'Hủy bỏ',
            confirmButtonColor: '#5b4bda',
            cancelButtonColor: '#9ca3af',
            width: '650px',
            padding: '1.5rem',
            customClass: {
                popup: 'rounded-2xl shadow-2xl overflow-hidden',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg shadow-[#5b4bda]/30',
                cancelButton: 'rounded-xl px-6 py-2.5 font-bold'
            },
            didOpen: () => {
                const phoneInput = document.getElementById('swal-phone');
                const nameInput = document.getElementById('swal-name');
                const loader = document.getElementById('phone-loader');
                const hint = document.getElementById('phone-hint');
                let timeout = null;

                phoneInput.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    const phone = e.target.value;
                    const phoneRegex = /^0\d{9}$/;
                    
                    if (!phoneRegex.test(phone)) {
                        nameInput.disabled = true;
                        nameInput.value = '';
                        nameInput.readOnly = false;
                        if (phone.length === 10 && !phone.startsWith('0')) {
                             hint.innerHTML = '<span class="text-red-500 font-bold">Số điện thoại phải bắt đầu bằng số 0</span>';
                        } else if (phone.length > 0 && phone.length < 10) {
                             hint.innerHTML = `Đang nhập... (${phone.length}/10)`;
                             hint.className = 'text-[10px] text-gray-400 mt-1 italic';
                        } else {
                            hint.innerHTML = 'Nhập SĐT (10 số, bắt đầu bằng 0) để mở khóa';
                            hint.className = 'text-[10px] text-gray-400 mt-1 italic';
                        }
                        return;
                    }

                    hint.innerHTML = `Đang kiểm tra... (10/10)`;
                    hint.className = 'text-[10px] text-indigo-500 mt-1 italic font-medium';
                    timeout = setTimeout(() => {
                        loader.classList.remove('hidden');
                        fetch(`{{ route('staff.customers.check') }}?phone=${phone}`)
                        .then(res => res.json())
                        .then(data => {
                            loader.classList.add('hidden');
                            nameInput.disabled = false;
                            
                            if (data.exists) {
                                nameInput.value = data.name;
                                nameInput.readOnly = true; 
                                hint.innerHTML = `<span class="text-green-600 font-bold flex items-center gap-1"><span class="material-icons-round text-[12px]">check_circle</span> Khách hàng cũ: ${data.name}</span>`;
                            } else {
                                nameInput.value = '';
                                nameInput.readOnly = false;
                                nameInput.focus();
                                hint.innerHTML = `<span class="text-indigo-600 font-bold flex items-center gap-1"><span class="material-icons-round text-[12px]">person_add</span> Khách hàng mới - Vui lòng nhập tên</span>`;
                            }
                        })
                        .catch(() => {
                            loader.classList.add('hidden');
                            nameInput.disabled = false;
                        });
                    }, 500); 
                });
            },
            preConfirm: () => {
                const license_plate = document.getElementById('swal-plate').value;
                const owner_name = document.getElementById('swal-name').value;
                const owner_phone = document.getElementById('swal-phone').value;
                const model = document.getElementById('swal-model').value;
                const typeEl = document.querySelector('input[name="vehicle_type"]:checked');
                const type = typeEl ? typeEl.value : 'sedan';
                const phoneRegex = /^0\d{9}$/;

                if (!license_plate || !owner_name || !owner_phone || !model) {
                    Swal.showValidationMessage('Vui lòng điền đầy đủ thông tin bắt buộc!');
                    return false;
                }
                
                if (!phoneRegex.test(owner_phone)) {
                    Swal.showValidationMessage('Số điện thoại không hợp lệ (Phải có 10 số và bắt đầu bằng 0)');
                    return false;
                }

                // Collect inspection tasks
                const inspectionOptions = {
                    general: document.getElementById('chk_inspect_general').checked,
                    use_3d: document.getElementById('chk_inspect_3d').checked,
                    cabin: document.getElementById('chk_inspect_cabin').checked,
                    engine: document.getElementById('chk_inspect_engine').checked,
                };

                return { license_plate, owner_name, owner_phone, model, type, inspection_options: inspectionOptions };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Đang xử lý...', didOpen: () => Swal.showLoading() });
                
                fetch('{{ route('staff.vehicle.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(result.value)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Đã tiếp nhận xe mới và tạo lệnh sửa chữa.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = `{{ route('staff.dashboard') }}?order_id=${data.order.id}`;
                        });
                    } else {
                        Swal.fire('Lỗi', 'Không thể lưu thông tin.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Lỗi', 'Lỗi hệ thống khi lưu xe.', 'error');
                });
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const plate = urlParams.get('new_order_plate');
        if(plate) {
            const quickInput = document.getElementById('quickInput');
            if(quickInput) {
                quickInput.value = plate;
                openAddVehicleModal();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Tự động ẩn Sidebar khi vào Bảng công việc để tối ưu diện tích
        if (typeof setSidebarState === 'function') {
            setSidebarState(false);
        }
    });
</script>
@include('staff.partials.order_modals')
@endpush
