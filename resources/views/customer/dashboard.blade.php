@extends('layouts.customer')

@section('title', 'Tổng quan khách hàng - Smart Garage')
@section('body_class', 'flex h-screen overflow-hidden bg-[#0b1120]')
@section('navbar')
@endsection
@section('footer')
@endsection

@php
    $statusLabels = [
        'pending' => 'Chờ tiếp nhận',
        'in_progress' => 'Đang kiểm tra',
        'pending_approval' => 'Chờ duyệt báo giá',
        'approved' => 'Đã duyệt báo giá',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $appointmentLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Đã tiếp nhận',
        'cancelled' => 'Đã hủy',
        'no_show' => 'Không đến',
    ];
    $progressFor = function ($order) {
        $tasks = $order->tasks ?? collect();
        $validTasks = $tasks->reject(fn ($task) => $task->customer_approval_status === 'rejected');
        if ($validTasks->isEmpty()) {
            return match ($order->status) {
                'completed' => 100,
                'approved', 'in_progress' => 45,
                'pending_approval' => 25,
                default => 10,
            };
        }

        return (int) round(($validTasks->where('status', 'completed')->count() / max(1, $validTasks->count())) * 100);
    };
    $quoteTotalFor = function ($order) {
        return $order->tasks
            ->whereNotNull('parent_id')
            ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty())
            ->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal'));
    };
    $pendingQuotes = $orders->filter(fn ($order) => $order->quote_status === 'sent' && $order->status === 'pending_approval');
    $activeOrders = $orders->filter(fn ($order) => ! in_array($order->status, ['completed', 'cancelled'], true));
    $recentOrders = $orders->take(5);
    $upcomingAppointments = $appointments->filter(fn ($appointment) => in_array($appointment->status, ['pending', 'confirmed'], true))->take(3);
    $completedOrdersCount = $orders->where('status', 'completed')->count();
    $spentTotal = $orders->where('payment_status', 'paid')->sum('total_amount');
@endphp

