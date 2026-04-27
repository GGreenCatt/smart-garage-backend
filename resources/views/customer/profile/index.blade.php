@extends('layouts.customer')

@section('title', 'Tài khoản')

@section('content')
<main class="pt-24 min-h-screen bg-[#0b1120]">
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <div>
            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white mb-3">
                <i class="fas fa-arrow-left"></i> Về tổng quan
            </a>
            <h1 class="text-3xl font-black text-white">Tài khoản</h1>
            <p class="text-slate-400 mt-2">Thông tin định danh dùng để đồng bộ xe, lịch hẹn và lịch sử sửa chữa.</p>
        </div>

        <section class="bg-slate-900/70 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex items-center gap-4">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0891b2&color=fff" class="w-16 h-16 rounded-2xl" alt="">
                <div>
                    <h2 class="text-xl font-black text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-400">{{ $user->phone ?: $user->email ?: 'Chưa có thông tin liên hệ' }}</p>
                </div>
            </div>

            <div class="p-6 grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-slate-500 text-xs uppercase font-black mb-2">Họ tên</label>
                    <div class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-white">{{ $user->name }}</div>
                </div>
                <div>
                    <label class="block text-slate-500 text-xs uppercase font-black mb-2">Số điện thoại</label>
                    <div class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-white">{{ $user->phone ?: 'Chưa cập nhật' }}</div>
                </div>
                <div>
                    <label class="block text-slate-500 text-xs uppercase font-black mb-2">Email</label>
                    <div class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-white">{{ $user->email ?: 'Chưa cập nhật' }}</div>
                </div>
                <div>
                    <label class="block text-slate-500 text-xs uppercase font-black mb-2">Địa chỉ</label>
                    <div class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-white">{{ $user->address ?: 'Chưa cập nhật' }}</div>
                </div>
            </div>
        </section>

        <section class="bg-cyan-500/10 border border-cyan-500/20 rounded-2xl p-5 text-cyan-100">
            <div class="flex gap-3">
                <i class="fas fa-circle-info text-cyan-300 mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-white">Cập nhật thông tin tại quầy</h3>
                    <p class="text-sm mt-1 text-cyan-100/80">
                        Nếu số điện thoại, email hoặc thông tin xe chưa đúng, vui lòng báo nhân viên garage để cập nhật. Số điện thoại là thông tin chính để đồng bộ lịch sử sửa chữa.
                    </p>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection
