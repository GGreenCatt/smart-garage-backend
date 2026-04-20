@extends('layouts.customer')

@section('title', 'Smart Garage - Hệ Thống Sửa Chữa Xe Thông Minh')

@section('styles')
<style>
    .hero-bg { background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%); }
    .animated-grid {
        background-size: 50px 50px;
        background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                          linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
    }
    .float-animation { animation: float 6s ease-in-out infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    .feature-icon {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(59, 130, 246, 0.1));
        border: 1px solid rgba(6, 182, 212, 0.2);
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="relative min-h-screen pt-32 pb-20 px-6 flex items-center hero-bg overflow-hidden">
    <div class="absolute inset-0 animated-grid pointer-events-none"></div>
    <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-cyan-500/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-16 items-center relative z-10">
        <div class="space-y-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-900/30 border border-cyan-500/30 text-cyan-400 font-medium text-sm">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Hệ thống quản lý thông minh 4.0
            </div>
            <h1 class="text-5xl md:text-7xl font-black leading-tight text-white">
                Chăm Sóc Xe <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">Chuẩn Tương Lai</span>
            </h1>
            <p class="text-lg text-slate-400 max-w-xl leading-relaxed">
                Trải nghiệm dịch vụ sửa chữa minh bạch tuyệt đối với công nghệ 3D Visual Check, theo dõi tiến độ thời gian thực và báo cáo chi tiết trực quan.
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                <button onclick="openLoginModal()" class="px-8 py-4 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white rounded-xl font-bold text-lg shadow-xl shadow-cyan-900/30 transition transform hover:-translate-y-1">
                    Đặt Lịch Ngay
                </button>
                <a href="#features" class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white rounded-xl font-bold text-lg border border-white/10 transition backdrop-blur-md flex items-center justify-center">
                    Tìm Hiểu Thêm
                </a>
            </div>

            <div class="pt-8 border-t border-white/10 flex gap-8">
                <div> <div class="text-3xl font-black text-white">50+</div> <div class="text-sm text-slate-500">Điểm kiểm tra</div> </div>
                <div> <div class="text-3xl font-black text-white">100%</div> <div class="text-sm text-slate-500">Minh bạch giá</div> </div>
                <div> <div class="text-3xl font-black text-white">24/7</div> <div class="text-sm text-slate-500">Hỗ trợ khẩn cấp</div> </div>
            </div>
        </div>

        <div class="relative hidden md:block" style="perspective: 1000px;">
            <div class="float-animation relative z-10 w-full aspect-square rounded-3xl glass border-white/20 overflow-hidden flex items-center justify-center p-8 shadow-2xl shadow-cyan-900/20">
                <div class="text-center space-y-4">
                    <div class="text-6xl text-cyan-500 font-black tracking-widest ">3D TECH</div>
                    <div class="text-sm text-slate-400 uppercase tracking-widest">Visual Inspection System</div>
                    <div class="w-64 h-32 bg-gradient-to-r from-slate-800 to-slate-900 rounded-lg mx-auto border border-slate-700 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center text-slate-600 text-xs">[Mô hình xe 3D xoay vòng]</div>
                        <div class="absolute top-0 bottom-0 w-1 bg-cyan-500/50 shadow-[0_0_15px_rgba(6,182,212,0.8)] animate-[scan_2s_ease-in-out_infinite]"></div>
                    </div>
                </div>
            </div>

            <div class="absolute -top-6 -right-6 glass p-4 rounded-xl border-l-4 border-l-green-500 animate-pulse delay-700 z-20 shadow-xl">
                <div class="text-xs text-slate-400">Trạng thái</div>
                <div class="font-bold text-white">Đã hoàn thành 100%</div>
            </div>
            <div class="absolute bottom-6 -left-6 glass p-4 rounded-xl border-l-4 border-l-cyan-500 animate-pulse delay-1000 z-20 shadow-xl">
                <div class="text-xs text-slate-400">Kỹ thuật viên</div>
                <div class="font-bold text-white">Đang thực hiện kiểm tra...</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-24 bg-slate-900 relative">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16 space-y-4">
            <span class="text-cyan-500 font-bold tracking-wider text-sm">CÔNG NGHỆ TIÊN TIẾN</span>
            <h2 class="text-4xl font-black text-white">Giải Pháp Toàn Diện Cho Xe Của Bạn</h2>
            <p class="text-slate-400 max-w-2xl mx-auto">Chúng tôi áp dụng những công nghệ hiện đại nhất để đảm bảo sự chính xác, minh bạch và an toàn tuyệt đối.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="glass-card p-8 rounded-2xl group">
                <div class="w-14 h-14 rounded-xl feature-icon flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-cube text-2xl text-cyan-400"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-400 transition">3D VHC Technology</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">Công nghệ kiểm tra xe trực quan với mô hình 3D, đánh dấu chính xác vị trí lỗi và hiển thị trực tiếp trên giao diện người dùng.</p>
                <ul class="text-sm text-slate-500 space-y-2">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-cyan-500 text-xs"></i> Quan sát trực quan 360 độ</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-cyan-500 text-xs"></i> Đánh dấu lỗi chính xác</li>
                </ul>
            </div>
            <!-- Feature 2 -->
            <div class="glass-card p-8 rounded-2xl group">
                <div class="w-14 h-14 rounded-xl feature-icon flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-chart-line text-2xl text-blue-400"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-400 transition">Real-time Tracking</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">Theo dõi tiến độ sửa chữa theo thời gian thực. Cập nhật từng bước từ khi nhận xe đến khi hoàn thành.</p>
            </div>
             <!-- Feature 3 -->
             <div class="glass-card p-8 rounded-2xl group">
                <div class="w-14 h-14 rounded-xl feature-icon flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-file-invoice-dollar text-2xl text-green-400"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-400 transition">Minh Bạch Chi Phí</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">Hệ thống báo giá tự động và minh bạch. Khách hàng duyệt báo giá trước khi thực hiện sửa chữa.</p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section id="process" class="py-24 bg-[#0f172a] relative border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="space-y-8">
            <span class="text-cyan-500 font-bold tracking-wider text-sm">QUY TRÌNH CHUẨN</span>
            <h2 class="text-4xl font-black text-white">Đơn Giản Hóa <br> Quy Trình Sửa Chữa</h2>
            <div class="space-y-6">
                @foreach([
                    ['1', 'Đặt Lịch & Tiếp Nhận', 'Đặt lịch online hoặc đến trực tiếp. Staff tiếp nhận xe và khởi tạo hồ sơ điện tử.'],
                    ['2', 'Kiểm Tra 3D Visual Check', 'Kỹ thuật viên kiểm tra toàn diện, ghi nhận lỗi trực quan trên mô hình 3D.'],
                    ['3', 'Báo Giá & Phê Duyệt', 'Nhận báo giá chi tiết qua App/Link. Khách hàng xác nhận hạng mục sửa chữa.'],
                    ['4', 'Sửa Chữa & Bàn Giao', 'Theo dõi tiến độ realtime. Nhận xe và thanh toán minh bạch.']
                ] as $step)
                <div class="flex gap-4 group">
                    <div class="w-12 h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-cyan-500 font-bold text-lg group-hover:bg-cyan-500 group-hover:text-white transition shadow-lg shadow-cyan-900/10">{{ $step[0] }}</div>
                    <div>
                        <h4 class="text-white font-bold text-lg mb-1 group-hover:text-cyan-400 transition">{{ $step[1] }}</h4>
                        <p class="text-slate-400 text-sm">{{ $step[2] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative">
            <div class="absolute inset-0 bg-cyan-500/10 blur-3xl rounded-full pointer-events-none"></div>
            <img src="https://images.unsplash.com/photo-1530046339160-ce3e530c7d2f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Process" class="relative rounded-2xl border border-slate-700 shadow-2xl skew-y-3 hover:skew-y-0 transition duration-500 grayscale hover:grayscale-0 block opacity-80 hover:opacity-100">
            <div class="absolute -bottom-10 -left-10 glass p-6 rounded-xl border-t border-t-cyan-500 max-w-xs animate-bounce delay-[2000ms]">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-500"><i class="fas fa-magic"></i></div>
                    <div>
                        <div class="text-xs text-slate-400">Thời gian trung bình</div>
                        <div class="font-bold text-white text-lg">Giảm 40%</div>
                    </div>
                </div>
                <p class="text-xs text-slate-500">So với quy trình truyền thống nhờ công nghệ và quy trình tối ưu.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Us Section -->
<section id="why-us" class="py-24 bg-slate-900 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-gradient-to-r from-blue-900/40 to-cyan-900/40 rounded-3xl p-12 border border-white/10 relative overflow-hidden text-center">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 blur-[80px]"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-cyan-500/10 blur-[80px]"></div>
            <div class="relative z-10 max-w-3xl mx-auto space-y-8">
                <h2 class="text-3xl md:text-5xl font-black text-white">Bạn Đã Sẵn Sàng Trải Nghiệm?</h2>
                <p class="text-lg text-slate-300">Đừng để việc chăm sóc xe trở thành nỗi lo. Hãy để SmartGarage đồng hành cùng bạn trên mọi hành trình.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <button onclick="openLoginModal()" class="px-8 py-4 bg-white text-slate-900 rounded-xl font-bold text-lg hover:bg-slate-100 transition">Đăng Ký Thành Viên</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('modals')
<!-- Login/Register Modal -->
<div id="authModal" class="hidden fixed inset-0 bg-black/80 z-[100] flex items-center justify-center backdrop-blur-md p-4">
    <div class="bg-slate-900 rounded-2xl w-full max-w-md shadow-2xl border border-slate-700 overflow-hidden">
        <div class="flex border-b border-slate-700">
            <button onclick="switchAuthTab('login')" id="tabLogin" class="flex-1 py-4 text-center font-bold text-white border-b-2 border-cyan-500 bg-slate-800/50">Đăng Nhập</button>
            <button onclick="switchAuthTab('register')" id="tabRegister" class="flex-1 py-4 text-center font-bold text-slate-400 hover:text-white transition">Đăng Ký</button>
        </div>

        <div class="p-8">
            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                <h3 class="text-xl font-bold text-white text-center mb-6">Chào mừng trở lại!</h3>
                <div><label class="block text-sm font-medium text-slate-400 mb-1">Email hoặc Số điện thoại</label><input type="text" class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none transition" placeholder="Nhập email hoặc SĐT"></div>
                <div><label class="block text-sm font-medium text-slate-400 mb-1">Mật khẩu</label><input type="password" class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none transition"></div>
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white rounded-lg font-bold shadow-lg shadow-cyan-900/30 transition mt-4">Đăng Nhập</button>
            </form>

            <!-- Register Form -->
            <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-4 hidden">
                <h3 class="text-xl font-bold text-white text-center mb-6">Tạo tài khoản mới</h3>
                <div class="bg-blue-900/20 p-3 rounded-lg border border-blue-500/20 text-xs text-blue-300 mb-4"><i class="fas fa-history mr-1"></i> Tài khoản sẽ tự động đồng bộ tất cả lịch sử sửa chữa cũ dựa trên Số điện thoại.</div>
                <div><label class="block text-sm font-medium text-slate-400 mb-1">Họ và tên</label><input type="text" class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none transition"></div>
                <div><label class="block text-sm font-medium text-slate-400 mb-1">Số điện thoại</label><input type="tel" class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none transition"></div>
                <div><label class="block text-sm font-medium text-slate-400 mb-1">Mật khẩu</label><input type="password" class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none transition"></div>
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white rounded-lg font-bold shadow-lg shadow-cyan-900/30 transition mt-4">Đăng Ký Thành Viên</button>
            </form>
        </div>
        <div class="bg-slate-800/50 p-4 text-center border-t border-slate-700">
            <button onclick="closeAuthModal()" class="text-sm font-semibold text-slate-400 hover:text-white">Đóng</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function switchAuthTab(tab) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        if (tab === 'login') {
            loginForm.classList.remove('hidden'); registerForm.classList.add('hidden');
            tabLogin.className = 'flex-1 py-4 text-center font-bold text-white border-b-2 border-cyan-500 bg-slate-800/50';
            tabRegister.className = 'flex-1 py-4 text-center font-bold text-slate-400 hover:text-white transition';
        } else {
            loginForm.classList.add('hidden'); registerForm.classList.remove('hidden');
            tabLogin.className = 'flex-1 py-4 text-center font-bold text-slate-400 hover:text-white transition';
            tabRegister.className = 'flex-1 py-4 text-center font-bold text-white border-b-2 border-cyan-500 bg-slate-800/50';
        }
    }
    function handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const userInput = form.querySelector('input[type="text"]').value;
        const password = form.querySelector('input[type="password"]').value;

        fetch("{{ route('login') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: userInput, password: password })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                 Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Đăng nhập thành công!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                setTimeout(() => {
                    window.location.href = res.body.redirect;
                }, 1500);
            } else {
                 Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: res.body.message || 'Đăng nhập thất bại!',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        })
        .catch(err => {
            console.error(err);
             Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Lỗi kết nối!',
                showConfirmButton: false,
                timer: 3000
            });
        });
    }
    function handleRegister(e) {
        e.preventDefault();
        const form = e.target;
        const name = form.querySelector('input[type="text"]').value;
        const phone = form.querySelector('input[type="tel"]').value;
        const password = form.querySelector('input[type="password"]').value;

        fetch("{{ route('register') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, phone: phone, password: password })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                 Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Đăng ký thành công!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                setTimeout(() => {
                     window.location.href = res.body.redirect;
                }, 1500);
            } else {
                 Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: res.body.message || 'Đăng ký thất bại!',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Lỗi kết nối!',
                showConfirmButton: false,
                timer: 3000
            });
        });
    }

    document.getElementById('authModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('authModal')) closeAuthModal();
    });
</script>
@endpush
