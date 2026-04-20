@extends('layouts.staff')

@section('title', 'Chỉnh Sửa Khách Hàng')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Chỉnh Sửa Khách Hàng</h1>
        <a href="{{ route('staff.customers.show', $customer->id) }}" class="text-slate-400 hover:text-white transition">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    <div class="glass-panel p-8 rounded-2xl max-w-2xl mx-auto bg-[#1e293b] border border-[#334155]">
        <form action="{{ route('staff.customers.update', $customer->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Họ & Tên</label>
                    <input type="text" name="name" value="{{ $customer->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Số Điện Thoại</label>
                        <input type="text" name="phone" value="{{ $customer->phone }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500" required>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" value="{{ $customer->email }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Địa Chỉ</label>
                    <textarea name="address" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-cyan-500">{{ $customer->address }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700 flex justify-end">
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-cyan-900/40 transition">
                    <i class="fas fa-save mr-2"></i> Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
