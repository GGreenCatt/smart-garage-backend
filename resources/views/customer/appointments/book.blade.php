@extends('layouts.customer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('customer.dashboard') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm transition"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-black text-slate-800">Đặt Lịch Hẹn</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 p-6 md:p-8">
        <form action="{{ route('customer.appointments.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehicle Selection -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Chọn Xe</label>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($vehicles as $v)
                        <label class="cursor-pointer relative">
                            <input type="radio" name="vehicle_id" value="{{ $v->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-100 bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition">
                                <div class="font-bold text-slate-800">{{ $v->license_plate }}</div>
                                <div class="text-xs text-slate-500">{{ $v->model }}</div>
                                <i class="fas fa-check-circle absolute top-4 right-4 text-indigo-500 opacity-0 peer-checked:opacity-100 transition transform scale-0 peer-checked:scale-100"></i>
                            </div>
                        </label>
                        @endforeach
                        @if($vehicles->isEmpty())
                        <div class="p-4 bg-red-50 text-red-500 rounded-lg text-sm">Bạn chưa có xe nào. Vui lòng thêm xe trước.</div>
                        @endif
                    </div>
                </div>

                <!-- Service Selection -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Dịch Vụ</label>
                    <select name="service_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700">
                        @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} - {{ number_format($s->price) }}đ</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Time -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Thời Gian</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" required>
                </div>

                <!-- Notes -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-slate-500 uppercase">Ghi Chú</label>
                    <textarea name="notes" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold text-slate-700" placeholder="Mô tả thêm về vấn đề xe..."></textarea>
                </div>
            </div>

            <button class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 transform active:scale-95 transition text-lg">
                Xác Nhận Đặt Lịch
            </button>
        </form>
    </div>
</div>
@endsection
