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
</head>
<body class="min-h-screen flex font-sans antialiased selection:bg-indigo-500 selection:text-white bg-gray-50 text-slate-900 dark:bg-[#020617] dark:text-slate-200 transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-[#0B1120] border-r border-gray-200 dark:border-[#1e293b] flex flex-col fixed inset-y-0 z-50 transition-transform duration-300 md:translate-x-0 -translate-x-full" id="sidebar">
        <!-- Logo -->
        <div class="h-20 flex items-center px-6 border-b border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/20 mr-3">
                SG
            </div>
            <div>
                <h1 class="font-heading font-bold text-white text-lg leading-tight">Smart Garage</h1>
                <div class="text-[10px] uppercase tracking-[0.2em] text-indigo-400 font-bold">Executive</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 no-scrollbar">
            <div class="px-3 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Quan</div>
            
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie w-6 text-center"></i>
                <span class="font-medium">Tổng Quan</span>
            </a>

            @if(auth()->user()->isAdmin() && session('admin_view_mode') != 'manager')
            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Hệ Thống & Cài Đặt</div>
            <a href="{{ route('admin.settings.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cogs w-6 text-center"></i>
                <span class="font-medium">Cấu Hình Hệ Thống</span>
            </a>
            
            <a href="{{ route('admin.roles.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="fas fa-cog w-6 text-center"></i>
                <span class="font-medium">Phân Quyền - Chức Vụ</span>
            </a>
            @endif
            
            @if(auth()->user()->isManager() || (auth()->user()->isAdmin() && session('admin_view_mode') == 'manager'))
            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Quản Lý Gara</div>
            
            <a href="{{ route('admin.staff.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.staff.index') ? 'active' : '' }}">
                <i class="fas fa-users-cog w-6 text-center"></i>
                <span class="font-medium">Nhân Sự</span>
            </a>
            
            <a href="{{ route('admin.customers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <i class="fas fa-user-tag w-6 text-center"></i>
                <span class="font-medium">Khách Hàng</span>
            </a>
            
            <a href="{{ route('admin.vehicles.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                <i class="fas fa-car w-6 text-center"></i>
                <span class="font-medium">Phương Tiện</span>
            </a>

            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Vận Hành</div>
            
            <a href="{{ route('admin.repair_orders.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.repair_orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-6 text-center"></i>
                <span class="font-medium">Lệnh Sửa Chữa</span>
            </a>
            
             <a href="{{ route('admin.inventory.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes w-6 text-center"></i>
                <span class="font-medium">Kho & Vật Tư</span>
            </a>

            <a href="{{ route('admin.requests.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check w-6 text-center"></i>
                <span class="font-medium">Phê Duyệt Vật Tư</span>
            </a>
            
            <a href="{{ route('admin.sos.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 dark:text-slate-400 font-medium hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.sos.*') ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt w-6 text-center"></i>
                <span class="font-medium">Bản Đồ Cứu Hộ Lưu Động</span>
            </a>

            <div class="px-3 mt-8 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Báo Cáo</div>
            
             <a href="{{ route('admin.staff.logs') }}" class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-indigo-600 dark:hover:text-white {{ request()->routeIs('admin.staff.logs') ? 'active' : '' }}">
                <i class="fas fa-history w-6 text-center"></i>
                <span class="font-medium">Nhật Ký Thao Tác</span>
            </a>
            @endif
        </nav>

        <!-- Profile -->
        <div class="p-4 border-t border-gray-200 dark:border-[#1e293b] bg-white dark:bg-[#0B1120]">
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#1e293b] transition cursor-pointer">
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&background=6366f1&color=fff" class="w-10 h-10 rounded-full border border-gray-200 dark:border-slate-600">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                    <p class="text-xs text-indigo-500 dark:text-indigo-400 capitalize">{{ auth()->user()->role ?? 'Staff' }}</p>
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

                @if(auth()->user()->isAdmin())
                <form action="{{ route('admin.toggle-view-mode') }}" method="POST" class="flex items-center ml-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-full {{ session('admin_view_mode') == 'manager' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-gray-100 dark:bg-[#1e293b] text-slate-500 hover:bg-gray-200 dark:hover:bg-slate-800' }} font-bold transition">
                        <i class="fas {{ session('admin_view_mode') == 'manager' ? 'fa-toggle-on text-sm' : 'fa-toggle-off text-sm' }}"></i> <span class="hidden sm:inline">Chế độ Quản Lý</span>
                    </button>
                </form>
                @endif

                @if(\App\Models\Setting::get('enable_notifications', '1') == '1')
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1e293b] text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-slate-700 transition flex items-center justify-center relative">
                    <i class="fas fa-bell"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                @endif

                <div class="h-8 w-px bg-gray-200 dark:bg-[#1e293b]"></div>
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ date('l, d M Y') }}</span>
            </div>
        </header>

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
