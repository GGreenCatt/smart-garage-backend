@extends('layouts.customer')

@section('title', 'Smart Garage - Hệ Thống Sửa Chữa Xe Thông Minh')

@section('styles')
<style>
    html { scroll-behavior: smooth; }

    .home-surface {
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.98) 42%, #0f172a 100%),
            url('https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1800&q=85') center top / cover no-repeat;
    }

    .hero-panel {
        background: rgba(2, 6, 23, 0.62);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 28px 90px rgba(2, 6, 23, 0.42);
        backdrop-filter: blur(18px);
    }

    .stat-tile,
    .service-tile,
    .workflow-step,
    .trust-tile {
        background: rgba(15, 23, 42, 0.74);
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 18px 50px rgba(2, 6, 23, 0.24);
    }

    .service-tile,
    .workflow-step,
    .trust-tile {
        transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease;
    }

    .service-tile:hover,
    .workflow-step:hover,
    .trust-tile:hover {
        transform: translateY(-4px);
        border-color: color-mix(in srgb, var(--brand-primary) 52%, transparent);
        background: rgba(15, 23, 42, 0.9);
    }

    .brand-gradient-text {
        color: transparent;
        background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
        -webkit-background-clip: text;
        background-clip: text;
    }

    .brand-gradient-bg {
        background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
    }

    .vehicle-frame {
        min-height: 460px;
        background:
            linear-gradient(180deg, rgba(2, 6, 23, 0.18), rgba(2, 6, 23, 0.88)),
            url('https://images.unsplash.com/photo-1599256630445-67b5772b1204?auto=format&fit=crop&w=1100&q=85') center / cover no-repeat;
    }

    .scan-line {
        animation: scanLine 3.2s ease-in-out infinite;
    }

    @keyframes scanLine {
        0%, 100% { transform: translateX(0); opacity: 0.45; }
        50% { transform: translateX(310px); opacity: 1; }
    }

    @media (max-width: 767px) {
        .home-surface { background-position: 56% top; }
        .vehicle-frame { min-height: 340px; }
        @keyframes scanLine {
            0%, 100% { transform: translateX(0); opacity: 0.45; }
            50% { transform: translateX(220px); opacity: 1; }
        }
    }
</style>
@endsection

