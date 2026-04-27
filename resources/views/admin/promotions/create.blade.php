@extends('layouts.admin')

@section('title', 'Tạo Mã Khuyến Mãi')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="border-b border-slate-700 p-6">
            <h2 class="text-lg font-bold text-white">Tạo Mã Khuyến Mãi</h2>
            <p class="text-sm text-slate-400">Mã này sẽ được nhân viên áp tại quầy khi khách thanh toán.</p>
        </div>

        <form action="{{ route('admin.promotions.store') }}" method="POST" class="space-y-6 p-6">
            @csrf
            @include('admin.promotions.form', ['promotion' => null])

            <div class="flex justify-end gap-4 border-t border-slate-700 pt-4">
                <a href="{{ route('admin.promotions.index') }}" class="rounded-lg bg-slate-800 px-6 py-3 font-bold text-white transition hover:bg-slate-700">Hủy</a>
                <button class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Tạo mã</button>
            </div>
        </form>
    </div>
</div>
@endsection
