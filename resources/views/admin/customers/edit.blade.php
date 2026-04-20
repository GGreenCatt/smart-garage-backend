@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Khách Hàng')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
        <div class="p-6 border-b border-slate-700">
            <h2 class="text-lg font-bold text-white">Chỉnh Sửa: {{ $customer->name }}</h2>
            <p class="text-sm text-slate-400">Cập nhật thông tin và mật khẩu khách hàng</p>
        </div>
        
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Họ và Tên</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                    @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Số Điện Thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                    @error('phone') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email (Tùy chọn)</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                    @error('email') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="border-slate-700">

            <!-- Tags -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Thẻ (Tags)</label>
                <div class="mb-2 text-xs text-slate-400">Nhập các thẻ phân loại, cách nhau bằng dấu phẩy (VD: VIP, Nợ xấu, Khách quen)</div>
                <input type="text" name="tags" value="{{ old('tags', $customer->tags ? implode(', ', $customer->tags) : '') }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none tag-input" placeholder="VD: VIP, Khách quen">
            </div>

            <hr class="border-slate-700">

            <!-- Security -->
            <div>
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2"><i class="fas fa-lock text-indigo-400"></i> Đổi Mật Khẩu (Tùy chọn)</h3>
                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700/50">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mật Khẩu Mới</label>
                    <input type="password" name="password" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Để trống nếu không muốn đổi">
                    <p class="mt-2 text-xs text-slate-500">Tối thiểu 6 ký tự. Chỉ nhập nếu bạn muốn đổi mật khẩu cho khách hàng này.</p>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-slate-700">
                <a href="{{ route('admin.customers.index') }}" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg transition">Hủy</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 transition">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
