@extends('layouts.customer')

@section('title', 'Dashboard - Smart Garage')

@section('body_class', 'flex h-screen overflow-hidden')

@section('navbar')
@endsection

@section('footer')
@endsection

@section('styles')
<style>
    :root { --secondary-dark: #334155; --accent-blue: #3b82f6; }
    .glass-panel { background: #1e293b; border: 1px solid #334155; }
    .nav-item { transition: all 0.2s; color: #94a3b8; border-right: 3px solid transparent; }
    .nav-item:hover { color: #fff; background: rgba(255, 255, 255, 0.05); }
    .nav-item.active { color: #06b6d4; background: rgba(6, 182, 212, 0.1); border-right: 3px solid #06b6d4; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--primary-dark); }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }
    .status-badge { @apply text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider; }
    .status-in-progress { @apply bg-cyan-500/10 text-cyan-400 border border-cyan-500/20; }
    .status-completed { @apply bg-green-500/10 text-green-400 border border-green-500/20; }
    .status-pending { @apply bg-orange-500/10 text-orange-400 border border-orange-500/20; }
</style>
@endsection

@section('content')
<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 bg-[#0b1120] border-r border-[#1e293b] flex-col w-64 transform -translate-x-full md:translate-x-0 md:static md:flex z-50 transition-transform duration-300">
    <div class="h-20 flex items-center justify-between px-6 border-b border-[#1e293b]">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="w-8 h-8 bg-cyan-600 rounded-lg flex items-center justify-center text-white font-black shadow-lg shadow-cyan-900/50">S</a>
            <span class="text-xl font-black tracking-tight text-white">Smart<span class="text-cyan-500">Garage</span></span>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <div class="flex items-center gap-3 p-3 mb-6 bg-[#1e293b] rounded-xl border border-[#334155]">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="w-10 h-10 rounded-full border border-gray-500">
            <div class="overflow-hidden">
                <h4 class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</h4>
                <p class="text-xs text-amber-500 font-semibold"><i class="fas fa-crown"></i> Gold Member</p>
            </div>
        </div>

        <nav class="space-y-1">
            <a href="{{ route('customer.dashboard') }}" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ Route::is('customer.dashboard') ? 'active text-cyan-400 bg-cyan-500/10 border-r-2 border-cyan-500' : '' }}">
                <i class="fas fa-th-large w-5"></i> Tổng Quan
            </a>
            
            <a href="{{ route('customer.vehicles.index') }}" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ Route::is('customer.vehicles.*') ? 'active text-cyan-400 bg-cyan-500/10 border-r-2 border-cyan-500' : '' }}">
                <i class="fas fa-car w-5"></i> Xe Của Tôi
            </a>

            <a href="{{ route('customer.orders.index') }}" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ Route::is('customer.orders.*') ? 'active text-cyan-400 bg-cyan-500/10 border-r-2 border-cyan-500' : '' }}">
                <i class="fas fa-history w-5"></i> Lịch Sử Sửa Chữa
            </a>
            
            <a href="{{ route('customer.appointments.index') }}" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ Route::is('customer.appointments.*') ? 'active text-cyan-400 bg-cyan-500/10 border-r-2 border-cyan-500' : '' }}">
                <i class="fas fa-calendar-alt w-5"></i> Đặt Lịch Hẹn
            </a>

            <div class="pt-4 mt-4 border-t border-[#1e293b]">
                <a href="{{ route('customer.profile') }}" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ Route::is('customer.profile') ? 'active text-cyan-400 bg-cyan-500/10 border-r-2 border-cyan-500' : '' }}">
                    <i class="fas fa-user-cog w-5"></i> Tài Khoản
                </a>
                <button onclick="logout()" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium hover:text-red-400"><i class="fas fa-sign-out-alt w-5"></i> Đăng Xuất</button>
            </div>
        </nav>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-opacity"></div>

