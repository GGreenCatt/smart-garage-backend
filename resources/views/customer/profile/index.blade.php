@extends('layouts.customer')

@section('title', 'Tài Khoản')

@section('content')
<main class="pt-24 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-white mb-6">Cài Đặt Tài Khoản</h1>
        <div class="glass-panel p-8 rounded-2xl bg-[#1e293b] border border-[#334155]">
            <form class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-400 text-xs uppercase font-bold mb-2">Họ Tên</label>
                        <input type="text" value="{{ $user->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-cyan-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-xs uppercase font-bold mb-2">Số Điện Thoại</label>
                        <input type="text" value="{{ $user->phone ?? $user->email }}" disabled class="w-full bg-slate-900/50 border border-slate-800 rounded-lg px-4 py-3 text-slate-500 cursor-not-allowed">
                        <p class="text-[10px] text-slate-500 mt-1">* Định danh không thể thay đổi</p>
                    </div>
                </div>
                <!-- Add more fields (Password, Address) later if needed -->
                
                <div class="pt-4">
                    <button type="button" class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-cyan-900/20 transition opacity-50 cursor-not-allowed" title="Tính năng đang cập nhật">Lưu Thay Đổi</button>
                    <span class="text-xs text-slate-500 ml-3">Chức năng cập nhật đang bảo trì</span>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
