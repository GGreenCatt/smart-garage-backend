<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Garage - Hệ Thống Sửa Chữa Xe Thông Minh')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-dark: #0f172a;
            --primary-light: #1e293b;
            --accent-cyan: #06b6d4;
            --text-gray: #94a3b8;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--primary-dark); color: #f8fafc; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.3s ease; }
        .glass-card:hover { background: rgba(255, 255, 255, 0.08); transform: translateY(-5px); border-color: rgba(6, 182, 212, 0.3); box-shadow: 0 10px 30px -10px rgba(6, 182, 212, 0.2); }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body class="@yield('body_class', 'overflow-x-hidden')">

    @section('navbar')
    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="w-10 h-10 bg-cyan-600 rounded-lg flex items-center justify-center text-white font-black text-xl shadow-lg shadow-cyan-900/50">S</a>
                <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-white">Smart<span class="text-cyan-500">Garage</span></a>
            </div>
            <div class="hidden md:flex items-center gap-8">
                @if(Route::is('home'))
                <a href="#features" class="text-sm font-medium text-slate-300 hover:text-white transition">Công Nghệ</a>
                <a href="#process" class="text-sm font-medium text-slate-300 hover:text-white transition">Quy Trình</a>
                <a href="#why-us" class="text-sm font-medium text-slate-300 hover:text-white transition">Cam Kết</a>
                @endif
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <!-- Notification Bell for Customer -->
                    <div class="relative" id="customer-notification-dropdown-container">
                        <button onclick="toggleCustomerNotifications()" class="relative w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white transition flex items-center justify-center border border-white/10">
                            <i class="fas fa-bell"></i>
                            <span id="cust-unread-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center hidden shadow-sm border border-[#0f172a]">0</span>
                        </button>
                        
                        <div id="customer-notification-dropdown" class="absolute right-0 mt-3 w-80 md:w-96 glass-card rounded-2xl shadow-2xl overflow-hidden transform scale-95 opacity-0 pointer-events-none transition-all duration-200 z-50 origin-top-right flex flex-col max-h-[400px]">
                            <div class="p-4 border-b border-white/10 flex justify-between items-center bg-white/5">
                                <h3 class="font-bold text-white"><i class="fas fa-bell mr-2 text-cyan-400"></i> Thông Báo</h3>
                            </div>
                            <div id="customer-notification-list" class="flex-1 overflow-y-auto w-full custom-scrollbar">
                                <div class="p-8 text-center text-slate-400">
                                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3 opacity-30"></i>
                                    <p class="text-sm">Đang tải...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('customer.dashboard') }}" class="px-6 py-2.5 bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-400 rounded-lg font-semibold transition border border-cyan-600/30 hidden md:inline-flex">
                        <i class="fas fa-home mr-1"></i> Trang Chủ
                    </a>
                @else
                    <button onclick="openLoginModal()" class="px-6 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-lg font-semibold transition backdrop-blur-sm border border-white/10">
                        Đăng Nhập / Đăng Ký
                    </button>
                @endauth
            </div>
        </div>
    </nav>
    @show
    
    @yield('content')

    @section('footer')
    <!-- Footer -->
    <footer class="bg-[#0b1120] border-t border-slate-800 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-12 mb-12">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-cyan-600 rounded flex items-center justify-center text-white font-bold">S</div>
                    <span class="text-xl font-bold text-white">SmartGarage</span>
                </div>
                <p class="text-slate-500 text-sm">Hệ thống chăm sóc xe thông minh hàng đầu Việt Nam.</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Dịch Vụ</h4>
                <ul class="space-y-2 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-cyan-400 transition">Bảo dưỡng định kỳ</a></li>
                    <li><a href="#" class="hover:text-cyan-400 transition">Sửa chữa chung</a></li>
                    <li><a href="#" class="hover:text-cyan-400 transition">Chăm sóc xe Detailing</a></li>
                    <li><a href="#" class="hover:text-cyan-400 transition">Cứu hộ 24/7</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Liên Hệ</h4>
                <ul class="space-y-2 text-sm text-slate-500">
                    <li class="flex items-center gap-2"><i class="fas fa-map-marker-alt w-4"></i> 123 Đường ABC, Hà Nội</li>
                    <li class="flex items-center gap-2"><i class="fas fa-phone w-4"></i> 1900 1234</li>
                    <li class="flex items-center gap-2"><i class="fas fa-envelope w-4"></i> contact@smartgarage.vn</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Giờ Mở Cửa</h4>
                <ul class="space-y-2 text-sm text-slate-500">
                    <li class="flex justify-between"><span>Thứ 2 - Thứ 6:</span> <span class="text-white">8:00 - 18:00</span></li>
                    <li class="flex justify-between"><span>Thứ 7:</span> <span class="text-white">8:00 - 17:00</span></li>
                    <li class="flex justify-between"><span>Chủ Nhật:</span> <span class="text-white">Nghỉ</span></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 border-t border-slate-800 pt-8 text-center text-sm text-slate-600">
            &copy; 2026 Smart Garage Management System. All rights reserved.
        </div>
    </footer>
    @show

    @if(!request('readonly'))
    <!-- SOS Button -->
    <a href="{{ route('customer.sos.index') }}" class="fixed bottom-28 right-6 w-14 h-14 bg-gradient-to-tr from-red-600 to-red-500 rounded-full flex items-center justify-center text-white shadow-[0_0_20px_rgba(239,68,68,0.5)] z-40 animate-bounce hover:scale-110 transition duration-300">
        <i class="fas fa-truck-medical text-xl"></i>
    </a>
    @endif

    @stack('modals')
    @stack('modals')
    @stack('scripts')
    <script>
        // Global SweetAlert Toast Logic
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif

        @if(session('error'))
            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
        @endif

        @if(session('warning'))
            Toast.fire({ icon: 'warning', title: "{{ session('warning') }}" });
        @endif

        @if(session('info'))
            Toast.fire({ icon: 'info', title: "{{ session('info') }}" });
        @endif
    </script>

    <script>
        function openLoginModal() {
            const modal = document.getElementById('authModal');
            if(modal) modal.classList.remove('hidden');
        }
        function closeAuthModal() {
            const modal = document.getElementById('authModal');
            if(modal) modal.classList.add('hidden');
        }
    </script>

    @auth
    <script>
        // --- Customer Notification Logic ---
        let custNotiPollInterval = null;
        let custUnreadCount = 0;

        function toggleCustomerNotifications() {
            const dropdown = document.getElementById('customer-notification-dropdown');
            if (dropdown.classList.contains('scale-95')) {
                dropdown.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.add('scale-100', 'opacity-100');
            } else {
                dropdown.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.remove('scale-100', 'opacity-100');
            }
        }

        document.addEventListener('click', function(e) {
            const container = document.getElementById('customer-notification-dropdown-container');
            if (container && !container.contains(e.target)) {
                const dropdown = document.getElementById('customer-notification-dropdown');
                if (dropdown) {
                    dropdown.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                    dropdown.classList.remove('scale-100', 'opacity-100');
                }
            }
        });

        function fetchCustomerNotifications() {
            fetch("{{ route('customer.notifications.index') }}")
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('customer-notification-list');
                const badge = document.getElementById('cust-unread-badge');
                if (!list) return;

                list.innerHTML = '';
                custUnreadCount = 0;

                if (data.length === 0) {
                    list.innerHTML = `<div class="p-8 text-center text-slate-400"><i class="fas fa-check-circle text-4xl mb-3 opacity-20"></i><p class="text-sm">Bạn không có thông báo nào</p></div>`;
                    badge.classList.add('hidden');
                    return;
                }

                data.forEach(noti => {
                    if (!noti.read_at) custUnreadCount++;
                    const bgClass = noti.read_at ? 'hover:bg-white/5' : 'bg-cyan-500/10 hover:bg-cyan-500/20';
                    const icon = noti.data.icon || 'fas fa-bell';
                    const title = noti.data.title || 'Thông báo mới';
                    const message = noti.data.message || '';
                    const link = noti.data.link || '#';
                    const time = new Date(noti.created_at).toLocaleString('vi-VN');

                    const item = `
                        <a href="javascript:void(0)" onclick="readCustomerNotification('${noti.id}', '${link}')" class="flex gap-4 p-4 border-b border-white/5 transition cursor-pointer ${bgClass}">
                            <div class="w-10 h-10 rounded-full ${!noti.read_at ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-500/30' : 'bg-white/10 text-slate-400'} flex items-center justify-center shrink-0">
                                <i class="${icon}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-white mb-1 ${!noti.read_at ? 'text-cyan-400' : ''}">${title}</h4>
                                <p class="text-xs text-slate-400 line-clamp-2 mb-1">${message}</p>
                                <span class="text-[10px] ${!noti.read_at ? 'text-cyan-500' : 'text-slate-500'} font-mono">${time}</span>
                            </div>
                        </a>
                    `;
                    list.innerHTML += item;
                });

                if (custUnreadCount > 0) {
                    badge.innerText = custUnreadCount > 9 ? '9+' : custUnreadCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(err => console.error("Notification Polling Error", err));
        }

        function readCustomerNotification(id, link) {
            fetch(`/customer/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => {
                if(link && link !== '#') {
                    window.location.href = link;
                } else {
                    fetchCustomerNotifications();
                }
            }).catch(err => {
                if(link && link !== '#') window.location.href = link;
            });
        }

        window.addEventListener('load', () => {
            fetchCustomerNotifications();
            custNotiPollInterval = setInterval(fetchCustomerNotifications, 10000);
        });
    </script>
    @endauth
</body>
</html>
