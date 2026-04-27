@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Khách Hàng')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="border-b border-slate-700 p-6">
            <h2 class="text-lg font-bold text-white">Chỉnh sửa: {{ $customer->name }}</h2>
            <p class="text-sm text-slate-400">Cập nhật thông tin liên hệ, trạng thái tài khoản và mật khẩu khách hàng.</p>
        </div>

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Họ và tên</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Trạng thái</label>
                    <select name="status" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                        <option value="active" @selected(old('status', $customer->status ?? 'active') === 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Ngừng hoạt động</option>
                        <option value="banned" @selected(old('status', $customer->status) === 'banned')>Đã khóa</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-6">
                <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Nhãn phân loại</label>
                <p class="mb-2 text-xs text-slate-400">Nhập các nhãn phân loại, cách nhau bằng dấu phẩy. Ví dụ: VIP, Khách quen.</p>
                <input type="text" name="tags" value="{{ old('tags', $customer->tags ? implode(', ', $customer->tags) : '') }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: VIP, Khách quen">
            </div>

            <div class="border-t border-slate-700 pt-6">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-white">
                    <i class="fas fa-lock text-indigo-400"></i>
                    Đổi mật khẩu
                </h3>
                <div class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4">
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mật khẩu mới</label>
                    <input type="password" name="password" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Để trống nếu không đổi">
                    <p class="mt-2 text-xs text-slate-500">Tối thiểu 6 ký tự. Chỉ nhập khi cần đặt lại mật khẩu cho khách hàng.</p>
                </div>
            </div>

            <div class="flex justify-end gap-4 border-t border-slate-700 pt-4">
                <a href="{{ route('admin.customers.show', $customer) }}" class="rounded-lg bg-slate-800 px-6 py-3 font-bold text-white transition hover:bg-slate-700">Hủy</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
