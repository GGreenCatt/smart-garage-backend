@extends('layouts.admin')

@section('title', 'Cài Đặt Hệ Thống')

@section('content')
<style>
    /* User Custom Styles */
    .glass {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .glow-primary {
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
    }
    .floating-label-group {
        position: relative;
    }
    .floating-label-group input:focus ~ label,
    .floating-label-group input:not(:placeholder-shown) ~ label {
        transform: translateY(-1.5rem) scale(0.85);
        color: #818cf8;
    }
    .floating-label-group label {
        position: absolute;
        top: 1rem;
        left: 1rem;
        transition: all 0.2s ease;
        pointer-events: none;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 22px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #475569;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: #6366f1; }
    input:checked + .slider:before { transform: translateX(22px); }
</style>

<div class="h-full flex flex-col">
    <!-- Header Actions (Placed inside content) -->
    <div class="flex justify-between items-center mb-8 px-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Cài Đặt Hệ Thống</h1>
            <p class="text-sm text-slate-400">Quản lý thông tin gara và các tham số vận hành</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                Hủy thay đổi
            </button>
            <button onclick="document.getElementById('settingsForm').submit()" class="bg-primary hover:bg-indigo-500 text-white px-5 py-2 rounded-lg font-semibold flex items-center gap-2 glow-primary transition-all active:scale-95">
                <span class="material-icons-round text-lg">save</span>
                Lưu Thay Đổi
            </button>
        </div>
    </div>

    <!-- Main Settings Layout -->
    <div class="flex gap-8 flex-1 overflow-hidden">
        
        <!-- Settings Sidebar -->
        <aside class="w-64 flex-shrink-0 hidden md:block overflow-y-auto">
            <nav class="space-y-1 sticky top-0">
                <a class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl bg-primary/10 text-primary border border-primary/20" href="#general">
                    <span class="material-icons-round mr-3">settings</span>
                    Thông Tin Chung
                </a>
                <a class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800/50 transition-colors" href="#finance">
                    <span class="material-icons-round mr-3">payments</span>
                    Tài Chính & Thuế
                </a>
                <a class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800/50 transition-colors" href="#operations">
                    <span class="material-icons-round mr-3">construction</span>
                    Vận Hành
                </a>
                <a class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800/50 transition-colors" href="#ui">
                    <span class="material-icons-round mr-3">palette</span>
                    Giao Diện & Thương Hiệu
                </a>
                <!-- Other links from user template omitted or kept as placeholder -->
            </nav>
        </aside>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm" enctype="multipart/form-data" class="space-y-8 pb-12">
                @csrf
                
                <!-- General Info -->
                <section id="general" class="bg-card-dark rounded-2xl p-8 border border-slate-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-indigo-500/10 rounded-lg">
                            <span class="material-icons-round text-primary">info</span>
                        </div>
                        <h2 class="text-xl font-bold text-white">Thông Tin Chung</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="space-y-6">
                            <div class="floating-label-group">
                                <input name="garage_name" class="w-full bg-slate-900 border border-slate-800 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl px-4 py-3 outline-none transition-all text-white" id="garageName" placeholder=" " type="text" value="{{ \App\Models\Setting::get('garage_name', 'Smart Garage Admin') }}"/>
                                <label class="text-slate-400" for="garageName">Tên Gara</label>
                            </div>
                            <div class="floating-label-group">
                                <input name="garage_phone" class="w-full bg-slate-900 border border-slate-800 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl px-4 py-3 outline-none transition-all text-white" id="phone" placeholder=" " type="text" value="{{ \App\Models\Setting::get('garage_phone', '') }}"/>
                                <label class="text-slate-400" for="phone">Số Điện Thoại</label>
                            </div>
                            <div class="floating-label-group">
                                <input name="garage_address" class="w-full bg-slate-900 border border-slate-800 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl px-4 py-3 outline-none transition-all text-white" id="address" placeholder=" " type="text" value="{{ \App\Models\Setting::get('garage_address', '') }}"/>
                                <label class="text-slate-400" for="address">Địa Chỉ Trụ Sở</label>
                            </div>
                        </div>
                        
                        <!-- Logo Upload -->
                        <div class="flex flex-col items-center justify-center border-2 border-dashed border-slate-800 rounded-2xl p-6 bg-slate-900/50 hover:bg-slate-800 transition-colors cursor-pointer group relative overflow-hidden">
                             @if(\App\Models\Setting::get('garage_logo'))
                                <img src="{{ \App\Models\Setting::get('garage_logo') }}" class="h-20 mb-3 object-contain z-10">
                            @else
                                <div class="mb-3 p-3 bg-slate-800 rounded-full shadow-sm group-hover:scale-110 transition-transform">
                                    <span class="material-icons-round text-slate-400 text-3xl">add_photo_alternate</span>
                                </div>
                            @endif
                            <p class="text-sm font-semibold mb-1 text-white z-10 w-full text-center">Tải lên Logo Gara</p>
                            <p class="text-xs text-slate-400 z-10">PNG, JPG tối đa 5MB</p>
                            <input name="garage_logo" class="absolute inset-0 opacity-0 cursor-pointer z-20" type="file" accept="image/*"/>
                        </div>
                    </div>
                </section>

                <!-- Finance -->
                <section id="finance" class="bg-card-dark rounded-2xl p-8 border border-slate-800 shadow-sm opacity-60 pointer-events-none select-none grayscale-[0.5] relative overflow-hidden">
                    <div class="absolute inset-0 bg-slate-900/10 z-10"></div>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-yellow-500/10 rounded-lg">
                            <span class="material-icons-round text-yellow-500">payments</span>
                        </div>
                        <h2 class="text-xl font-bold text-white">Tài Chính & Thuế</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-400">Thuế VAT (%)</label>
                            <input name="tax_rate" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white" type="number" value="{{ \App\Models\Setting::get('tax_rate', '8') }}"/>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-400">Đơn Vị Tiền Tệ</label>
                            <select name="currency_symbol" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white">
                                <option value="₫" {{ \App\Models\Setting::get('currency_symbol') == '₫' ? 'selected' : '' }}>VNĐ (₫)</option>
                                <option value="$" {{ \App\Models\Setting::get('currency_symbol') == '$' ? 'selected' : '' }}>USD ($)</option>
                                <option value="€" {{ \App\Models\Setting::get('currency_symbol') == '€' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-400">Tiền Tố Hóa Đơn</label>
                            <input name="invoice_prefix" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white" placeholder="Gõ tiền tố..." type="text" value="{{ \App\Models\Setting::get('invoice_prefix', 'INV-') }}"/>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t border-slate-800/50">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-500/10 rounded-lg">
                                <span class="material-icons-round text-indigo-400">account_balance</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Chuyển Khoản & Mã QR (VietQR)</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-400">Ngân Hàng (Mã Ngắn gọn)</label>
                                <input name="bank_id" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white" placeholder="VD: mbbank, vietinbank, vietcombank..." type="text" value="{{ \App\Models\Setting::get('bank_id', 'vietinbank') }}"/>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-400">Số Tài Khoản</label>
                                <input name="bank_account_no" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white" placeholder="Gõ STK..." type="text" value="{{ \App\Models\Setting::get('bank_account_no', '102875143924') }}"/>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-400">Tên Chủ Tài Khoản (Không dấu)</label>
                                <input name="bank_account_name" class="w-full bg-slate-900 border-slate-800 focus:ring-primary rounded-xl px-4 py-3 text-white uppercase" placeholder="VD: NGUYEN VAN A" type="text" value="{{ \App\Models\Setting::get('bank_account_name', 'NGO VAN DAN') }}"/>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Operations -->
                <section id="operations" class="bg-card-dark rounded-2xl p-8 border border-slate-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-rose-500/10 rounded-lg">
                            <span class="material-icons-round text-rose-500">construction</span>
                        </div>
                        <h2 class="text-xl font-bold text-white">Vận Hành</h2>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-900/50 border border-slate-800/50">
                            <div class="flex gap-4">
                                <span class="material-icons-round text-rose-500">warning</span>
                                <div>
                                    <p class="font-semibold text-white">Chế Độ Bảo Trì</p>
                                    <p class="text-sm text-slate-500">Chỉ Admin mới có thể truy cập hệ thống khi bật chế độ này.</p>
                                </div>
                            </div>
                            <label class="switch">
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input name="maintenance_mode" type="checkbox" value="1" {{ \App\Models\Setting::get('maintenance_mode') == '1' ? 'checked' : '' }}/>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-800/50">
                                <div>
                                    <p class="font-semibold text-white">Tự động phân bổ kỹ thuật</p>
                                    <p class="text-xs text-slate-500">AI tự gán thợ dựa trên kỹ năng & độ trống</p>
                                </div>
                                <label class="switch">
                                    <input type="hidden" name="auto_assign_tech" value="0">
                                    <input name="auto_assign_tech" type="checkbox" value="1" {{ \App\Models\Setting::get('auto_assign_tech', '1') == '1' ? 'checked' : '' }}/>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-800/50">
                                <div>
                                    <p class="font-semibold text-white">Bật Kiểm Tra 3D</p>
                                    <p class="text-xs text-slate-500">Sử dụng mô hình 3D để đánh dấu hư hỏng</p>
                                </div>
                                <label class="switch">
                                    <input type="hidden" name="enable_3d_check" value="0">
                                    <input name="enable_3d_check" type="checkbox" value="1" {{ \App\Models\Setting::get('enable_3d_check', '0') == '1' ? 'checked' : '' }}/>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- UI & Branding -->
                <section id="ui" class="bg-card-dark rounded-2xl p-8 border border-slate-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-emerald-500/10 rounded-lg">
                            <span class="material-icons-round text-emerald-500">palette</span>
                        </div>
                        <h2 class="text-xl font-bold text-white">Giao Diện & Thương Hiệu</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="text-sm font-medium text-slate-400">Màu Chủ Đạo Portal Khách Hàng</label>
                            <div class="flex items-center gap-4">
                                <input name="portal_color_primary" class="w-12 h-12 rounded-lg cursor-pointer border-none p-0 bg-transparent" type="color" value="{{ \App\Models\Setting::get('portal_color_primary', '#6366f1') }}"/>
                                <div class="flex-1">
                                    <input class="w-full bg-slate-900 border-slate-800 rounded-lg px-3 py-2 uppercase text-sm font-mono text-white" type="text" value="{{ \App\Models\Setting::get('portal_color_primary', '#6366f1') }}" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-sm font-medium text-slate-400">Màu Phụ (Accent)</label>
                            <div class="flex items-center gap-4">
                                <input name="portal_color_accent" class="w-12 h-12 rounded-lg cursor-pointer border-none p-0 bg-transparent" type="color" value="{{ \App\Models\Setting::get('portal_color_accent', '#10b981') }}"/>
                                <div class="flex-1">
                                    <input class="w-full bg-slate-900 border-slate-800 rounded-lg px-3 py-2 uppercase text-sm font-mono text-white" type="text" value="{{ \App\Models\Setting::get('portal_color_accent', '#10b981') }}" readonly/>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </form>
        </div>
    </div>
</div>
@endsection
