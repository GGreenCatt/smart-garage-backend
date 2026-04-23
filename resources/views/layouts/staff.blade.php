<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Admin') - Smart Garage Executive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        .material-icons-round {
            font-family: 'Material Icons Round';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        slate: { 850: '#1e293b', 900: '#0f172a', 950: '#020617' },
                        indigo: { 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 900: '#312e81' },
                        gold: { 400: '#fbbf24', 500: '#f59e0b' }
                    }
                }
            }
        }
    </script>
    <style>
        /* body { background: #020617; color: #e2e8f0; } Removed to perform locally */
        .glass-panel { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-link {
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, transparent 100%);
            border-left: 3px solid #6366f1;
            color: #4f46e5; /* Indigo 600 */
            font-weight: 700;
        }
        .dark .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.2) 0%, transparent 100%);
            color: #fff;
        }
        .sidebar-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.03);
            padding-left: 1.25rem;
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex font-sans antialiased selection:bg-indigo-500 selection:text-white bg-gray-50 text-slate-900 dark:bg-[#020617] dark:text-slate-200 transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-[#0B1120] border-r border-gray-200 dark:border-[#1e293b] flex flex-col fixed inset-y-0 z-50 transition-transform duration-300 md:translate-x-0 -translate-x-full" id="sidebar">
        <!-- Logo -->
        <!-- Logo -->
        <div class="h-20 flex items-center px-6 border-b border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/20 mr-3">
                SG
            </div>
            <div>
                <h1 class="font-heading font-bold text-white text-lg leading-tight">Smart Garage</h1>
                <div class="text-[10px] uppercase tracking-[0.2em] text-indigo-400 font-bold">Cổng Quản Trị Viên</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 no-scrollbar">
            <div class="px-3 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Quan</div>
            
            <a href="{{ route('staff.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="fas fa-columns w-6 text-center"></i>
                <span class="font-medium">Bảng Công Việc</span>
            </a>

            <div class="px-4 py-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 px-2">Khách Hàng</p>
                <a href="{{ route('staff.appointments.index') }}" class="flex items-center gap-3 px-2 py-2.5 rounded-lg {{ request()->routeIs('staff.appointments.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-600 hover:bg-slate-50' }} transition">
                    <i class="fas fa-calendar-check w-5 text-center"></i>
                    <span>Lịch Hẹn</span>
                </a>
                <a href="{{ route('staff.customers.index') }}" class="flex items-center gap-3 px-2 py-2.5 rounded-lg {{ request()->routeIs('staff.customers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-600 hover:bg-slate-50' }} transition">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span>Khách Hàng</span>
                </a>
            </div>

            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Vận Hành</div>

            <a href="{{ route('staff.inventory.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes w-6 text-center"></i>
                <span class="font-medium">Kho Linh Kiện</span>
            </a>

            <a href="{{ route('staff.requests.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.requests.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check w-6 text-center"></i>
                <span class="font-medium">Yêu Cầu Vật Tư</span>
            </a>

             <a href="{{ route('staff.customers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.customers.*') ? 'active' : '' }}">
                <i class="fas fa-users w-6 text-center"></i>
                <span class="font-medium">Khách Hàng</span>
            </a>

             <a href="{{ route('staff.sos.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.sos.*') ? 'active' : '' }}">
                <i class="fas fa-truck-medical w-6 text-center"></i>
                <span class="font-medium text-red-500 dark:text-red-400">Cứu Hộ (SOS)</span>
            </a>

            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Cá Nhân</div>


            
            <a href="{{ route('staff.chat.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.chat.*') ? 'active' : '' }}">
                <i class="fas fa-comments w-6 text-center"></i>
                <span class="font-medium">Tin Nhắn</span>
            </a>

            @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
            <a href="{{ route('staff.notifications.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell w-6 text-center"></i>
                <span class="font-medium">Thông Báo</span>
            </a>
            @endif

            <a href="{{ route('staff.profile') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-6 text-center"></i>
                <span class="font-medium">Hồ Sơ Của Tôi</span>
            </a>
        </nav>

        <!-- Profile -->
        <div class="p-4 border-t border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#1e293b] transition cursor-pointer">
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&background=6366f1&color=fff" class="w-10 h-10 rounded-full border border-gray-200 dark:border-slate-600">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'Quản trị viên' }}</p>
                    <p class="text-xs text-indigo-500 dark:text-indigo-400 capitalize">{{ auth()->user()->role === 'staff' ? 'Chuyên viên' : 'Quản trị viên' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 dark:text-slate-500 dark:hover:text-red-400 transition"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Content -->
    <main id="mainContent" class="flex-1 md:ml-64 min-h-screen bg-gray-50 dark:bg-[#020617] relative flex flex-col transition-all duration-300">
        @unless(View::hasSection('no_header'))
        <!-- Header -->
        <header class="h-20 flex items-center justify-between px-8 border-b border-gray-200 dark:border-[#1e293b] bg-white/80 dark:bg-[#0B1120]/80 backdrop-blur-md sticky top-0 z-40 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-gray-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                    <!-- Hamburger Icon -->
                    <span class="material-icons-round">menu</span>
                </button>
                <h2 class="text-xl font-heading font-bold text-slate-800 dark:text-white">@yield('title')</h2>
            </div>
            <div class="flex items-center gap-4">
                <!-- Dark Mode Toggle -->
                <button onclick="toggleTheme()" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1e293b] text-amber-500 dark:text-indigo-400 hover:bg-gray-200 dark:hover:bg-slate-700 transition flex items-center justify-center">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline-block"></i>
                </button>

                @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
                <!-- Notifications Dropdown -->
                <div class="relative" id="notification-dropdown-container">
                    <button onclick="toggleNotifications()" class="relative w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1e293b] text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-slate-700 transition flex items-center justify-center">
                        <i class="fas fa-bell"></i>
                        <span id="nav-unread-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center hidden border-2 border-white dark:border-[#0B1120] shadow-sm">0</span>
                    </button>

                    <!-- Dropdown Content -->
                    <div id="notification-dropdown" class="absolute right-0 mt-3 w-80 md:w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-slate-100 dark:border-slate-700 overflow-hidden transform scale-95 opacity-0 pointer-events-none transition-all duration-200 z-50 origin-top-right flex flex-col max-h-[400px]">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/80">
                            <h3 class="font-bold text-slate-800 dark:text-white"><i class="fas fa-bell mr-2 text-indigo-500"></i> Thông Báo</h3>
                        </div>
                        <div id="notification-list" class="flex-1 overflow-y-auto w-full custom-scrollbar">
                            <!-- JS Injects Notifications Here -->
                            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                                <i class="fas fa-circle-notch fa-spin text-3xl mb-3 opacity-30"></i>
                                <p class="text-sm">Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="h-8 w-px bg-gray-200 dark:bg-[#1e293b]"></div>
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Thứ {{ date('w') == 0 ? 'Nhật' : date('w') + 1 }}, ngày {{ date('d/m/Y') }}</span>
            </div>
        </header>
        @endunless

        @hasSection('full-width-content')
            @yield('full-width-content')
        @else
            <div class="p-8 flex-1 overflow-y-auto">
                @yield('content')
            </div>
        @endif
    </main>

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

        // Dark Mode Logic
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Init Theme
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // --- Notification Logic ---
        let notiPollInterval = null;
        let unreadCount = 0;

        function toggleNotifications() {
            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown.classList.contains('scale-95')) {
                dropdown.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.add('scale-100', 'opacity-100');
            } else {
                dropdown.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.remove('scale-100', 'opacity-100');
            }
        }

        // Close dropdown when click outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('notification-dropdown-container');
            if (container && !container.contains(e.target)) {
                const dropdown = document.getElementById('notification-dropdown');
                if (dropdown) {
                    dropdown.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                    dropdown.classList.remove('scale-100', 'opacity-100');
                }
            }
        });

        function fetchNotifications() {
            fetch("{{ route('staff.notifications.index') }}")
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('notification-list');
                const badge = document.getElementById('nav-unread-badge');
                if (!list) return;

                list.innerHTML = '';
                unreadCount = 0;

                if (data.length === 0) {
                    list.innerHTML = `<div class="p-8 text-center text-slate-500 dark:text-slate-400"><i class="fas fa-check-circle text-4xl mb-3 opacity-20"></i><p class="text-sm">Bạn không có thông báo nào</p></div>`;
                    badge.classList.add('hidden');
                    return;
                }

                data.forEach(noti => {
                    if (!noti.read_at) unreadCount++;
                    const bgClass = noti.read_at ? 'bg-white dark:bg-slate-800' : 'bg-indigo-50/50 dark:bg-indigo-900/10';
                    const icon = noti.data.icon || 'fas fa-bell';
                    const title = noti.data.title || 'Thông báo mới';
                    const message = noti.data.message || '';
                    const link = noti.data.link || '#';
                    const time = new Date(noti.created_at).toLocaleString('vi-VN');

                    const item = `
                        <a href="javascript:void(0)" onclick="readNotification('${noti.id}', '${link}')" class="flex gap-4 p-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition cursor-pointer ${bgClass}">
                            <div class="w-10 h-10 rounded-full ${!noti.read_at ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'} flex items-center justify-center shrink-0">
                                <i class="${icon}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1 ${!noti.read_at ? 'text-indigo-600 dark:text-indigo-400' : ''}">${title}</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mb-1">${message}</p>
                                <span class="text-[10px] ${!noti.read_at ? 'text-indigo-500' : 'text-slate-400'} font-mono">${time}</span>
                            </div>
                        </a>
                    `;
                    list.innerHTML += item;
                });

                if (unreadCount > 0) {
                    badge.innerText = unreadCount > 9 ? '9+' : unreadCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(err => console.error("Notification Polling Error", err));
        }

        function readNotification(id, link) {
            fetch(`/staff/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => {
                if(link && link !== '#') {
                    window.location.href = link;
                } else {
                    fetchNotifications();
                }
            }).catch(err => {
                // If it fails (e.g., token expired), attempt redirect anyway if link exists
                if(link && link !== '#') window.location.href = link;
            });
        }

        @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
        window.addEventListener('load', () => {
            fetchNotifications();
            notiPollInterval = setInterval(fetchNotifications, 10000); // 10s polling
        });
        @endif

        // Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent') || document.querySelector('main');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        function setSidebarState(isOpen) {
            if (isOpen) {
                // Open: Add ml-64 to main, Show Sidebar
                sidebar.classList.remove('-translate-x-full'); 
                sidebar.classList.add('md:translate-x-0'); // Ensure it's shown on desktop
                if(mainContent) mainContent.classList.add('md:ml-64');
            } else {
                // Close: Remove ml-64, Hide Sidebar
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('md:translate-x-0'); // Ensure it's hidden on desktop
                if(mainContent) mainContent.classList.remove('md:ml-64');
            }
            localStorage.setItem('sidebarOpen', isOpen);
        }

        // Initialize Sidebar
        const isSidebarOpen = localStorage.getItem('sidebarOpen') !== 'false';
        setSidebarState(isSidebarOpen);

        if(sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                // Check if currently hidden (mobile logic or desktop logic need to be unified)
                // If it has -translate-x-full, it is hidden.
                // If it DOES NOT have md:translate-x-0 (on desktop), it is hidden.
                const isHidden = sidebar.classList.contains('-translate-x-full');
                setSidebarState(isHidden);
            });
        }
    </script>
</body>
</html>
