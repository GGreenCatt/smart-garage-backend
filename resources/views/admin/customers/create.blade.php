@extends('layouts.admin')

@section('title', 'Thêm Khách Hàng')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.customers.index') }}" class="transition hover:text-indigo-300">Khách hàng</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">Thêm khách hàng</span>
    </div>

    <form action="{{ route('admin.customers.store') }}" method="POST" class="glass-panel rounded-2xl border border-slate-700/50 p-8">
        @csrf
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white">Tạo Tài Khoản Khách Hàng</h2>
            <p class="mt-1 text-sm text-slate-400">Dùng khi Admin cần tạo sẵn tài khoản để khách đăng nhập xem báo giá và lịch sử sửa chữa.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
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
                <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: Nguyễn Văn A">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số điện thoại</label>
                <input name="phone" value="{{ old('phone') }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="0909123456">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="customer@example.com">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mật khẩu</label>
                <input type="password" name="password" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Tối thiểu 6 ký tự">
            </div>
        </div>

        <div class="mt-6">
            <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Nhãn phân loại</label>
            <input name="tags" value="{{ old('tags') }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: Khách quen, VIP">
        </div>

        <div class="mt-8 flex justify-end gap-4 border-t border-slate-700 pt-6">
            <a href="{{ route('admin.customers.index') }}" class="rounded-lg bg-slate-800 px-6 py-3 font-bold text-white transition hover:bg-slate-700">Hủy</a>
            <button class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Tạo khách hàng</button>
        </div>
    </form>
</div>
@endsection
