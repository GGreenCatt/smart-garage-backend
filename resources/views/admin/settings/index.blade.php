@extends('layouts.admin')

@section('title', 'Cài Đặt Hệ Thống')

@section('content')
<style>
    .settings-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .settings-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .settings-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #475569;
        border-radius: 999px;
        transition: .2s;
    }

    .settings-slider:before {
        content: "";
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: .2s;
    }

    .settings-switch input:checked + .settings-slider {
        background: #6366f1;
    }

    .settings-switch input:checked + .settings-slider:before {
        transform: translateX(22px);
    }
</style>

<div class="flex h-full flex-col">
    <div class="mb-8 flex flex-col gap-4 px-2 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-white">Cài đặt hệ thống</h1>
            <p class="mt-2 text-sm text-slate-400">Quản lý thông tin garage, tài chính, chuyển khoản và các tham số vận hành.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.index') }}" class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                Hủy thay đổi
            </a>
            <button type="button" onclick="document.getElementById('settingsForm').submit()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                <span class="material-icons-round text-lg">save</span>
                Lưu thay đổi
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-4 text-sm font-bold text-emerald-300">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm font-bold text-red-300">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="flex min-h-0 flex-1 gap-8">
        <aside class="hidden w-72 shrink-0 overflow-y-auto md:block">
            <nav class="sticky top-0 space-y-2">
                <a class="flex items-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 px-4 py-3 text-sm font-bold text-indigo-300" href="#general">
                    <span class="material-icons-round mr-3">settings</span>
                    Thông tin chung
                </a>
                <a class="flex items-center rounded-xl px-4 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-800/60 hover:text-white" href="#finance">
                    <span class="material-icons-round mr-3">receipt_long</span>
                    Tài chính & thuế
                </a>
                <a class="flex items-center rounded-xl px-4 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-800/60 hover:text-white" href="#transfer">
                    <span class="material-icons-round mr-3">qr_code_2</span>
                    Chuyển khoản & mã QR
                </a>
                <a class="flex items-center rounded-xl px-4 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-800/60 hover:text-white" href="#operations">
                    <span class="material-icons-round mr-3">construction</span>
                    Vận hành
                </a>
                <a class="flex items-center rounded-xl px-4 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-800/60 hover:text-white" href="#branding">
                    <span class="material-icons-round mr-3">palette</span>
                    Giao diện & thương hiệu
                </a>
            </nav>
        </aside>

        <div class="min-w-0 flex-1 overflow-y-auto pr-2">
            <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm" enctype="multipart/form-data" class="space-y-8 pb-12">
                @csrf

                <section id="general" class="rounded-2xl border border-slate-800 bg-card-dark p-8 shadow-sm">
                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-xl bg-indigo-500/10 p-2">
                            <span class="material-icons-round text-indigo-400">info</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Thông tin chung</h2>
                            <p class="mt-1 text-sm text-slate-400">Thông tin hiển thị trên hóa đơn, phiếu in và khu vực quản trị.</p>
                        </div>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-sm font-bold text-slate-400">Tên garage</span>
                                <input name="garage_name" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" type="text" value="{{ \App\Models\Setting::get('garage_name', 'Smart Garage') }}">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-400">Số điện thoại</span>
                                <input name="garage_phone" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" type="text" value="{{ \App\Models\Setting::get('garage_phone', '') }}">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-400">Địa chỉ</span>
                                <input name="garage_address" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" type="text" value="{{ \App\Models\Setting::get('garage_address', '') }}">
                            </label>
                        </div>

                        <label class="relative flex min-h-56 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-800 bg-slate-900/60 p-6 text-center transition hover:border-indigo-500/60 hover:bg-slate-900">
                            @if(\App\Models\Setting::get('garage_logo'))
                                <img src="{{ \App\Models\Setting::get('garage_logo') }}" class="z-10 mb-4 h-20 object-contain" alt="Logo garage">
                            @else
                                <div class="z-10 mb-4 rounded-full bg-slate-800 p-4">
                                    <span class="material-icons-round text-4xl text-slate-400">add_photo_alternate</span>
                                </div>
                            @endif
                            <span class="z-10 text-sm font-black text-white">Tải lên logo garage</span>
                            <span class="z-10 mt-1 text-xs font-semibold text-slate-500">PNG, JPG, WEBP tối đa 5MB</span>
                            <input name="garage_logo" class="absolute inset-0 z-20 cursor-pointer opacity-0" type="file" accept="image/*">
                        </label>
                    </div>
                </section>

                <section id="finance" class="relative overflow-hidden rounded-2xl border border-slate-800 bg-card-dark p-8 opacity-60 shadow-sm grayscale-[0.35]">
                    <div class="absolute inset-0 z-10 cursor-not-allowed bg-slate-950/20"></div>
                    <div class="absolute right-6 top-6 z-20 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black uppercase tracking-wider text-amber-300">
                        Đã khóa
                    </div>
                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-xl bg-amber-500/10 p-2">
                            <span class="material-icons-round text-amber-400">receipt_long</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Tài chính & thuế</h2>
                            <p class="mt-1 text-sm text-slate-400">Dự án cá nhân không dùng nghiệp vụ thuế nên phần này đang được tắt.</p>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Thuế VAT (%)</span>
                            <input name="tax_rate" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" type="number" min="0" max="100" step="0.01" value="{{ \App\Models\Setting::get('tax_rate', '8') }}">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Đơn vị tiền tệ</span>
                            <select name="currency_symbol" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500">
                                <option value="₫" {{ \App\Models\Setting::get('currency_symbol', '₫') == '₫' ? 'selected' : '' }}>VNĐ (₫)</option>
                                <option value="$" {{ \App\Models\Setting::get('currency_symbol') == '$' ? 'selected' : '' }}>USD ($)</option>
                                <option value="€" {{ \App\Models\Setting::get('currency_symbol') == '€' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Tiền tố hóa đơn</span>
                            <input name="invoice_prefix" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" type="text" value="{{ \App\Models\Setting::get('invoice_prefix', 'INV-') }}">
                        </label>
                    </div>
                </section>

                <section id="transfer" class="rounded-2xl border border-slate-800 bg-card-dark p-8 shadow-sm">
                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-xl bg-cyan-500/10 p-2">
                            <span class="material-icons-round text-cyan-300">qr_code_2</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Chuyển khoản & mã QR</h2>
                            <p class="mt-1 text-sm text-slate-400">Cấu hình tài khoản nhận tiền và mã VietQR dùng ở hóa đơn/thanh toán tại quầy.</p>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Mã ngân hàng</span>
                            <input name="bank_id" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" placeholder="VD: mbbank, vietinbank, vietcombank" type="text" value="{{ \App\Models\Setting::get('bank_id', 'vietinbank') }}">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Số tài khoản</span>
                            <input name="bank_account_no" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 font-mono text-white outline-none transition focus:border-indigo-500" type="text" value="{{ \App\Models\Setting::get('bank_account_no', '102875143924') }}">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Tên chủ tài khoản</span>
                            <input name="bank_account_name" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 uppercase text-white outline-none transition focus:border-indigo-500" placeholder="VD: NGUYEN VAN A" type="text" value="{{ \App\Models\Setting::get('bank_account_name', 'NGO VAN DAN') }}">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Mẫu VietQR</span>
                            <select name="vietqr_template" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500">
                                @foreach(['compact2' => 'Compact 2 - đầy đủ', 'compact' => 'Compact', 'qr_only' => 'Chỉ mã QR', 'print' => 'Bản in'] as $value => $label)
                                    <option value="{{ $value }}" {{ \App\Models\Setting::get('vietqr_template', 'compact2') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-bold text-slate-400">Nội dung chuyển khoản</span>
                            <input name="qr_payment_content" class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500" placeholder="VD: Thanh toan hoa don {order_id}" type="text" value="{{ \App\Models\Setting::get('qr_payment_content', 'Thanh toan hoa don {order_id}') }}">
                            <span class="mt-2 block text-xs text-slate-500">Có thể dùng biến <b>{order_id}</b> hoặc <b>{track_id}</b>.</span>
                        </label>
                    </div>
                </section>

                <section id="operations" class="rounded-2xl border border-slate-800 bg-card-dark p-8 shadow-sm">
                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-xl bg-rose-500/10 p-2">
                            <span class="material-icons-round text-rose-400">construction</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Vận hành</h2>
                            <p class="mt-1 text-sm text-slate-400">Bật/tắt các chức năng hệ thống đang dùng trong vận hành hằng ngày.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach([
                            ['key' => 'maintenance_mode', 'default' => '0', 'icon' => 'warning', 'title' => 'Chế độ bảo trì', 'desc' => 'Chỉ quản trị viên nên truy cập khi hệ thống bảo trì.'],
                            ['key' => 'enable_notifications', 'default' => '1', 'icon' => 'notifications_active', 'title' => 'Thông báo hệ thống', 'desc' => 'Bật/tắt khu vực thông báo cho nhân viên và khách hàng.'],
                            ['key' => 'enable_3d_check', 'default' => '0', 'icon' => 'view_in_ar', 'title' => 'Kiểm tra 3D/VHC', 'desc' => 'Cho phép dùng mô hình 3D để đánh dấu hư hỏng khi kiểm tra xe.'],
                        ] as $toggle)
                            <div class="flex items-center justify-between gap-5 rounded-2xl border border-slate-800 bg-slate-900/50 p-5">
                                <div class="flex gap-4">
                                    <div class="h-fit rounded-xl bg-slate-800 p-2">
                                        <span class="material-icons-round text-indigo-300">{{ $toggle['icon'] }}</span>
                                    </div>
                                    <div>
                                        <p class="font-black text-white">{{ $toggle['title'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $toggle['desc'] }}</p>
                                    </div>
                                </div>
                                <label class="settings-switch shrink-0">
                                    <input type="hidden" name="{{ $toggle['key'] }}" value="0">
                                    <input name="{{ $toggle['key'] }}" type="checkbox" value="1" {{ \App\Models\Setting::get($toggle['key'], $toggle['default']) == '1' ? 'checked' : '' }}>
                                    <span class="settings-slider"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="branding" class="rounded-2xl border border-slate-800 bg-card-dark p-8 shadow-sm">
                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-xl bg-emerald-500/10 p-2">
                            <span class="material-icons-round text-emerald-400">palette</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white">Giao diện & thương hiệu</h2>
                            <p class="mt-1 text-sm text-slate-400">Màu sắc hiển thị cho portal khách hàng.</p>
                        </div>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-3 block text-sm font-bold text-slate-400">Màu chủ đạo portal khách hàng</span>
                            <div class="flex items-center gap-4">
                                <input name="portal_color_primary" class="h-12 w-14 cursor-pointer rounded-xl border-0 bg-transparent p-0" type="color" value="{{ \App\Models\Setting::get('portal_color_primary', '#06b6d4') }}" oninput="this.nextElementSibling.value = this.value.toUpperCase()">
                                <input class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 font-mono text-sm uppercase text-white" type="text" readonly value="{{ \App\Models\Setting::get('portal_color_primary', '#06b6d4') }}">
                            </div>
                        </label>
                        <label class="block">
                            <span class="mb-3 block text-sm font-bold text-slate-400">Màu phụ</span>
                            <div class="flex items-center gap-4">
                                <input name="portal_color_accent" class="h-12 w-14 cursor-pointer rounded-xl border-0 bg-transparent p-0" type="color" value="{{ \App\Models\Setting::get('portal_color_accent', '#10b981') }}" oninput="this.nextElementSibling.value = this.value.toUpperCase()">
                                <input class="w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 font-mono text-sm uppercase text-white" type="text" readonly value="{{ \App\Models\Setting::get('portal_color_accent', '#10b981') }}">
                            </div>
                        </label>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>
@endsection
