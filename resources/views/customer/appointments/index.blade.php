@extends('layouts.customer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm transition"><i class="fas fa-arrow-left"></i></a>
            <h1 class="text-2xl font-black text-slate-800">Lịch Sử Hẹn</h1>
        </div>
        <a href="{{ route('customer.appointments.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded-lg shadow-lg shadow-indigo-500/20 transform active:scale-95 transition">
            <i class="fas fa-plus"></i> Đặt Lịch Mới
        </a>
    </div>

    <div class="space-y-4">
        @forelse($appointments as $appt)
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold
                    {{ $appt->status == 'confirmed' ? 'bg-green-100 text-green-600' : 
                       ($appt->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 
                       ($appt->status == 'completed' ? 'bg-blue-100 text-blue-600' : 'bg-red-100 text-red-600')) }}">
                    <i class="fas {{ $appt->status == 'completed' ? 'fa-check' : 'fa-calendar-day' }}"></i>
                </div>
                <div>
                    <div class="font-bold text-slate-800 text-lg">{{ $appt->service->name ?? 'Kiểm tra / Tư vấn' }}</div>
                    @if($appt->vehicle)
                        <div class="text-xs text-slate-500 font-bold uppercase">{{ $appt->vehicle->license_plate }} • {{ $appt->vehicle->model }}</div>
                    @else
                        <div class="text-xs text-slate-500 font-bold uppercase">{{ $appt->license_plate ?? 'Chưa rõ' }} • {{ $appt->vehicle_name ?? 'Chưa rõ' }}</div>
                    @endif
                    <div class="text-sm text-indigo-600 font-bold mt-1">{{ $appt->scheduled_at->format('H:i - d/m/Y') }}</div>
                    @if($appt->reason)
                        <div class="text-xs text-slate-600 mt-1 italic max-w-sm">"{{ Str::limit($appt->reason, 50) }}"</div>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-col items-end gap-2">
                @php
                    $statusLabel = match($appt->status) {
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => 'Không rõ'
                    };
                    $statusClass = match($appt->status) {
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'confirmed' => 'bg-green-100 text-green-700',
                        'completed' => 'bg-blue-100 text-blue-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-slate-100 text-slate-700'
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
                @if($appt->admin_notes)
                <div class="text-xs text-slate-400 italic mt-1 max-w-[200px] text-right">"{{ $appt->admin_notes }}"</div>
                @endif
                
                @if($appt->status === 'pending')
                <div class="flex items-center gap-3 mt-2">
                    <a href="{{ route('customer.appointments.edit', $appt->id) }}" class="text-xs text-indigo-500 hover:text-indigo-700 font-bold underline"><i class="fas fa-edit"></i> Chỉnh Sửa</a>
                    <form action="{{ route('customer.appointments.destroy', $appt->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold underline"><i class="fas fa-times"></i> Hủy Lịch</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3 class="font-bold text-slate-600">Chưa có lịch hẹn nào</h3>
            <p class="text-slate-400 text-sm">Hãy đặt lịch bảo dưỡng ngay hôm nay!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