@section('styles')
<style>
    body { background: #0b1120; }
    .nav-link { color: #94a3b8; border: 1px solid transparent; }
    .nav-link:hover { color: #fff; background: rgba(255,255,255,.05); }
    .nav-link.active { color: #22d3ee; background: rgba(6,182,212,.1); border-color: rgba(6,182,212,.25); }
    .panel { background: #111827; border: 1px solid #1f2937; }
    .panel-soft { background: rgba(15,23,42,.72); border: 1px solid rgba(51,65,85,.9); }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; border-radius: 999px; }
</style>
@endsection

@section('content')
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full border-r border-slate-800 bg-[#0b1120] transition-transform md:static md:translate-x-0">
    <div class="h-20 px-6 border-b border-slate-800 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-lg bg-cyan-600 text-white grid place-items-center font-black">S</span>
            <span class="text-xl font-black text-white">Smart<span class="text-cyan-400">Garage</span></span>
        </a>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400"><i class="fas fa-times"></i></button>
    </div>

    <div class="p-5 space-y-6">
        <div class="panel-soft rounded-xl p-4 flex items-center gap-3">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0891b2&color=fff" class="w-11 h-11 rounded-full" alt="">
            <div class="min-w-0">
                <div class="font-bold text-white truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-400">{{ Auth::user()->phone ?: Auth::user()->email }}</div>
            </div>
        </div>

        <nav class="space-y-2">
            <a href="{{ route('customer.dashboard') }}" class="nav-link active flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-table-cells-large w-5"></i> Tổng quan
            </a>
            <a href="{{ route('customer.appointments.create') }}" class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-calendar-plus w-5"></i> Đặt lịch mới
            </a>
            <a href="{{ route('customer.appointments.index') }}" class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-calendar-days w-5"></i> Lịch hẹn
            </a>
            <a href="{{ route('customer.vehicles.index') }}" class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-car w-5"></i> Xe của tôi
            </a>
            <a href="{{ route('customer.orders.index') }}" class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-clock-rotate-left w-5"></i> Lịch sử sửa chữa
            </a>
            <a href="{{ route('customer.profile') }}" class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold">
                <i class="fas fa-user-gear w-5"></i> Tài khoản
            </a>
        </nav>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold hover:text-red-300">
                <i class="fas fa-arrow-right-from-bracket w-5"></i> Đăng xuất
            </button>
        </form>
    </div>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-black/60 md:hidden"></div>

<main class="flex-1 h-screen overflow-y-auto scrollbar-thin">
    <header class="sticky top-0 z-30 border-b border-slate-800 bg-[#0b1120]/90 backdrop-blur md:hidden h-16 px-4 flex items-center justify-between">
        <div class="font-black text-white">SmartGarage</div>
        <button onclick="toggleSidebar()" class="text-white"><i class="fas fa-bars"></i></button>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 space-y-8">
        <section class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
            <div>
                <p class="text-cyan-300 text-sm font-black uppercase tracking-[0.2em]">Cổng khách hàng</p>
                <h1 class="text-3xl md:text-4xl font-black text-white mt-2">Tổng quan xe của bạn</h1>
                <p class="text-slate-400 mt-2">Theo dõi báo giá, lịch hẹn, tiến độ sửa chữa và trao đổi với garage tại một nơi.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customer.appointments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-cyan-950/40">
                    <i class="fas fa-calendar-plus"></i> Đặt lịch
                </a>
                <button onclick="openChat('Chung')" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 hover:bg-slate-800 px-5 py-3 text-sm font-black text-slate-100">
                    <i class="fas fa-comments text-cyan-300"></i> Chat với garage
                </button>
            </div>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="panel rounded-xl p-5">
                <div class="text-xs uppercase tracking-widest text-slate-500 font-bold">Xe đã lưu</div>
                <div class="text-3xl font-black text-white mt-2">{{ $vehicles->count() }}</div>
            </div>
            <div class="panel rounded-xl p-5">
                <div class="text-xs uppercase tracking-widest text-slate-500 font-bold">Đơn đang xử lý</div>
                <div class="text-3xl font-black text-cyan-300 mt-2">{{ $activeOrders->count() }}</div>
            </div>
            <div class="panel rounded-xl p-5">
                <div class="text-xs uppercase tracking-widest text-slate-500 font-bold">Đã hoàn thành</div>
                <div class="text-3xl font-black text-emerald-300 mt-2">{{ $completedOrdersCount }}</div>
            </div>
            <div class="panel rounded-xl p-5">
                <div class="text-xs uppercase tracking-widest text-slate-500 font-bold">Đã thanh toán</div>
                <div class="text-2xl font-black text-amber-300 mt-2">{{ number_format($spentTotal) }}đ</div>
            </div>
        </section>

        @if($pendingQuotes->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-black text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice-dollar text-amber-400"></i> Cần bạn duyệt báo giá
                    </h2>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    @foreach($pendingQuotes as $quote)
                        @php
                            $quoteTotal = $quoteTotalFor($quote);
                            $quotedItems = $quote->tasks->whereNotNull('parent_id')->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty())->count();
                        @endphp
                        <article class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-5">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-xs font-black uppercase tracking-widest text-amber-300">Đang chờ bạn phản hồi</div>
                                    <h3 class="text-xl font-black text-white mt-2">
                                        {{ $quote->vehicle->model ?? 'Xe của bạn' }}
                                        <span class="text-slate-400">({{ $quote->vehicle->license_plate ?? 'Chưa rõ biển số' }})</span>
                                    </h3>
                                    <p class="text-sm text-slate-400 mt-2">Mã đơn #{{ $quote->id }} · {{ $quotedItems }} hạng mục · Tổng dự kiến {{ number_format($quoteTotal) }}đ</p>
                                </div>
                                <a href="{{ route('customer.quote.show', $quote->id) }}" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 hover:bg-amber-300 px-5 py-3 text-sm font-black text-slate-950">
                                    Xem báo giá <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-6">
            <div class="space-y-6">
                <div class="panel rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="font-black text-white">Xe đang sửa</h2>
                        <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-cyan-300 hover:text-cyan-200">Xem tất cả</a>
                    </div>
                    <div class="p-5 space-y-4">
                        @forelse($activeOrders as $order)
                            @php
                                $progress = $progressFor($order);
                                $context = trim(($order->vehicle->model ?? 'Xe') . ' - ' . ($order->vehicle->license_plate ?? ''));
                            @endphp
                            <article class="panel-soft rounded-xl p-4">
                                <div class="flex flex-col lg:flex-row gap-5">
                                    <div class="lg:w-48 aspect-video rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center relative overflow-hidden">
                                        <i class="fas fa-car-side text-4xl text-slate-700"></i>
                                        @if($order->vehicle_id)
                                            <a href="{{ route('customer.vehicle.3d', ['id' => $order->vehicle_id, 'order_id' => $order->id]) }}" class="absolute inset-0 grid place-items-center bg-black/0 hover:bg-black/55 text-transparent hover:text-white text-xs font-black transition">
                                                Xem 3D/VHC
                                            </a>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-lg font-black text-white">{{ $order->vehicle->model ?? 'Xe của bạn' }}</h3>
                                                <p class="text-sm text-slate-400">{{ $order->vehicle->license_plate ?? 'Chưa rõ biển số' }} · {{ $order->service_type ?: 'Dịch vụ sửa chữa' }}</p>
                                            </div>
                                            <span class="w-max rounded-full border border-cyan-500/25 bg-cyan-500/10 px-3 py-1 text-xs font-black text-cyan-300">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                                        </div>
                                        <div class="mt-4">
                                            <div class="flex justify-between text-xs font-bold text-slate-400 mb-2">
                                                <span>Tiến độ</span>
                                                <span>{{ $progress }}%</span>
                                            </div>
                                            <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full bg-cyan-500" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if($order->quote_status)
                                                <a href="{{ route('customer.quote.show', $order->id) }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-2 text-xs font-bold text-white">Xem báo giá</a>
                                            @endif
                                            @if($order->vehicle_id)
                                                <a href="{{ route('customer.vehicle.3d', ['id' => $order->vehicle_id, 'order_id' => $order->id]) }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-2 text-xs font-bold text-white">Xem 3D</a>
                                            @endif
                                            <button onclick="openChat(@js($context), {{ $order->id }})" class="rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 px-3 py-2 text-xs font-bold text-cyan-200 border border-cyan-500/25">Chat</button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-700 p-8 text-center text-slate-400">
                                <i class="fas fa-car-side text-4xl opacity-30 mb-3"></i>
                                <p class="font-bold">Hiện chưa có xe đang sửa.</p>
                                <p class="text-sm mt-1">Bạn có thể đặt lịch mới để garage kiểm tra xe.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="panel rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="font-black text-white">Lịch sử gần đây</h2>
                        <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-cyan-300 hover:text-cyan-200">Xem lịch sử</a>
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse($recentOrders as $order)
                            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div>
                                    <div class="font-black text-white">Đơn #{{ $order->id }} · {{ $order->vehicle->license_plate ?? 'Chưa rõ biển số' }}</div>
                                    <div class="text-sm text-slate-400 mt-1">{{ $order->created_at?->format('d/m/Y') }} · {{ $statusLabels[$order->status] ?? $order->status }}</div>
                                </div>
                                <div class="text-left md:text-right">
                                    <div class="font-black text-white">{{ number_format($order->total_amount ?? 0) }}đ</div>
                                    <div class="text-xs text-slate-500">{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400">Chưa có lịch sử sửa chữa.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="panel rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="font-black text-white">Lịch hẹn sắp tới</h2>
                        <a href="{{ route('customer.appointments.index') }}" class="text-xs font-bold text-cyan-300 hover:text-cyan-200">Quản lý</a>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($upcomingAppointments as $appointment)
                            <div class="rounded-xl bg-slate-950/40 border border-slate-800 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-black text-white">{{ $appointment->service->name ?? 'Kiểm tra / tư vấn' }}</div>
                                        <div class="text-sm text-slate-400 mt-1">{{ $appointment->scheduled_at->format('H:i d/m/Y') }}</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $appointment->vehicle?->license_plate ?? $appointment->license_plate ?? 'Chưa rõ biển số' }}
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-amber-500/10 border border-amber-500/25 px-2 py-1 text-[10px] font-black text-amber-300">{{ $appointmentLabels[$appointment->status] ?? $appointment->status }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-700 p-6 text-center text-slate-400">
                                <p class="font-bold">Chưa có lịch hẹn sắp tới.</p>
                                <a href="{{ route('customer.appointments.create') }}" class="inline-flex mt-3 text-cyan-300 text-sm font-bold">Đặt lịch mới</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="panel rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="font-black text-white">Xe của tôi</h2>
                        <a href="{{ route('customer.vehicles.index') }}" class="text-xs font-bold text-cyan-300 hover:text-cyan-200">Xem tất cả</a>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($vehicles->take(4) as $vehicle)
                            <div class="rounded-xl bg-slate-950/40 border border-slate-800 p-4 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-black text-white">{{ $vehicle->license_plate }}</div>
                                    <div class="text-sm text-slate-400">{{ $vehicle->model }} {{ $vehicle->year ? '(' . $vehicle->year . ')' : '' }}</div>
                                </div>
                                <a href="{{ route('customer.vehicle.3d', $vehicle->id) }}" class="text-xs font-bold text-cyan-300">3D</a>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-700 p-6 text-center text-slate-400">Chưa có xe được liên kết với tài khoản.</div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </section>
    </div>
</main>
@endsection

@push('modals')
<button onclick="openChat('Chung')" id="global-chat-btn" class="fixed bottom-6 right-6 w-14 h-14 bg-cyan-600 rounded-full shadow-[0_0_24px_rgba(6,182,212,0.35)] flex items-center justify-center text-white text-2xl z-40 hover:scale-105 transition">
    <i class="fas fa-comment-dots"></i>
    <span id="chat-unread-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-[#0b1120] hidden">0</span>
</button>

<div id="chat-widget" class="fixed bottom-6 right-6 w-[calc(100vw-2rem)] max-w-md h-[560px] bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl z-50 flex flex-col transition-all duration-300 transform scale-0 origin-bottom-right opacity-0 pointer-events-none">
    <div class="p-4 border-b border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-cyan-500/10 border border-cyan-500/25 flex items-center justify-center text-cyan-300">
                <i class="fas fa-headset"></i>
            </div>
            <div>
                <h3 class="font-black text-white">Hỗ trợ trực tuyến</h3>
                <p id="chat-context" class="text-[10px] uppercase tracking-widest text-cyan-300">Đang chọn: Chung</p>
            </div>
        </div>
        <button onclick="toggleChat()" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300"><i class="fas fa-minus"></i></button>
    </div>
    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin"></div>
    <div class="p-4 border-t border-slate-700">
        <div id="chat-image-preview" class="hidden mb-3 relative max-w-[150px]">
            <img src="" id="chat-preview-img" class="w-full h-auto rounded-lg border border-slate-600" alt="Preview">
            <button onclick="clearChatImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-slate-900 text-white rounded-full hover:bg-red-500"><i class="fas fa-times text-[10px]"></i></button>
        </div>
        <div class="bg-slate-900 rounded-xl px-3 py-2 flex items-center gap-3 border border-slate-700 focus-within:border-cyan-500">
            <input type="file" id="chat-image-input" accept="image/*" class="hidden" onchange="previewChatImage(this)">
            <button onclick="document.getElementById('chat-image-input').click()" class="text-slate-400 hover:text-cyan-300"><i class="fas fa-paperclip"></i></button>
            <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." class="bg-transparent border-none outline-none text-white text-sm flex-1 placeholder-slate-500" onkeypress="handleChatKey(event)">
            <button onclick="sendChatMessage()" id="chat-send-btn" class="w-8 h-8 rounded-full bg-cyan-600 text-white hover:bg-cyan-500"><i class="fas fa-paper-plane text-xs"></i></button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const CHAT_API_SEND = "{{ route('chat.send') }}";
    const CHAT_API_MESSAGES = "{{ route('chat.messages') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let guestSessionId = localStorage.getItem('sg_guest_session_id') || 'guest_' + Date.now();
    localStorage.setItem('sg_guest_session_id', guestSessionId);
    let chatPollInterval = null;
    let lastMessageCount = 0;
    let activeRepairOrderId = null;

    function escapeChatHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closed = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !closed);
        overlay.classList.toggle('hidden', !closed);
    }

    function openChat(context = 'Chung', repairOrderId = null) {
        activeRepairOrderId = repairOrderId;
        document.getElementById('chat-context').innerText = 'Đang hỗ trợ: ' + context;
        document.getElementById('chat-unread-badge').classList.add('hidden');
        document.getElementById('chat-unread-badge').innerText = '0';
        const widget = document.getElementById('chat-widget');
        widget.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
        fetchMessages(true);
        startPolling();
        setTimeout(() => document.getElementById('chat-input').focus(), 250);
    }

    function toggleChat() {
        const widget = document.getElementById('chat-widget');
        if (widget.classList.contains('scale-0')) openChat('Chung');
        else widget.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
    }

    function startPolling() {
        if (!chatPollInterval) chatPollInterval = setInterval(() => fetchMessages(false), 3000);
    }

    function fetchMessages(forceRender = false) {
        const params = new URLSearchParams({ guest_session_id: guestSessionId });
        if (activeRepairOrderId) params.append('repair_order_id', activeRepairOrderId);

        fetch(`${CHAT_API_MESSAGES}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                const messages = data.messages || [];
                const isOpen = !document.getElementById('chat-widget').classList.contains('scale-0');
                if (messages.length > lastMessageCount) {
                    const lastMsg = messages[messages.length - 1];
                    if (lastMessageCount > 0 && lastMsg.is_staff && !isOpen) {
                        const badge = document.getElementById('chat-unread-badge');
                        badge.innerText = String((parseInt(badge.innerText || '0') || 0) + 1);
                        badge.classList.remove('hidden');
                    }
                    lastMessageCount = messages.length;
                }
                if (forceRender || isOpen) renderMessages(messages);
            });
    }

    function renderMessages(messages) {
        const container = document.getElementById('chat-messages');
        container.innerHTML = '';
        if (messages.length === 0) {
            container.innerHTML = `<div class="flex gap-3"><div class="w-8 h-8 rounded-full bg-cyan-600 text-white grid place-items-center text-xs font-black">SG</div><div class="bg-slate-800 text-slate-200 p-3 rounded-2xl rounded-tl-none text-sm">Xin chào, garage có thể hỗ trợ gì cho xe của bạn?</div></div>`;
            return;
        }
        messages.forEach(msg => {
            const isUser = !msg.is_staff;
            const div = document.createElement('div');
            div.className = isUser ? 'flex justify-end' : 'flex justify-start';
            let content = escapeChatHtml(msg.message || '').replace(/\n/g, '<br>');
            if (msg.attachment_path) {
                const attachmentPath = escapeChatHtml(msg.attachment_path);
                content += `<div class="mt-2"><img src="${attachmentPath}" class="max-w-full rounded-lg cursor-pointer border border-slate-600" onclick="window.open('${attachmentPath}', '_blank')"></div>`;
            }
            div.innerHTML = `<div class="${isUser ? 'bg-cyan-600 text-white rounded-tr-none' : 'bg-slate-800 text-slate-100 rounded-tl-none'} p-3 rounded-2xl text-sm max-w-[82%]">${content}</div>`;
            container.appendChild(div);
        });
        container.scrollTop = container.scrollHeight;
    }

    function previewChatImage(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('chat-preview-img').src = e.target.result;
            document.getElementById('chat-image-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }

    function clearChatImage() {
        document.getElementById('chat-image-input').value = '';
        document.getElementById('chat-image-preview').classList.add('hidden');
        document.getElementById('chat-preview-img').src = '';
    }

    function handleChatKey(e) {
        if (e.key === 'Enter') sendChatMessage();
    }

    function sendChatMessage() {
        const input = document.getElementById('chat-input');
        const fileInput = document.getElementById('chat-image-input');
        const msg = input.value.trim();
        const file = fileInput.files[0];
        if (!msg && !file) return;

        const formData = new FormData();
        formData.append('message', msg);
        if (file) formData.append('image', file);
        formData.append('guest_session_id', guestSessionId);
        formData.append('context', document.getElementById('chat-context').innerText);
        if (activeRepairOrderId) formData.append('repair_order_id', activeRepairOrderId);

        input.value = '';
        clearChatImage();
        fetch(CHAT_API_SEND, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: formData
        }).then(() => fetchMessages(true));
    }

    window.addEventListener('load', () => {
        startPolling();
        fetchMessages(false);
    });
</script>
@endpush
