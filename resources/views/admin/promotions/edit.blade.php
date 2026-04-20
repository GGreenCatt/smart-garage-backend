@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Khuyến Mãi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
        <div class="p-6 border-b border-slate-700 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-white">Cập Nhật Mã: {{ $promotion->code }}</h2>
                <p class="text-sm text-slate-400">Chỉnh sửa thông tin chương trình khuyến mãi</p>
            </div>
             <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('Xóa mã này?');">
                @csrf
                @method('DELETE')
                <button class="text-red-400 hover:text-red-300 font-bold text-sm bg-red-500/10 px-3 py-1.5 rounded-lg border border-red-500/20">Xóa Mã</button>
            </form>
        </div>
        
        <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Code & Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mã Code</label>
                    <input type="text" name="code" value="{{ old('code', $promotion->code) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white font-mono uppercase tracking-wider focus:border-indigo-500 focus:outline-none">
                    @error('code') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Trạng Thái</label>
                    <label class="inline-flex items-center cursor-pointer mt-2">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $promotion->is_active ? 'checked' : '' }}>
                        <div class="relative w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ms-3 text-sm font-medium text-slate-300">Đang Kích Hoạt</span>
                    </label>
                </div>
            </div>

            <!-- Value -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại Giảm Giá</label>
                    <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                        <option value="fixed" {{ $promotion->type == 'fixed' ? 'selected' : '' }}>Số Tiền Cố Định (VND)</option>
                        <option value="percent" {{ $promotion->type == 'percent' ? 'selected' : '' }}>Phần Trăm (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Giá Trị Giảm</label>
                    <input type="number" name="value" value="{{ old('value', $promotion->value) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                    @error('value') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mô Tả / Ghi Chú</label>
                <textarea name="description" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">{{ old('description', $promotion->description) }}</textarea>
            </div>

            <hr class="border-slate-700">

            <!-- Constraints -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Ngày Bắt Đầu</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', $promotion->start_date) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Ngày Kết Thúc</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', $promotion->end_date) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Giới Hạn Lượt Dùng</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit) }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                     <p class="text-[10px] text-slate-500 mt-1">Đã dùng: {{ $promotion->used_count }} lần</p>
                </div>
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Áp Dụng Cho Khách (SĐT)</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $promotion->customer->phone ?? '') }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Nhập SĐT khách hàng cụ thể">
                    <p class="text-[10px] text-slate-500 mt-1">Để trống để áp dụng cho tất cả khách hàng</p>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-slate-700">
                <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg transition">Hủy</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 transition">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