<!-- Main Content -->
<main class="flex-1 flex flex-col h-screen overflow-hidden relative">
    <!-- Top Mobile Header -->
    <header class="h-16 bg-[#0b1120]/90 backdrop-blur border-b border-[#1e293b] flex md:hidden items-center justify-between px-4 sticky top-0 z-30">
        <div class="font-bold text-white">SmartGarage</div>
        <button onclick="toggleSidebar()" class="text-white"><i class="fas fa-bars"></i></button>
    </header>

    <div class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
        <!-- Overview Tab -->
        <div id="tab-overview" class="tab-content max-w-6xl mx-auto space-y-8 animate-fade-in">
            <div class="flex justify-between items-end">
                <div>
                <h1 class="text-3xl font-bold text-white mb-1">Tổng Quan</h1>
                    <p class="text-slate-400 text-sm">Chào mừng trở lại, hôm nay xe của bạn thế nào?</p>
                </div>
                <a href="{{ route('customer.appointments.create') }}" class="bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2.5 rounded-lg font-semibold shadow-lg shadow-cyan-900/30 transition text-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Đặt Lịch Mới
                </a>
            </div>

            @php $pendingQuotes = $orders->where('quote_status', 'sent'); @endphp
            @if($pendingQuotes->count() > 0)
            <div class="space-y-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-amber-500"></i> Báo giá chờ bạn duyệt
                </h2>
                @foreach($pendingQuotes as $quote)
                <div class="glass-panel rounded-2xl p-6 border-l-4 border-l-amber-500 bg-amber-500/5 transition hover:bg-amber-500/10 scale-100 hover:scale-[1.01]">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-bold text-amber-500 uppercase tracking-widest">Đang chờ duyệt</span>
                                <span class="text-slate-500">•</span>
                                <span class="text-slate-400 text-xs">Mã đơn: #{{ $quote->id }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">{{ $quote->vehicle->model }} ({{ $quote->vehicle->license_plate }})</h3>
                            
                            <div class="space-y-2 mb-4">
                                @foreach($quote->items as $item)
                                <div class="flex justify-between text-sm items-center">
                                    <span class="text-slate-400">
                                        {{ $item->name }} x{{ $item->qty }}
                                        @if($item->status == 'rejected')
                                            <span class="text-[10px] text-red-500 ml-2 uppercase font-bold">(Từ chối)</span>
                                        @elseif($item->status == 'approved')
                                            <span class="text-[10px] text-green-500 ml-2 uppercase font-bold">(Đã duyệt)</span>
                                        @endif
                                    </span>
                                    <span class="text-slate-300 font-mono">{{ number_format($item->qty * $item->price) }}đ</span>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="pt-3 border-t border-slate-700 flex justify-between items-center">
                                <span class="text-slate-400 font-medium">Tổng phí dự kiến:</span>
                                <span class="text-xl font-black text-amber-500">{{ number_format($quote->items->sum('total')) }}đ</span>
                            </div>
                        </div>
                        <div class="flex flex-row md:flex-col gap-3 justify-center">
                            <button onclick="approveQuote({{ $quote->id }})" class="flex-1 bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-cyan-900/40">
                                <i class="fas fa-check mr-2"></i> Đồng Ý
                            </button>
                            <button onclick="rejectQuote({{ $quote->id }})" class="flex-1 bg-slate-800 hover:bg-red-900/30 text-slate-300 hover:text-red-400 border border-slate-700 hover:border-red-900/50 px-6 py-3 rounded-xl font-bold transition">
                                <i class="fas fa-times mr-2"></i> Từ Chối
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Active Service List -->
            @php $activeOrders = $orders->where('status', '!=', 'completed'); @endphp
            @if($activeOrders->count() > 0)
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-cyan-500 rounded-full"></div>
                        <h2 class="text-xl font-bold text-white">Xe Đang Sửa ({{ $activeOrders->count() }})</h2>
                    </div>

                    @foreach($activeOrders as $activeOrder)
                    <div class="glass-panel rounded-2xl p-6 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition"><i class="fas fa-cogs text-9xl text-cyan-500"></i></div>
                        
                        <div class="flex flex-col md:flex-row gap-8 relative z-10">
                            <!-- Vehicle Image / 3D Link -->
                            <div class="w-full md:w-1/3">
                                <div class="aspect-video bg-slate-800 rounded-xl border border-slate-700 flex items-center justify-center relative overflow-hidden">
                                    <i class="fas fa-car text-4xl text-slate-600 mb-2"></i>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-60"></div>
                                    <div class="absolute bottom-3 left-3">
                                        <div class="text-white font-bold">{{ $activeOrder->vehicle->model }}</div>
                                        <div class="text-xs text-slate-400">{{ $activeOrder->vehicle->license_plate }}</div>
                                    </div>
                                    <a href="{{ route('customer.vehicle.3d', ['id' => $activeOrder->vehicle->id]) }}" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 hover:opacity-100 transition backdrop-blur-sm">
                                        <span class="bg-white text-black px-4 py-2 rounded-full font-bold text-xs"><i class="fas fa-eye mr-1"></i> Xem 3D</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Progress Info -->
                            <div class="flex-1 space-y-5">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-400">Dịch vụ:</span>
                                    <span class="bg-slate-700 px-3 py-1 rounded-full text-white text-xs">{{ $activeOrder->service_type }}</span>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-2">
                                        <span class="text-cyan-400 font-bold">Tiến độ: {{ $activeOrder->progress }}%</span>
                                        <span class="status-badge status-in-progress">{{ $activeOrder->status == 'in_progress' ? 'Đang thực hiện' : 'Chờ xử lý' }}</span>
                                    </div>
                                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-cyan-600 to-blue-500 shadow-[0_0_10px_rgba(6,182,212,0.5)]" style="width: {{ $activeOrder->progress }}%"></div>
                                    </div>
                                </div>
                                <div class="bg-slate-800/50 rounded-lg p-3 border border-slate-700 flex items-center justify-between gap-3">
                                  <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition text-indigo-400 bg-white/5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center"><i class="fas fa-home"></i></div>
                    <span class="font-bold">Trang Chủ</span>
                </a>
                 <a href="{{ route('customer.orders.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition text-slate-400 hover:text-white">
                    <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center"><i class="fas fa-history"></i></div>
                    <span class="font-bold">Lịch Sử Sửa Chữa</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition text-slate-400 hover:text-white">
                    <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center"><i class="fas fa-car"></i></div>
                    <span class="font-bold">Xe Của Tôi</span>
                </a>                    </div>
                                    <button onclick="openChat('{{ $activeOrder->vehicle->model }} - {{ $activeOrder->vehicle->license_plate }}')" class="bg-cyan-600/20 hover:bg-cyan-600/40 text-cyan-400 border border-cyan-600/30 px-3 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                        <i class="fas fa-comments"></i> Chat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="glass-panel p-8 text-center text-slate-400 rounded-xl border-dashed border-2 border-slate-700">
                    <i class="fas fa-car-side text-4xl mb-3 opacity-30"></i>
                    <p>Hiện không có xe nào đang sửa chữa.</p>
                </div>
            @endif

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="glass-panel p-5 rounded-xl"><div class="text-slate-400 text-xs uppercase tracking-wider mb-2">Tổng Chi Tiêu</div><div class="text-2xl font-black text-white">15.2M <span class="text-xs font-normal text-slate-500">VND</span></div></div>
                <div class="glass-panel p-5 rounded-xl"><div class="text-slate-400 text-xs uppercase tracking-wider mb-2">Điểm Tích Lũy</div><div class="text-2xl font-black text-amber-500">2,450 <span class="text-xs font-normal text-slate-500">pts</span></div></div>
                <div class="glass-panel p-5 rounded-xl"><div class="text-slate-400 text-xs uppercase tracking-wider mb-2">Số Xe</div><div class="text-2xl font-black text-white">2</div></div>
                <div class="glass-panel p-5 rounded-xl"><div class="text-slate-400 text-xs uppercase tracking-wider mb-2">Lần Sửa Chữa</div><div class="text-2xl font-black text-white">12</div></div>
            </div>
        </div>

        <!-- History Tab -->
        <div id="tab-history" class="tab-content hidden max-w-6xl mx-auto space-y-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-white">Lịch Sử Sửa Chữa</h1>
                <div class="bg-blue-900/30 border border-blue-500/30 px-4 py-2 rounded-lg flex items-center gap-2 text-xs text-blue-300">
                    <i class="fas fa-sync-alt"></i> Đồng bộ theo hồ sơ: {{ Auth::user()->name ?? 'Guest' }}
                </div>
            </div>
            
            <div class="space-y-8 relative before:absolute before:left-4 before:top-4 before:bottom-4 before:w-0.5 before:bg-slate-700">
                @forelse($orders as $order)
                <div class="relative pl-12 group">
                    <div class="absolute left-[9px] top-6 w-4 h-4 rounded-full {{ $order->status == 'completed' ? 'bg-cyan-600 shadow-[0_0_10px_rgba(6,182,212,0.5)]' : 'bg-orange-500' }} border-4 border-[#0b1120]"></div>
                    <div class="glass-panel p-6 rounded-xl hover:border-cyan-500/30 transition">
                        <div class="flex flex-col md:flex-row justify-between md:items-start gap-4 mb-4">
                            <div>
                                <div class="text-xs text-cyan-400 font-bold mb-1">{{ $order->created_at->format('d/m/Y') }} (#{{ $order->id }})</div>
                                <h3 class="text-lg font-bold text-white">{{ $order->service_type }}</h3>
                                <div class="text-slate-400 text-sm">{{ $order->vehicle->model ?? 'Unknown' }} • {{ $order->vehicle->license_plate ?? 'Unknown' }}</div>
                            </div>
                            <div class="text-right">
                                <span class="status-badge {{ $order->status == 'completed' ? 'status-completed' : 'status-pending' }}">
                                    {{ $order->status == 'completed' ? 'Hoàn Thành' : 'Đang Xử Lý' }}
                                </span>
                            </div>
                        </div>
                        {{-- Optional: List tasks or total cost if available --}}
                        <div class="flex gap-3">
                            <a href="{{ route('customer.vehicle.3d', $order->vehicle_id) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs font-semibold">Xem 3D & Chat</a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="pl-12">
                   <div class="glass-panel p-8 text-center text-slate-400">
                       <i class="fas fa-history text-4xl mb-3 opacity-30"></i>
                       <p>Chưa có lịch sử sửa chữa nào được ghi nhận.</p>
                   </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="tab-profile" class="tab-content hidden max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-white mb-6">Cài Đặt Tài Khoản</h1>
            <div class="glass-panel p-8 rounded-2xl">
                <form class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div><label class="block text-slate-400 text-xs uppercase font-bold mb-2">Họ Tên</label><input type="text" value="{{ Auth::user()->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-cyan-500 outline-none transition"></div>
                        <div><label class="block text-slate-400 text-xs uppercase font-bold mb-2">Số Điện Thoại</label><input type="text" value="{{ Auth::user()->phone ?? Auth::user()->email }}" disabled class="w-full bg-slate-900/50 border border-slate-800 rounded-lg px-4 py-3 text-slate-500 cursor-not-allowed"><p class="text-[10px] text-slate-500 mt-1">* Định danh không thể thay đổi</p></div>
                    </div>
                    <div class="pt-4"><button class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-cyan-900/20 transition">Lưu Thay Đổi</button></div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('modals')
<!-- QR Modal -->
<div id="qr-modal" class="hidden fixed inset-0 bg-black/80 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 text-center max-w-sm w-full">
        <h3 class="text-slate-900 font-bold text-xl mb-2">Chia Sẻ Trạng Thái Xe</h3>
        <p class="text-slate-500 text-sm mb-4" id="qr-vehicle-name">Toyota Camry • 30A-123.45</p>
        <div class="bg-slate-100 p-4 rounded-xl inline-block mb-4"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://smartgarage.vn/guest/123" class="w-40 h-40 mix-blend-multiply"></div>
        <p class="text-xs text-slate-500 mb-4 px-4">Quét mã này để xem trạng thái sửa chữa mà không cần đăng nhập.</p>
        <button onclick="document.getElementById('qr-modal').classList.add('hidden')" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800">Đóng</button>
    </div>
</div>

<!-- Global Chat Bubble -->
<button onclick="openChat('Chung')" id="global-chat-btn" class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-tr from-cyan-600 to-blue-600 rounded-full shadow-[0_0_20px_rgba(6,182,212,0.4)] flex flex-col items-center justify-center text-white text-2xl z-40 hover:scale-110 transition-transform duration-300">
    <i class="fas fa-comment-dots"></i>
    <span id="chat-unread-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-[#0b1120] hidden shadow-lg animate-pulse">0</span>
</button>

<!-- Chat Widget -->
<div id="chat-widget" class="fixed bottom-6 right-6 w-96 bg-[#0f172a]/95 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 z-50 flex flex-col transition-all duration-400 ease-[cubic-bezier(0.34,1.56,0.64,1)] transform scale-0 origin-bottom-right opacity-0 pointer-events-none">
    <div class="p-4 bg-gradient-to-r from-slate-800 to-[#1e293b] border-b border-slate-700/50 rounded-t-2xl flex justify-between items-center cursor-pointer shadow-sm" onclick="toggleChat()">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-headset text-cyan-400 text-lg"></i>
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-800 rounded-full shadow-[0_0_5px_#22c55e]"></span>
            </div>
            <div>
                <h3 class="font-bold text-white tracking-wide">Hỗ Trợ Trực Tuyến</h3>
                <p id="chat-context" class="text-[10px] text-cyan-400 font-medium uppercase tracking-wider">Đang chọn: Chung</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="event.stopPropagation(); toggleChat()" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-white hover:bg-slate-700 transition"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    
    <div class="flex-1 h-[350px] overflow-y-auto p-5 space-y-4 bg-[#0b1120]/80 custom-scrollbar" id="chat-messages">
        <!-- Messages will be injected here -->
    </div>
    
    <div class="p-4 bg-slate-800/80 border-t border-slate-700/50 rounded-b-2xl backdrop-blur-md">
        <!-- Image Preview Area -->
        <div id="chat-image-preview" class="hidden mb-3 relative max-w-[150px]">
            <img src="" id="chat-preview-img" class="w-full h-auto rounded-lg border border-slate-600 shadow-sm" alt="Preview">
            <button onclick="clearChatImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-slate-900 text-white rounded-full flex items-center justify-center hover:bg-red-500 transition shadow-lg border border-slate-700">
                <i class="fas fa-times text-[10px]"></i>
            </button>
        </div>
        
        <div class="bg-slate-900/80 rounded-xl px-4 py-2.5 flex items-center gap-3 border border-slate-700 shadow-inner focus-within:border-cyan-500 transition focus-within:ring-2 focus-within:ring-cyan-500/20">
            <input type="file" id="chat-image-input" accept="image/*" class="hidden" onchange="previewChatImage(this)">
            <button onclick="document.getElementById('chat-image-input').click()" class="text-slate-400 hover:text-cyan-400 transition" title="Gửi ảnh">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." class="bg-transparent border-none outline-none text-white text-sm flex-1 placeholder-slate-500" onkeypress="handleChatKey(event)">
            <button onclick="sendChatMessage()" id="chat-send-btn" class="w-8 h-8 flex items-center justify-center rounded-full bg-cyan-600 text-white hover:bg-cyan-500 shadow-lg shadow-cyan-900/50 transition transform hover:scale-105"><i class="fas fa-paper-plane text-xs -ml-0.5"></i></button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function approveQuote(id) {
        if(!confirm('Bạn đồng ý với báo giá này và cho phép xưởng tiến hành sửa chữa?')) return;
        fetch(`/customer/order/${id}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                alert('Cảm ơn bạn! Xưởng sẽ sớm tiến hành công việc.');
                location.reload();
            }
        });
    }

    function rejectQuote(id) {
        if(!confirm('Bạn không đồng ý với báo giá này? Cố vấn dịch vụ sẽ liên hệ lại với bạn.')) return;
        fetch(`/customer/order/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                alert('Đã ghi nhận ý kiến từ chối. Chúng tôi sẽ liên hệ lại sớm.');
                location.reload();
            }
        });
    }

    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.nav-item').forEach(el => { el.classList.remove('active', 'text-cyan-400', 'bg-cyan-500/10', 'border-cyan-500'); });
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        const btn = document.querySelector(`button[onclick="showTab('${tabId}')"]`);
        if (btn) btn.classList.add('active');
    }
    function showQRCode(name, plate) {
        document.getElementById('qr-vehicle-name').innerText = `${name} • ${plate}`;
        document.getElementById('qr-modal').classList.remove('hidden');
    }
    function logout() {
        if (confirm("Bạn có chắc muốn đăng xuất?")) {
            fetch("{{ route('logout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
            })
            .then(res => {
                window.location.href = "/";
            })
            .catch(err => alert("Lỗi đăng xuất"));
        }
    }

    // Chat Logic
    const CHAT_API_SEND = "{{ route('chat.send') }}";
    const CHAT_API_MESSAGES = "{{ route('chat.messages') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    
    let guestSessionId = localStorage.getItem('sg_guest_session_id');
    if (!guestSessionId) {
        guestSessionId = 'guest_' + Date.now();
        localStorage.setItem('sg_guest_session_id', guestSessionId);
    }

    let chatPollInterval = null;
    let lastMessageCount = 0;

    // Ping Audio Generator (No external assets needed)
    function playPing() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0, ctx.currentTime);
            gain.gain.linearRampToValueAtTime(0.5, ctx.currentTime + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch (e) { console.log('Audio disabled by browser.'); }
    }

    function startPolling() {
        if(!chatPollInterval) {
            chatPollInterval = setInterval(fetchMessages, 3000); // Poll every 3 seconds
        }
    }

    function fetchMessages() {
        fetch(`${CHAT_API_MESSAGES}?guest_session_id=${guestSessionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.messages) {
                    const newCount = data.messages.length;
                    if (newCount > lastMessageCount) {
                        const newMsgs = newCount - lastMessageCount;
                        const lastMsg = data.messages[newCount - 1];
                        const isChatOpen = !document.getElementById('chat-widget').classList.contains('scale-0');
                        
                        // Notify if it's a new message from staff
                        if (lastMessageCount > 0 && lastMsg.is_staff) {
                            playPing();
                            if (!isChatOpen) {
                                const badge = document.getElementById('chat-unread-badge');
                                badge.innerText = parseInt(badge.innerText || 0) + newMsgs;
                                badge.classList.remove('hidden');
                                document.getElementById('global-chat-btn').classList.add('animate-bounce');
                                setTimeout(() => document.getElementById('global-chat-btn').classList.remove('animate-bounce'), 2000);
                            }
                        }

                        lastMessageCount = newCount;
                        if (isChatOpen) {
                            renderMessages(data.messages);
                        }
                    }
                }
            });
    }

    function renderMessages(messages) {
        const container = document.getElementById('chat-messages');
        const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
        
        container.innerHTML = ''; 
        if(messages.length === 0) {
            container.innerHTML = `
            <div class="flex gap-3 mb-6 animate-fade-in shadow-sm">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-cyan-900/30 flex-shrink-0">SG</div>
                <div class="bg-slate-800 text-slate-200 p-3 rounded-2xl rounded-tl-none text-sm border border-slate-700/50 shadow-md">Xin chào Quý khách! <br> Chúng tôi có thể hỗ trợ gì cho xe của bạn hôm nay?</div>
            </div>`;
        }
        
        messages.forEach((msg, index) => {
            const isUser = !msg.is_staff; 
            const div = document.createElement('div');
            // Adding subtle entry animation for the last message
            const animClass = (index === messages.length - 1) ? 'animate-[slideUp_0.3s_ease-out]' : '';
            div.className = isUser ? `flex gap-3 flex-row-reverse mb-4 ${animClass}` : `flex gap-3 mb-4 ${animClass}`;
            
            let msgContent = msg.message;
            if (msg.attachment_path) {
                msgContent += `<div class="mt-2"><img src="${msg.attachment_path}" class="max-w-full rounded-lg cursor-pointer hover:opacity-90 transition border border-slate-600/50" onclick="window.open('${msg.attachment_path}', '_blank')"></div>`;
            }

            div.innerHTML = isUser ? 
                `<div class="bg-cyan-600 text-white p-3 rounded-2xl rounded-tr-none text-sm max-w-[80%] shadow-md shadow-cyan-900/20">${msgContent}
                    <div class="text-[9px] text-cyan-200 text-right mt-1 opacity-70">Gửi đi</div>
                 </div>` :
                `<div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-cyan-900/30 flex-shrink-0">SG</div>
                 <div class="bg-slate-800 text-slate-200 p-3 rounded-2xl rounded-tl-none text-sm max-w-[80%] border border-slate-700/50 shadow-md">${msgContent}</div>`;
            container.appendChild(div);
        });

        // Auto scroll to bottom
        if (wasScrolledToBottom || messages.length > 0) {
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }
    }

    function openChat(context = '') {
        const widget = document.getElementById('chat-widget');
        const contextEl = document.getElementById('chat-context');
        const badge = document.getElementById('chat-unread-badge');
        
        if (context) contextEl.innerText = "Đang hỗ trợ: " + context;
        
        // Hide badge
        badge.classList.add('hidden');
        badge.innerText = '0';
        
        widget.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
        setTimeout(() => document.getElementById('chat-input').focus(), 400);
        
        // Force immediate fetch and tender
        fetchMessages();
        startPolling(); // Ensure polling is active
    }

    function toggleChat() {
        const widget = document.getElementById('chat-widget');
        if (widget.classList.contains('scale-0')) {
            openChat();
        } else {
            widget.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
        }
    }
    
    function previewChatImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('chat-preview-img').src = e.target.result;
                document.getElementById('chat-image-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearChatImage() {
        document.getElementById('chat-image-input').value = "";
        document.getElementById('chat-image-preview').classList.add('hidden');
        document.getElementById('chat-preview-img').src = "";
    }

    function handleChatKey(e) { if (e.key === 'Enter') sendChatMessage(); }
    
    function sendChatMessage() {
        const input = document.getElementById('chat-input');
        const fileInput = document.getElementById('chat-image-input');
        const msg = input.value.trim();
        const file = fileInput.files[0];
        const container = document.getElementById('chat-messages');
        const submitBtn = document.getElementById('chat-send-btn');
        
        if (!msg && !file) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs -ml-0.5"></i>';

        // Optimistic UI for text
        const userDiv = document.createElement('div');
        userDiv.className = 'flex gap-3 flex-row-reverse mb-4 animate-[slideUp_0.2s_ease-out]';
        
        let optimisticContent = msg || '[Hình ảnh]';
        userDiv.innerHTML = `<div class="bg-cyan-600 text-white p-3 rounded-2xl rounded-tr-none text-sm max-w-[80%] shadow-md opacity-70">${optimisticContent} <i class="fas fa-spinner fa-spin text-[10px] ml-2"></i></div>`;
        container.appendChild(userDiv);
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        
        input.value = '';
        clearChatImage();

        const formData = new FormData();
        formData.append('message', msg);
        if (file) formData.append('image', file);
        formData.append('guest_session_id', guestSessionId);
        formData.append('context', document.getElementById('chat-context').innerText);

        fetch(CHAT_API_SEND, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
             fetchMessages(); // Refresh UI instantly
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane text-xs -ml-0.5"></i>';
        });
    }

    // Start polling immediately when customer logs in to catch red dots
    window.addEventListener('load', () => {
        startPolling();
    });
    // Mobile menu logic
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isClosed = sidebar.classList.contains('-translate-x-full');
        
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>
@endpush
