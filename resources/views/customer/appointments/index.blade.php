@extends('layouts.customer')

@section('title', 'Lịch hẹn của tôi')

@php
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Đã tiếp nhận',
        'cancelled' => 'Đã hủy',
        'no_show' => 'Không đến',
    ];
    $statusClasses = [
        'pending' => 'bg-amber-500/10 text-amber-300 border-amber-500/25',
        'confirmed' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/25',
        'completed' => 'bg-cyan-500/10 text-cyan-300 border-cyan-500/25',
        'cancelled' => 'bg-red-500/10 text-red-300 border-red-500/25',
        'no_show' => 'bg-slate-500/10 text-slate-300 border-slate-500/25',
    ];
@endphp

@section('content')
<main class="pt-24 min-h-screen bg-[#0b1120]">
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white mb-3">
                    <i class="fas fa-arrow-left"></i> Về tổng quan
                </a>
                <h1 class="text-3xl font-black text-white">Lịch hẹn của tôi</h1>
                <p class="text-slate-400 mt-2">Theo dõi lịch hẹn đã đặt và phản hồi xác nhận từ garage.</p>
            </div>
            <a href="{{ route('customer.appointments.create') }}" class="inline-flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-500 text-white font-black py-3 px-5 rounded-xl shadow-lg shadow-cyan-950/30 transition">
                <i class="fas fa-calendar-plus"></i> Đặt lịch mới
            </a>
        </div>

        <div class="grid gap-4">
            @forelse($appointments as $appt)
                <article class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 text-cyan-300 flex items-center justify-center text-xl shrink-0">
                                <i class="fas {{ $appt->status === 'completed' ? 'fa-check' : 'fa-calendar-day' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-black text-white text-lg">{{ $appt->service->name ?? 'Kiểm tra / tư vấn' }}</h2>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $statusClasses[$appt->status] ?? $statusClasses['no_show'] }}">
                                        {{ $statusLabels[$appt->status] ?? 'Không rõ' }}
                                    </span>
                                </div>
                                <div class="text-sm text-slate-400 mt-1">
                                    @if($appt->vehicle)
                                        {{ $appt->vehicle->license_plate }} · {{ $appt->vehicle->model }}
                                    @else
                                        {{ $appt->license_plate ?? 'Chưa rõ biển số' }} · {{ $appt->vehicle_name ?? 'Chưa rõ xe' }}
                                    @endif
                                </div>
                                <div class="text-cyan-300 font-bold mt-2">{{ $appt->scheduled_at->format('H:i - d/m/Y') }}</div>
                                @if($appt->reason)
                                    <p class="text-sm text-slate-400 mt-2 max-w-2xl">{{ $appt->reason }}</p>
                                @endif
                                @if($appt->admin_notes)
                                    <div class="mt-3 rounded-xl bg-slate-950/50 border border-slate-800 p-3 text-sm text-slate-300">
                                        <span class="font-bold text-slate-100">Phản hồi garage:</span> {{ $appt->admin_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($appt->status === 'pending')
                            <div class="flex items-center gap-3 lg:justify-end">
                                <a href="{{ route('customer.appointments.edit', $appt->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 hover:bg-slate-700 px-4 py-2 text-sm font-bold text-white">
                                    <i class="fas fa-pen"></i> Chỉnh sửa
                                </a>
                                <form action="{{ route('customer.appointments.destroy', $appt->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/25 px-4 py-2 text-sm font-bold text-red-300">
                                        <i class="fas fa-xmark"></i> Hủy
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="text-center py-16 border border-dashed border-slate-700 rounded-2xl text-slate-400">
                    <i class="fas fa-calendar-times text-5xl mb-4 opacity-30"></i>
                    <h3 class="font-black text-white">Chưa có lịch hẹn nào</h3>
                    <p class="text-sm mt-2">Bạn có thể đặt lịch để garage kiểm tra xe.</p>
                    <a href="{{ route('customer.appointments.create') }}" class="inline-flex mt-5 bg-cyan-600 hover:bg-cyan-500 text-white font-black py-3 px-5 rounded-xl">Đặt lịch mới</a>
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
