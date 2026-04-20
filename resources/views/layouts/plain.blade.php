<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Smart Garage')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        slate: { 850: '#1e293b', 900: '#0f172a' },
                        teal: { 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background: #F8FAFC; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        @yield('styles')
    </style>
</head>
<body class="min-h-screen flex flex-col overflow-hidden @yield('bg_class', 'bg-slate-50')">
    <div class="@yield('main_class', 'w-full h-full flex-1 relative')">
        @yield('content')
    </div>
    
    <!-- Toast Component -->
    <div id="toast" class="fixed top-6 right-6 bg-slate-900 text-white px-5 py-3.5 rounded-xl shadow-2xl transform translate-x-20 opacity-0 transition-all z-[60] flex items-center gap-3 border border-slate-800">
        <div class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center text-xs text-white font-bold"><i class="fas fa-check"></i></div>
        <span id="toastMsg" class="font-medium text-sm">Action Successful</span>
    </div>

    <script>
        function showToast(msg) {
            const t = document.getElementById('toast');
            if(t && document.getElementById('toastMsg')) {
                document.getElementById('toastMsg').textContent = msg;
                t.classList.remove('translate-x-20', 'opacity-0');
                setTimeout(() => t.classList.add('translate-x-20', 'opacity-0'), 3000);
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
