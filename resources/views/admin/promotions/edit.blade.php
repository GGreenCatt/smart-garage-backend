@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Mã Khuyến Mãi')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="flex flex-col gap-4 border-b border-slate-700 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Chỉnh sửa mã: {{ $promotion->code }}</h2>
                <p class="text-sm text-slate-400">Cập nhật giá trị, thời hạn, giới hạn dùng và khách áp dụng.</p>
            </div>
            <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('Xóa hoặc tắt mã này?');">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm font-bold text-red-300 transition hover:bg-red-500/20">Xóa / tắt mã</button>
            </form>
        </div>

        <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')
            @include('admin.promotions.form', ['promotion' => $promotion])

            <div class="flex justify-end gap-4 border-t border-slate-700 pt-4">
                <a href="{{ route('admin.promotions.index') }}" class="rounded-lg bg-slate-800 px-6 py-3 font-bold text-white transition hover:bg-slate-700">Hủy</a>
                <button class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
