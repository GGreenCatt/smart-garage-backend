@extends('layouts.admin')

@section('title', 'Appointment Management')

@section('content')
<!-- Custom Tailwind Config for this page -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: "#6366f1", // Indigo 500
                    "primary-hover": "#4f46e5",
                    "background-dark": "#0f172a",
                    "surface-dark": "#1e293b",
                    "accent-cyan": "#06b6d4",
                    "accent-purple": "#8b5cf6",
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    /* Enforce Dark Glass Panel */
    .glass-panel {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    /* FullCalendar Dark Theme Overrides */
    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
    }
    .fc-button {
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        text-transform: capitalize !important;
        font-weight: 600 !important;
        color: white !important;
    }
    .fc-button:hover {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }
    .fc-button-active {
        background-color: #4338ca !important;
        border-color: #4338ca !important;
    }
    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: white !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #334155 !important;
        background: transparent !important;
    }
    .fc-daygrid-day-number {
        color: #e2e8f0 !important;
    }
    .fc-col-header-cell-cushion {
        color: #cbd5e1 !important;
    }
    .fc-view {
        background-color: transparent !important;
    }
    /* Hide scrollbar for FullCalendar */
    .fc-scroller::-webkit-scrollbar {
        display: none;
    }
    .fc-scroller {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #1e293b; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569; 
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b; 
    }
    .fc-event {
        cursor: pointer !important;
        border-radius: 9999px !important; /* Rounded Pills */
        padding: 2px 4px !important;
        border: none !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
    }
    .fc-event-title {
        font-weight: 600 !important;
    }
    
    /* Sidebar Transitions */
    #sidebarContainer {
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    }
    #sidebarContent {
        transition: opacity 0.2s ease;
    }
    .sidebar-collapsed #sidebarContainer {
        width: 0px !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        opacity: 0;
    }
    .sidebar-collapsed #sidebarContent {
        opacity: 0;
        pointer-events: none;
    }
    
    /* Calendar Cell Height - Squarer Look */
    .fc-daygrid-day-frame {
        min-height: 10rem !important; /* Taller cells */
    }

    /* Modern Pill Event Styling */
    .fc-event {
        cursor: pointer !important;
        background: rgba(15, 23, 42, 0.6) !important; /* Translucent Dark */
        border: none !important;
        border-left-width: 4px !important;
        border-radius: 4px 12px 12px 4px !important; /* Unique Pill Shape */
        padding: 4px 8px !important;
        margin-bottom: 4px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        font-weight: 500 !important;
        font-size: 0.75rem !important;
        transition: all 0.2s ease;
    }
    .fc-event:hover {
        transform: translateX(2px);
        filter: brightness(1.2);
    }
    .fc-event-title, .fc-event-time {
        font-weight: 600 !important;
        color: #e2e8f0 !important;
    }

    /* Event Colors by Status */
    .evt-confirmed { border-left-color: #059669 !important; background: linear-gradient(90deg, rgba(6, 78, 59, 0.4) 0%, rgba(6, 78, 59, 0.1) 100%) !important; }
    .evt-pending { border-left-color: #d97706 !important; background: linear-gradient(90deg, rgba(120, 53, 15, 0.4) 0%, rgba(120, 53, 15, 0.1) 100%) !important; }
    .evt-cancelled { border-left-color: #dc2626 !important; background: linear-gradient(90deg, rgba(127, 29, 29, 0.4) 0%, rgba(127, 29, 29, 0.1) 100%) !important; }
    .evt-completed { border-left-color: #2563eb !important; background: linear-gradient(90deg, rgba(30, 58, 138, 0.4) 0%, rgba(30, 58, 138, 0.1) 100%) !important; }
</style>
<script>
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        // Trigger resize for FullCalendar to adjust
        setTimeout(() => window.dispatchEvent(new Event('resize')), 350);
    }
</script>

<div class="h-[calc(100vh-100px)] flex overflow-hidden font-sans bg-background-dark text-slate-200">
    <!-- Sidebar Wrapper -->
    <script> document.body.classList.add('sidebar-collapsed'); </script>
    <div id="sidebarContainer" class="w-80 border-r border-slate-700 bg-surface-dark/50 backdrop-blur-sm z-10 shrink-0 rounded-l-2xl overflow-hidden relative transition-all">
        <aside id="sidebarContent" class="w-80 flex flex-col h-full absolute inset-0">
            <div class="p-6 pb-2">
                <button onclick="document.getElementById('createModal').showModal()" class="w-full group relative flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white py-3.5 px-6 rounded-xl shadow-lg shadow-indigo-500/40 transition-all transform hover:-translate-y-0.5">
                    <span class="material-icons-round text-xl">add_circle</span>
                    <span class="font-semibold">Thêm Lịch Hẹn</span>
                    <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </button>
            </div>
            
            <div class="px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-accent-cyan text-base">event_available</span>
                    Sắp Tới
                </h2>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-slate-700 text-slate-300">{{ $appointments->where('scheduled_at', '>=', now())->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto px-4 pb-6 space-y-3 custom-scrollbar">
                @forelse($appointments as $appt)
                @php
                    $statusColor = match($appt->status) {
                        'confirmed' => 'bg-emerald-900/30 text-emerald-400 border-emerald-800',
                        'cancelled' => 'bg-red-900/30 text-red-400 border-red-800',
                        'completed' => 'bg-blue-900/30 text-blue-400 border-blue-800',
                        default => 'bg-amber-900/30 text-amber-400 border-amber-800'
                    };
                    $indicatorColor = match($appt->status) {
                        'confirmed' => 'bg-emerald-500',
                        'cancelled' => 'bg-red-500',
                        'completed' => 'bg-blue-500',
                        default => 'bg-amber-500'
                    };
                @endphp
                <div onclick="showEventDetails({{ $appt }})" class="p-4 rounded-xl bg-slate-800 border-l-4 border-l-{{ str_replace('bg-', '', $indicatorColor) }} border-y border-r border-slate-700 shadow-sm hover:shadow-md transition-all cursor-pointer group hover:border-r-indigo-500">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400">{{ $appt->scheduled_at->format('d/m • H:i') }}</span>
                        </div>
                        <span class="h-2 w-2 rounded-full {{ $indicatorColor }} {{ $appt->status == 'pending' ? 'animate-pulse' : '' }}"></span>
                    </div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold border border-slate-600">
                            {{ substr($appt->customer->name ?? 'K', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-100 group-hover:text-primary transition-colors text-sm">{{ $appt->customer->name ?? 'Khách lẻ' }}</h3>
                            <p class="text-xs text-slate-400 font-mono">{{ $appt->vehicle->license_plate ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @if($appt->service)
                        <span class="px-2 py-1 rounded-md {{ $statusColor }} text-[10px] font-bold uppercase tracking-wide border">
                            {{ $appt->service->name }}
                        </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center text-slate-500 py-8 italic text-sm">Chưa có lịch hẹn sắp tới</div>
                @endforelse
            </div>
        </aside>
    </div>

    <!-- Main Calendar Area -->
    <main class="flex-1 p-6 flex flex-col z-10 overflow-hidden bg-background-dark/50">
        <!-- Custom Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="flex items-center bg-surface-dark rounded-lg shadow-sm border border-slate-700 p-1">
                    <button onclick="toggleSidebar()" class="p-2 hover:bg-slate-700 rounded-md text-slate-400 transition-colors mr-2 border-r border-slate-700">
                        <span class="material-icons-round">menu_open</span>
                    </button>
                    <button id="cal-prev" class="p-1 hover:bg-slate-700 rounded-md text-slate-400 transition-colors">
                        <span class="material-icons-round">chevron_left</span>
                    </button>
                    <button id="cal-next" class="p-1 hover:bg-slate-700 rounded-md text-slate-400 transition-colors">
                        <span class="material-icons-round">chevron_right</span>
                    </button>
                    <button id="cal-today" class="px-3 py-1 text-sm font-medium text-slate-200 hover:bg-slate-700 rounded-md ml-1 transition-colors">Today</button>
                </div>
                <h2 id="cal-title" class="text-2xl font-bold text-white min-w-[200px]">...</h2>
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="relative group flex-1 sm:flex-none">
                    <span class="absolute left-3 top-2.5 text-slate-400 material-icons-round text-sm">search</span>
                    <input id="cal-search" class="w-full sm:w-64 pl-9 pr-4 py-2 bg-surface-dark border border-slate-700 rounded-lg text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder-slate-500" placeholder="Search..." type="text">
                </div>
                <div class="flex bg-surface-dark rounded-lg p-1 border border-slate-700 shadow-sm">
                    <button class="view-btn px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-colors" data-view="dayGridMonth">Month</button>
                    <button class="view-btn px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-colors" data-view="timeGridWeek">Week</button>
                    <button class="view-btn px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-colors" data-view="timeGridDay">Day</button>
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="flex-1 glass-panel rounded-2xl border border-slate-700 shadow-xl overflow-hidden p-4">
             <div id="calendar" class="h-full text-slate-200"></div>
        </div>
    </main>
</div>

<!-- Create Modal -->
<dialog id="createModal" class="bg-surface-dark text-white border border-slate-700 rounded-2xl shadow-2xl p-6 w-[500px] backdrop:bg-black/80">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-xl flex items-center gap-2">
            <span class="material-icons-round text-primary">add_circle_outline</span>
            Tạo Lịch Hẹn Mới
        </h3>
        <button onclick="document.getElementById('createModal').close()" class="p-2 hover:bg-slate-800 rounded-full transition-colors text-slate-500"><span class="material-icons-round">close</span></button>
    </div>
    
    <form action="{{ route('admin.appointments.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Khách Hàng (SĐT)</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-slate-400 material-icons-round text-sm">search</span>
                <input type="number" list="customer_list" name="customer_phone" class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm transition-all text-white placeholder-slate-500" placeholder="Tìm bằng số điện thoại..." required onchange="findCustomer(this.value)">
            </div>
            <input type="hidden" name="customer_id" id="customer_id">
            <datalist id="customer_list">
                @foreach(\App\Models\User::where('role', 'customer')->get() as $c)
                    <option value="{{ $c->phone }}">{{ $c->name }}</option>
                @endforeach
            </datalist>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Ngày Giờ</label>
                <input type="datetime-local" name="scheduled_at" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-white" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Dịch Vụ</label>
                 <select name="service_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-white">
                    <option value="">-- Chọn Dịch Vụ --</option>
                    @foreach(\App\Models\Service::all() as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Ghi Chú</label>
            <textarea name="notes" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-white placeholder-slate-500"></textarea>
        </div>

        <button class="w-full bg-primary hover:bg-primary-hover text-white py-3 rounded-xl font-bold mt-4 shadow-lg shadow-indigo-500/30 transition-all">Xác Nhận Đặt Lịch</button>
    </form>
</dialog>

<!-- Detail Modal -->
<dialog id="detailModal" class="bg-surface-dark text-white border border-slate-700 rounded-2xl shadow-2xl p-6 w-[500px] backdrop:bg-black/80">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-xl flex items-center gap-2">
            <span class="material-icons-round text-accent-cyan">info</span>
            Chi Tiết Lịch Hẹn
        </h3>
        <button onclick="document.getElementById('detailModal').close()" class="p-2 hover:bg-slate-800 rounded-full transition-colors text-slate-500"><span class="material-icons-round">close</span></button>
    </div>
    
    <div class="space-y-6">
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-900/30 text-primary flex items-center justify-center font-bold text-lg" id="detail_avatar">KH</div>
                <div>
                    <div class="font-bold text-lg text-white" id="detail_customer">Nguyen Van A</div>
                    <div class="text-sm text-slate-400 font-mono flex items-center gap-1">
                        <span class="material-icons-round text-xs">call</span>
                        <span id="detail_phone">0923000000</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-slate-900/50 p-3 rounded-lg border border-slate-700/50">
                    <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Phương tiện</span>
                    <span class="text-slate-200 font-semibold" id="detail_vehicle">Vios</span>
                </div>
                <div class="bg-slate-900/50 p-3 rounded-lg border border-slate-700/50">
                    <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Dịch vụ</span>
                    <span class="text-slate-200 font-semibold" id="detail_service">Thay nhớt</span>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Ghi chú</span>
                <div class="text-sm p-3 bg-slate-900/50 rounded-lg border border-slate-700/50 text-slate-300 italic" id="detail_notes">Ghi chú...</div>
            </div>
        </div>
        
        <form id="updateForm" method="POST" class="flex gap-3">
            @csrf
            @method('PUT')
            <select name="status" class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-slate-200 flex-1 focus:ring-2 focus:ring-primary focus:border-transparent outline-none" id="detail_status">
                <option value="pending">Chờ xác nhận</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="cancelled">Hủy</option>
                <option value="completed">Hoàn thành</option>
            </select>
            <button class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-md transition-all">Cập nhật</button>
        </form>

        <form id="convertForm" method="POST" class="border-t border-slate-700 pt-6">
            @csrf
            <button class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                <span class="material-icons-round">build_circle</span> Tiếp Nhận & Tạo Lệnh Sửa Chữa
            </button>
        </form>
    </div>
</dialog>

<!-- FullCalendar CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // Hide default toolbar to use custom one
            themeSystem: 'standard',
            height: '100%',
            events: [
                @foreach($appointments as $appt)
                {
                    title: '{{ $appt->scheduled_at->format("H:i") }} {{ $appt->customer->name ?? "K/L" }}',
                    start: '{{ $appt->scheduled_at }}',
                    classNames: ['evt-{{ $appt->status }}'],
                    extendedProps: @json($appt)
                },
                @endforeach
            ],
            datesSet: function(info) {
                // Update Title
                document.getElementById('cal-title').innerText = info.view.title;
                // Update Active View Button
                document.querySelectorAll('.view-btn').forEach(btn => {
                    if(btn.dataset.view === info.view.type) {
                        btn.classList.add('bg-primary', 'text-white');
                        btn.classList.remove('text-slate-400');
                    } else {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('text-slate-400');
                    }
                });
            },
            eventClick: function(info) {
                const appt = info.event.extendedProps;
                const modal = document.getElementById('detailModal');
                
                // Populate Data
                document.getElementById('detail_customer').innerText = appt.customer ? appt.customer.name : 'Khách lẻ';
                document.getElementById('detail_avatar').innerText = appt.customer ? appt.customer.name.charAt(0) : 'K';
                document.getElementById('detail_phone').innerText = appt.customer ? appt.customer.phone : 'N/A';
                document.getElementById('detail_vehicle').innerText = appt.vehicle ? appt.vehicle.model : 'Chưa có xe';
                document.getElementById('detail_service').innerText = appt.service ? appt.service.name : 'Chung';
                document.getElementById('detail_notes').innerText = appt.notes || 'Không có ghi chú';
                document.getElementById('detail_status').value = appt.status;
                
                // Update Action URLs
                document.getElementById('updateForm').action = `/admin/appointments/${appt.id}`;
                document.getElementById('convertForm').action = `/admin/appointments/${appt.id}/convert`;

                modal.showModal();
            }
        });
        calendar.render();

        // Custom Control Logic
        document.getElementById('cal-prev').addEventListener('click', () => calendar.prev());
        document.getElementById('cal-next').addEventListener('click', () => calendar.next());
        document.getElementById('cal-today').addEventListener('click', () => calendar.today());
        
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                calendar.changeView(e.target.dataset.view);
            });
        });

        // Simple Search Logic (Client-side filter by title)
        document.getElementById('cal-search').addEventListener('input', function(e) {
            const keyword = e.target.value.toLowerCase();
            const allEvents = calendar.getEvents();
            allEvents.forEach(evt => {
                if (evt.title.toLowerCase().includes(keyword)) {
                    evt.setProp('display', 'auto');
                } else {
                    evt.setProp('display', 'none');
                }
            });
        });
    });

    // Simple mocked customer finder script
    @php
        $customerList = \App\Models\User::where('role', 'customer')->get(['id', 'phone', 'name']);
    @endphp
    const customers = @json($customerList);
    function findCustomer(phone) {
        const found = customers.find(c => c.phone == phone);
        if(found) {
            document.getElementById('customer_id').value = found.id;
        }
    }
</script>
@endsection
