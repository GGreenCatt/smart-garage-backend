<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Nhân viên') - Smart Garage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
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
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-link {
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
        }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.12) 0%, transparent 100%);
            border-left: 3px solid #6366f1;
            color: #4f46e5;
            font-weight: 800;
        }
        .dark .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.22) 0%, transparent 100%);
            color: #fff;
        }
        .sidebar-link:hover:not(.active) {
            background: rgba(99, 102, 241, 0.08);
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, .55); border-radius: 999px; }
        @keyframes staff-sos-pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, .72); }
            50% { transform: scale(1.12); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }
        @keyframes staff-sos-link-flash {
            0%, 100% { background: rgba(239, 68, 68, .10); }
            50% { background: rgba(239, 68, 68, .24); }
        }
        .staff-sos-alert-active {
            animation: staff-sos-link-flash 1s ease-in-out infinite;
            color: #ef4444 !important;
        }
        .staff-sos-alert-badge {
            display: none;
        }
        .staff-sos-alert-active .staff-sos-alert-badge {
            display: inline-flex;
            animation: staff-sos-pulse .9s ease-in-out infinite;
        }
        .staff-sos-swal-popup {
            cursor: pointer;
            border: 1px solid rgba(239, 68, 68, .28) !important;
            box-shadow: 0 18px 45px rgba(127, 29, 29, .28) !important;
            border-radius: 1.35rem !important;
            box-sizing: border-box !important;
            width: 13.5rem !important;
            max-width: calc(100vw - 1rem) !important;
            overflow: visible !important;
            padding: .9rem 1rem !important;
            transition: width .22s ease, transform .22s ease, box-shadow .22s ease !important;
        }
        .staff-sos-swal-popup:hover,
        .staff-sos-swal-popup:focus-within {
            width: min(30rem, calc(100vw - 1rem)) !important;
            box-shadow: 0 22px 55px rgba(127, 29, 29, .34) !important;
        }
        .staff-sos-swal-popup .swal2-html-container {
            margin: 0 !important;
            overflow: visible !important;
            width: 100% !important;
        }
        .staff-sos-swal-popup .swal2-icon,
        .staff-sos-swal-popup .swal2-title {
            display: none !important;
        }
        .staff-sos-alert-compact {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-height: 3rem;
        }
        .staff-sos-alert-detail {
            display: none;
            min-width: 0;
            width: 100%;
        }
        .staff-sos-swal-popup:hover .staff-sos-alert-compact,
        .staff-sos-swal-popup:focus-within .staff-sos-alert-compact {
            display: none;
        }
        .staff-sos-swal-popup:hover .staff-sos-alert-detail,
        .staff-sos-swal-popup:focus-within .staff-sos-alert-detail {
            display: block;
        }
        .staff-sos-alert-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .staff-sos-alert-action {
            white-space: normal;
            overflow-wrap: anywhere;
        }
        @media (min-width: 768px) {
            .staff-sos-swal-container {
                left: 16rem !important;
                width: calc(100vw - 16rem) !important;
                padding: 1rem !important;
            }
            .staff-sos-swal-popup:hover,
            .staff-sos-swal-popup:focus-within {
                width: min(30rem, calc(100vw - 18rem)) !important;
            }
        }

        @media (max-width: 767px) {
            html, body { overflow-x: hidden; }
            input, select, textarea { font-size: 16px !important; }
            button, a, input, select, textarea { -webkit-tap-highlight-color: transparent; }
            table { min-width: 720px; }
            dialog { width: calc(100vw - 1rem) !important; max-width: calc(100vw - 1rem) !important; }
            .swal2-popup { width: calc(100vw - 1rem) !important; max-height: calc(100vh - 1rem); overflow-y: auto; }
            .staff-sos-swal-popup {
                width: 11.75rem !important;
                max-height: none !important;
                border-radius: 1.15rem !important;
            }
            .staff-sos-swal-popup:hover,
            .staff-sos-swal-popup:focus-within {
                width: calc(100vw - 1rem) !important;
            }
            .staff-mobile-scroll { -webkit-overflow-scrolling: touch; }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen overflow-x-hidden flex font-sans antialiased selection:bg-indigo-500 selection:text-white bg-gray-50 text-slate-900 dark:bg-[#020617] dark:text-slate-200 transition-colors duration-300">
    <div id="sidebarBackdrop" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 z-50 flex w-[18rem] max-w-[86vw] -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-300 md:w-64 md:max-w-none dark:border-[#1e293b] dark:bg-[#0B1120]">
        <div class="h-16 md:h-20 flex items-center px-5 md:px-6 border-b border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/20 mr-3">
                SG
            </div>
            <div class="min-w-0">
                <h1 class="font-heading font-bold text-slate-900 dark:text-white text-lg leading-tight truncate">Smart Garage</h1>
                <div class="text-[10px] uppercase tracking-[0.2em] text-indigo-400 font-bold">Cổng nhân viên</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-1 no-scrollbar">
            <div class="px-3 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng quan</div>
            <a href="{{ route('staff.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="fas fa-columns w-6 text-center"></i>
                <span class="font-medium">Bảng Công Việc</span>
            </a>

            <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Khách hàng</div>
            <a href="{{ route('staff.appointments.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check w-6 text-center"></i>
                <span class="font-medium">Lịch Hẹn</span>
            </a>
            <a href="{{ route('staff.customers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.customers.*') ? 'active' : '' }}">
                <i class="fas fa-users w-6 text-center"></i>
                <span class="font-medium">Khách Hàng</span>
            </a>

            <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Vận hành</div>
            <a href="{{ route('staff.inventory.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes w-6 text-center"></i>
                <span class="font-medium">Kho Linh Kiện</span>
            </a>
            <a href="{{ route('staff.requests.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.requests.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check w-6 text-center"></i>
                <span class="font-medium">Yêu Cầu Vật Tư</span>
            </a>
            <a href="{{ route('staff.sos.index') }}" id="staffSosSidebarLink" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.sos.*') ? 'active' : '' }}">
                <i class="fas fa-truck-medical w-6 text-center"></i>
                <span class="font-medium text-red-500 dark:text-red-400">Cứu Hộ (SOS)</span>
                <span id="staffSosSidebarBadge" class="staff-sos-alert-badge ml-auto size-6 items-center justify-center rounded-full bg-red-600 text-xs font-black text-white shadow-lg shadow-red-500/30">!</span>
            </a>

            <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Cá nhân</div>
            <a href="{{ route('staff.chat.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.chat.*') ? 'active' : '' }}">
                <i class="fas fa-comments w-6 text-center"></i>
                <span class="font-medium">Tin Nhắn</span>
            </a>
            @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
            <a href="{{ route('staff.notifications.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell w-6 text-center"></i>
                <span class="font-medium">Thông Báo</span>
            </a>
            @endif
            <a href="{{ route('staff.profile') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-6 text-center"></i>
                <span class="font-medium">Hồ Sơ Của Tôi</span>
            </a>
        </nav>

        <div class="p-4 border-t border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#1e293b] transition">
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Staff' }}&background=6366f1&color=fff" class="w-10 h-10 rounded-full border border-gray-200 dark:border-slate-600">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'Nhân viên' }}</p>
                    <p class="text-xs text-indigo-500 dark:text-indigo-400 capitalize">{{ auth()->user()->role === 'staff' ? 'Chuyên viên' : 'Nhân viên' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 dark:text-slate-500 dark:hover:text-red-400 transition"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <main id="mainContent" class="min-w-0 flex-1 md:ml-64 min-h-screen bg-gray-50 dark:bg-[#020617] relative flex flex-col overflow-x-hidden transition-all duration-300">
        @unless(View::hasSection('no_header'))
        <header class="h-16 md:h-20 flex items-center justify-between gap-3 px-4 md:px-8 border-b border-gray-200 dark:border-[#1e293b] bg-white/90 dark:bg-[#0B1120]/90 backdrop-blur-md sticky top-0 z-40 transition-colors duration-300">
            <div class="flex min-w-0 items-center gap-3 md:gap-4">
                <button id="sidebarToggle" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-gray-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-icons-round">menu</span>
                </button>
                <h2 class="truncate text-lg md:text-xl font-heading font-bold text-slate-800 dark:text-white">@yield('title')</h2>
            </div>
            <div class="flex shrink-0 items-center gap-2 md:gap-4">
                <button onclick="toggleTheme()" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1e293b] text-amber-500 dark:text-indigo-400 hover:bg-gray-200 dark:hover:bg-slate-700 transition flex items-center justify-center">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline-block"></i>
                </button>

                @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
                <div class="relative" id="notification-dropdown-container">
                    <button onclick="toggleNotifications()" class="relative w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1e293b] text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-slate-700 transition flex items-center justify-center">
                        <i class="fas fa-bell"></i>
                        <span id="nav-unread-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center hidden border-2 border-white dark:border-[#0B1120] shadow-sm">0</span>
                    </button>
                    <div id="notification-dropdown" class="fixed left-3 right-3 top-16 md:absolute md:left-auto md:right-0 md:top-auto mt-3 md:w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-slate-100 dark:border-slate-700 overflow-hidden transform scale-95 opacity-0 pointer-events-none transition-all duration-200 z-50 origin-top-right flex flex-col max-h-[70vh] md:max-h-[400px]">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/80">
                            <h3 class="font-bold text-slate-800 dark:text-white"><i class="fas fa-bell mr-2 text-indigo-500"></i> Thông Báo</h3>
                        </div>
                        <div id="notification-list" class="flex-1 overflow-y-auto w-full custom-scrollbar">
                            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                                <i class="fas fa-circle-notch fa-spin text-3xl mb-3 opacity-30"></i>
                                <p class="text-sm">Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="hidden h-8 w-px bg-gray-200 dark:bg-[#1e293b] md:block"></div>
                <span class="hidden text-sm font-bold text-slate-500 dark:text-slate-400 md:inline">Ngày {{ date('d/m/Y') }}</span>
            </div>
        </header>
        @endunless

        @hasSection('full-width-content')
            @yield('full-width-content')
        @else
            <div class="min-w-0 flex-1 overflow-y-auto p-4 pb-24 md:p-8 md:pb-8 staff-mobile-scroll">
                @yield('content')
            </div>
        @endif
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-2 py-2 shadow-[0_-12px_30px_rgba(15,23,42,0.08)] backdrop-blur md:hidden dark:border-slate-800 dark:bg-[#0B1120]/95">
        <div class="grid grid-cols-5 gap-1">
            <a href="{{ route('staff.dashboard') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 text-[11px] font-bold {{ request()->routeIs('staff.dashboard') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas fa-columns text-base"></i><span>Bảng</span>
            </a>
            <a href="{{ route('staff.appointments.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 text-[11px] font-bold {{ request()->routeIs('staff.appointments.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas fa-calendar-check text-base"></i><span>Lịch</span>
            </a>
            <a href="{{ route('staff.customers.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 text-[11px] font-bold {{ request()->routeIs('staff.customers.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas fa-users text-base"></i><span>Khách</span>
            </a>
            <a href="{{ route('staff.chat.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 text-[11px] font-bold {{ request()->routeIs('staff.chat.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas fa-comments text-base"></i><span>Chat</span>
            </a>
            <a href="{{ route('staff.profile') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 text-[11px] font-bold {{ request()->routeIs('staff.profile') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas fa-user-circle text-base"></i><span>Hồ sơ</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
    <script>
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
            Toast.fire({ icon: 'success', title: @js(session('success')) });
        @endif
        @if(session('error'))
            Toast.fire({ icon: 'error', title: @js(session('error')) });
        @endif
        @if(session('warning'))
            Toast.fire({ icon: 'warning', title: @js(session('warning')) });
        @endif
        @if(session('info'))
            Toast.fire({ icon: 'info', title: @js(session('info')) });
        @endif

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notification-dropdown');
            if (!dropdown) return;
            if (dropdown.classList.contains('scale-95')) {
                dropdown.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.add('scale-100', 'opacity-100');
            } else {
                dropdown.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                dropdown.classList.remove('scale-100', 'opacity-100');
            }
        }

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
                    if (!list || !badge) return;

                    list.innerHTML = '';
                    let unreadCount = 0;

                    if (!data.length) {
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

                        list.innerHTML += `
                            <a href="javascript:void(0)" onclick="readNotification('${noti.id}', '${link}')" class="flex gap-4 p-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition cursor-pointer ${bgClass}">
                                <div class="w-10 h-10 rounded-full ${!noti.read_at ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'} flex items-center justify-center shrink-0">
                                    <i class="${icon}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1 ${!noti.read_at ? 'text-indigo-600 dark:text-indigo-400' : ''}">${title}</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mb-1">${message}</p>
                                    <span class="text-[10px] ${!noti.read_at ? 'text-indigo-500' : 'text-slate-400'} font-mono">${time}</span>
                                </div>
                            </a>`;
                    });

                    if (unreadCount > 0) {
                        badge.innerText = unreadCount > 9 ? '9+' : unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(err => console.error('Notification polling error', err));
        }

        function readNotification(id, link) {
            fetch(`/staff/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => {
                if (link && link !== '#') window.location.href = link;
                else fetchNotifications();
            }).catch(() => {
                if (link && link !== '#') window.location.href = link;
            });
        }

        @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
        window.addEventListener('load', () => {
            fetchNotifications();
            setInterval(fetchNotifications, 10000);
        });
        @endif

        const staffSosSidebarLink = document.getElementById('staffSosSidebarLink');
        let staffSosActiveItems = [];
        let staffSosAlertVisible = false;
        let staffSosAlertNavigating = false;
        let staffSosAlertPaused = false;

        function staffSosIsOwnAlertVisible() {
            return Boolean(Swal.isVisible() && document.querySelector('.staff-sos-swal-popup'));
        }

        window.addEventListener('staff-sos-alert:pause', () => {
            staffSosAlertPaused = true;
            if (staffSosIsOwnAlertVisible()) {
                Swal.close();
            }
        });

        window.addEventListener('staff-sos-alert:resume', () => {
            staffSosAlertPaused = false;
            if (staffSosActiveItems.length > 0) {
                setTimeout(() => staffSosRenderAlert(staffSosActiveItems), 250);
            }
        });

        function staffSosOpenTarget() {
            if (!staffSosActiveItems.length) return;
            staffSosAlertNavigating = true;
            const target = staffSosActiveItems[0].url || "{{ route('staff.sos.index') }}";
            window.location.href = target;
        }

        function staffSosSetSidebarState(hasPending) {
            if (!staffSosSidebarLink) return;
            staffSosSidebarLink.classList.toggle('staff-sos-alert-active', hasPending);
        }

        function staffSosRenderAlert(items) {
            staffSosActiveItems = items || [];
            const hasPending = staffSosActiveItems.length > 0;
            staffSosSetSidebarState(hasPending);

            if (staffSosAlertPaused) {
                return;
            }

            if (!hasPending) {
                if (staffSosIsOwnAlertVisible()) {
                    Swal.close();
                    staffSosAlertVisible = false;
                }
                return;
            }

            const first = staffSosActiveItems[0];
            const more = staffSosActiveItems.length > 1 ? ` +${staffSosActiveItems.length - 1} yêu cầu khác` : '';
            const html = `
                <div class="text-left">
                    <div class="staff-sos-alert-compact">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-base font-black text-white shadow-lg shadow-red-500/30 animate-pulse">!</span>
                        <div class="min-w-0">
                            <div class="staff-sos-alert-title text-sm font-black text-red-600">SOS mới${staffSosActiveItems.length > 1 ? ` (${staffSosActiveItems.length})` : ''}</div>
                            <div class="staff-sos-alert-title text-xs font-bold text-slate-600">${first.display_name || 'Khách hàng'}</div>
                        </div>
                    </div>
                    <div class="staff-sos-alert-detail">
                        <div class="mb-2 flex items-center gap-2 text-red-600">
                            <span class="inline-flex size-8 items-center justify-center rounded-full bg-red-600 text-white animate-pulse">!</span>
                            <span class="min-w-0 text-xs font-black uppercase tracking-wide leading-4">SOS khẩn cấp${more}</span>
                        </div>
                        <div class="text-sm font-black text-slate-900">${first.display_name || 'Khách hàng'}</div>
                        <div class="mt-1 text-xs font-semibold text-slate-600">${first.vehicle || 'Xe ngoài hệ thống'} · ${first.display_phone || 'Không có SĐT'}</div>
                        <div class="mt-2 line-clamp-2 text-xs text-slate-500">${first.description || 'Không có mô tả.'}</div>
                        <div class="staff-sos-alert-action mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-700">Bấm vào thông báo để mở yêu cầu.</div>
                    </div>
                </div>
            `;

            if (staffSosIsOwnAlertVisible()) {
                Swal.update({ html });
                return;
            }

            if (Swal.isVisible()) {
                return;
            }

            staffSosAlertVisible = true;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'Yêu cầu cứu hộ mới',
                html,
                showConfirmButton: false,
                showCloseButton: false,
                timer: undefined,
                timerProgressBar: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    container: 'staff-sos-swal-container',
                    popup: 'staff-sos-swal-popup'
                },
                didOpen: (popup) => {
                    popup.addEventListener('click', staffSosOpenTarget);
                },
                willClose: () => {
                    staffSosAlertVisible = false;
                    if (!staffSosAlertNavigating && staffSosActiveItems.length > 0) {
                        setTimeout(() => staffSosRenderAlert(staffSosActiveItems), 300);
                    }
                }
            });
        }

        function staffSosFetchPending() {
            fetch("{{ route('staff.sos.pending-alert') }}", {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) return;
                    staffSosRenderAlert(data.items || []);
                })
                .catch(error => console.error('SOS alert polling error', error));
        }

        window.addEventListener('load', () => {
            staffSosFetchPending();
            setInterval(staffSosFetchPending, 10000);
        });

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent') || document.querySelector('main');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function isMobileViewport() {
            return window.matchMedia('(max-width: 767px)').matches;
        }

        function setSidebarState(isOpen, options = {}) {
            if (!sidebar) return;
            const persist = options.persist ?? !isMobileViewport();

            if (isOpen) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.setAttribute('aria-hidden', 'false');
                if (sidebarBackdrop) sidebarBackdrop.classList.toggle('hidden', !isMobileViewport());
                if (!isMobileViewport() && mainContent) mainContent.classList.add('md:ml-64');
                document.body.dataset.sidebar = 'open';
                document.body.classList.toggle('overflow-hidden', isMobileViewport());
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.setAttribute('aria-hidden', 'true');
                if (sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
                if (mainContent) mainContent.classList.remove('md:ml-64');
                document.body.dataset.sidebar = 'closed';
                document.body.classList.remove('overflow-hidden');
            }

            if (persist) {
                localStorage.setItem('sidebarOpen', isOpen ? 'true' : 'false');
            }
        }

        window.setSidebarState = setSidebarState;

        const initialSidebarOpen = isMobileViewport() ? false : localStorage.getItem('sidebarOpen') !== 'false';
        setSidebarState(initialSidebarOpen, { persist: false });

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => setSidebarState(sidebar.classList.contains('-translate-x-full'), { persist: true }));
        }
        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', () => setSidebarState(false));
        }
        if (sidebar) {
            sidebar.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (isMobileViewport()) setSidebarState(false);
                });
            });
        }
        window.addEventListener('resize', () => {
            if (isMobileViewport()) setSidebarState(false);
            else setSidebarState(localStorage.getItem('sidebarOpen') !== 'false', { persist: false });
        });
    </script>
</body>
</html>
