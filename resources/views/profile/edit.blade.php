@extends('layouts.staff')

@section('title', 'Hồ Sơ Của Tôi')

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-[#1e293b] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 transition-colors">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-icons-round text-indigo-500 text-3xl">account_circle</span>
            Hồ Sơ Của Tôi
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Quản lý thông tin cá nhân và bảo mật</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Profile Info -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-teal-500">info</span>
                Thông Tin Cá Nhân
            </h2>
            
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('patch')
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Họ Tên</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-[#0B1120] border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-teal-500 outline-none text-slate-700 dark:text-white transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-[#0B1120] border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-teal-500 outline-none text-slate-700 dark:text-white transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-lg shadow-teal-500/30 transition transform active:scale-95">
                        Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-rose-500">lock</span>
                Đổi Mật Khẩu
            </h2>
            
            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('put')
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Mật Khẩu Hiện Tại</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-[#0B1120] border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 outline-none text-slate-700 dark:text-white transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Mật Khẩu Mới</label>
                    <input type="password" name="password" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-[#0B1120] border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 outline-none text-slate-700 dark:text-white transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nhập Lại Mật Khẩu</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-[#0B1120] border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 outline-none text-slate-700 dark:text-white transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-lg shadow-rose-500/30 transition transform active:scale-95">
                        Cập Nhật Mật Khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