@section('content')
<main class="home-surface overflow-hidden">
    <section class="relative min-h-screen px-6 pt-28 pb-16 flex items-center">
        <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-[1.02fr_0.98fr] gap-10 xl:gap-14 items-center">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-slate-100 backdrop-blur-md">
                    <span class="h-2.5 w-2.5 rounded-full bg-brand-accent"></span>
                    Garage số hóa cho khách hàng hiện đại
                </div>

                <h1 class="mt-7 text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.04] text-white">
                    Chăm sóc xe minh bạch, nhanh gọn và dễ theo dõi.
                </h1>

                <p class="mt-6 max-w-2xl text-base sm:text-lg leading-8 text-slate-300">
                    Smart Garage giúp khách hàng đặt lịch, gửi yêu cầu cứu hộ, nhận báo giá và theo dõi tiến độ sửa chữa trên một cổng thông tin trực quan.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    @guest
                        <button onclick="openLoginModal()" class="inline-flex items-center justify-center gap-3 rounded-xl brand-gradient-bg px-6 py-4 text-base font-bold text-white shadow-xl shadow-cyan-950/40 transition hover:-translate-y-0.5">
                            <i class="fas fa-calendar-check"></i>
                            Đặt lịch / Đăng nhập
                        </button>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center justify-center gap-3 rounded-xl brand-gradient-bg px-6 py-4 text-base font-bold text-white shadow-xl shadow-cyan-950/40 transition hover:-translate-y-0.5">
                            <i class="fas fa-gauge-high"></i>
                            Vào bảng điều khiển
                        </a>
                    @endguest
                    <a href="{{ route('customer.sos.index') }}" class="inline-flex items-center justify-center gap-3 rounded-xl border border-red-400/35 bg-red-500/12 px-6 py-4 text-base font-bold text-red-100 transition hover:bg-red-500/20">
                        <i class="fas fa-truck-medical"></i>
                        Cứu hộ khẩn cấp
                    </a>
                    <a href="#features" class="inline-flex items-center justify-center gap-3 rounded-xl border border-white/15 bg-white/10 px-6 py-4 text-base font-bold text-white transition hover:bg-white/15">
                        <i class="fas fa-arrow-down"></i>
                        Xem dịch vụ
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-3 gap-3 sm:gap-4 max-w-2xl">
                    <div class="stat-tile rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-white">24/7</div>
                        <div class="mt-1 text-xs sm:text-sm text-slate-400">Cứu hộ</div>
                    </div>
                    <div class="stat-tile rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-white">3D</div>
                        <div class="mt-1 text-xs sm:text-sm text-slate-400">Kiểm tra xe</div>
                    </div>
                    <div class="stat-tile rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-white">100%</div>
                        <div class="mt-1 text-xs sm:text-sm text-slate-400">Rõ chi phí</div>
                    </div>
                </div>
            </div>

            <div class="hero-panel rounded-2xl p-4 sm:p-5">
                <div class="vehicle-frame relative overflow-hidden rounded-xl">
                    <div class="absolute inset-x-6 top-6 flex flex-wrap gap-3">
                        <div class="rounded-lg bg-slate-950/72 px-4 py-3 backdrop-blur">
                            <p class="text-xs text-slate-400">Tình trạng</p>
                            <p class="mt-1 font-bold text-white">Đang kiểm tra tổng quát</p>
                        </div>
                        <div class="rounded-lg bg-slate-950/72 px-4 py-3 backdrop-blur">
                            <p class="text-xs text-slate-400">Báo giá</p>
                            <p class="mt-1 font-bold text-brand-accent">Chờ phê duyệt</p>
                        </div>
                    </div>

                    <div class="absolute left-8 right-8 bottom-8">
                        <div class="rounded-xl bg-slate-950/78 p-5 backdrop-blur">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-400">Quy trình sửa chữa</p>
                                    <p class="mt-1 text-xl font-black text-white">Theo dõi realtime</p>
                                </div>
                                <div class="h-12 w-12 rounded-xl brand-gradient-bg flex items-center justify-center text-white">
                                    <i class="fas fa-screwdriver-wrench"></i>
                                </div>
                            </div>
                            <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full w-[72%] rounded-full brand-gradient-bg"></div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3 text-xs text-slate-300">
                                <span>Nhập xe</span>
                                <span>Kiểm tra</span>
                                <span class="text-right text-brand-accent">Báo giá</span>
                            </div>
                        </div>
                    </div>

                    <div class="scan-line absolute top-0 bottom-0 left-8 w-1 bg-cyan-300/80 shadow-[0_0_30px_rgba(103,232,249,0.9)]"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="bg-slate-950/85 px-6 py-20 border-t border-white/10">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl">
                <span class="text-sm font-bold uppercase tracking-widest text-brand-primary">Dịch vụ nổi bật</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-white">Mỗi điểm chạm được thiết kế để khách hàng yên tâm hơn.</h2>
                <p class="mt-4 text-slate-400 leading-7">Bố cục mới tập trung vào các hành động guest cần nhất: đặt lịch, gọi cứu hộ, xem quy trình và hiểu rõ lợi ích.</p>
            </div>

            <div class="mt-10 grid md:grid-cols-3 gap-5">
                <article class="service-tile rounded-xl p-6">
                    <div class="h-12 w-12 rounded-xl bg-cyan-500/12 text-brand-primary flex items-center justify-center text-xl">
                        <i class="fas fa-cube"></i>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-white">Kiểm tra 3D VHC</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">Đánh dấu vị trí lỗi trực quan, giúp khách hàng hiểu rõ tình trạng xe trước khi phê duyệt sửa chữa.</p>
                </article>

                <article class="service-tile rounded-xl p-6">
                    <div class="h-12 w-12 rounded-xl bg-emerald-500/12 text-brand-accent flex items-center justify-center text-xl">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-white">Báo giá minh bạch</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">Từng hạng mục, phụ tùng và công sửa chữa được trình bày rõ ràng để khách hàng chủ động quyết định.</p>
                </article>

                <article class="service-tile rounded-xl p-6">
                    <div class="h-12 w-12 rounded-xl bg-red-500/12 text-red-300 flex items-center justify-center text-xl">
                        <i class="fas fa-location-dot"></i>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-white">SOS trên bản đồ</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">Gửi vị trí và thông tin sự cố nhanh chóng để đội cứu hộ tiếp nhận, cập nhật trạng thái và điều phối kịp thời.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="process" class="px-6 py-20 bg-slate-900">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-[0.9fr_1.1fr] gap-10 items-start">
            <div>
                <span class="text-sm font-bold uppercase tracking-widest text-brand-primary">Quy trình</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-white">Từ tiếp nhận đến bàn giao, mỗi bước đều có dữ liệu.</h2>
                <p class="mt-4 text-slate-400 leading-7">Trang chủ mới giải thích ngắn gọn cách garage vận hành, tránh dàn trải và giúp guest biết nên bắt đầu từ đâu.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                @foreach([
                    ['01', 'Đặt lịch hoặc gửi SOS', 'Khách hàng chọn lịch hẹn hoặc gửi yêu cầu cứu hộ nếu đang gặp sự cố trên đường.'],
                    ['02', 'Tiếp nhận và kiểm tra', 'Nhân viên tạo hồ sơ, ghi nhận thông tin xe và thực hiện kiểm tra tổng quát.'],
                    ['03', 'Gửi báo giá để phê duyệt', 'Hệ thống gửi danh sách hạng mục, chi phí và ghi chú kỹ thuật cho khách hàng.'],
                    ['04', 'Sửa chữa và bàn giao', 'Khách hàng theo dõi tiến độ, nhận thông báo và xem lịch sử sửa chữa sau bàn giao.'],
                ] as $step)
                    <article class="workflow-step rounded-xl p-6">
                        <div class="text-sm font-black brand-gradient-text">{{ $step[0] }}</div>
                        <h3 class="mt-3 text-lg font-black text-white">{{ $step[1] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">{{ $step[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="why-us" class="px-6 py-20 bg-slate-950 border-t border-white/10">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-[1.05fr_0.95fr] gap-10 items-center">
            <div>
                <span class="text-sm font-bold uppercase tracking-widest text-brand-primary">Cam kết</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-white">Không chỉ sửa xe, mà còn làm rõ toàn bộ trải nghiệm dịch vụ.</h2>
                <p class="mt-4 text-slate-400 leading-7">Giao diện mới cân bằng giữa hình ảnh garage, lợi ích sản phẩm và những hành động chính, giúp guest ra quyết định nhanh hơn.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div class="trust-tile rounded-xl p-6">
                    <i class="fas fa-shield-halved text-2xl text-brand-accent"></i>
                    <h3 class="mt-4 font-black text-white">Lưu lịch sử rõ ràng</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Hồ sơ xe, báo giá và trạng thái được gom về một nơi sau khi khách hàng đăng nhập.</p>
                </div>
                <div class="trust-tile rounded-xl p-6">
                    <i class="fas fa-headset text-2xl text-brand-primary"></i>
                    <h3 class="mt-4 font-black text-white">Hỗ trợ nhanh</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Khách hàng có lối vào SOS nổi bật và thông tin liên hệ luôn sẵn sàng ở footer.</p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-14 rounded-2xl border border-white/12 bg-white/[0.06] p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white">Sẵn sàng để bắt đầu chăm sóc xe thông minh?</h2>
                    <p class="mt-3 text-slate-400">Đăng nhập hoặc tạo tài khoản để đặt lịch và theo dõi hồ sơ sửa chữa.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    @guest
                        <button onclick="openLoginModal()" class="inline-flex items-center justify-center gap-3 rounded-xl brand-gradient-bg px-6 py-4 font-bold text-white">
                            <i class="fas fa-user-plus"></i>
                            Tạo tài khoản
                        </button>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center justify-center gap-3 rounded-xl brand-gradient-bg px-6 py-4 font-bold text-white">
                            <i class="fas fa-gauge-high"></i>
                            Mở dashboard
                        </a>
                    @endguest
                    <a href="{{ route('customer.sos.index') }}" class="inline-flex items-center justify-center gap-3 rounded-xl border border-white/15 px-6 py-4 font-bold text-white hover:bg-white/10">
                        <i class="fas fa-truck-medical"></i>
                        Cần cứu hộ
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
