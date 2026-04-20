@extends('layouts.staff')

@section('title', $customer->name)

@section('content')
<div class="h-full flex flex-col xl:flex-row gap-6">
    <!-- Sidebar: Customer Info -->
    <div class="w-full xl:w-[350px] flex flex-col gap-6 shrink-0">
        <div class="bg-white dark:bg-[#1e293b] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 text-center relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-28 bg-gradient-to-br from-indigo-500 to-sky-600 dark:from-indigo-600 dark:to-sky-800 transition-all group-hover:scale-105 duration-700"></div>
            
            <div class="relative z-10 w-24 h-24 rounded-full bg-white dark:bg-[#0f172a] p-1.5 mx-auto mt-10 mb-4 shadow-xl shadow-indigo-500/20">
                <div class="w-full h-full rounded-full bg-slate-100 dark:bg-[#334155] flex items-center justify-center text-4xl font-black text-slate-400 dark:text-slate-300 uppercase">
                    {{ substr($customer->name, 0, 1) }}
                </div>
            </div>
            
            <h1 class="font-black text-2xl text-slate-800 dark:text-white mb-1">{{ $customer->name }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-6 flex items-center justify-center gap-1">
                <span class="material-icons-round text-amber-500 text-[16px]">stars</span>
                Khách hàng thành viên
            </p>
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                <a href="tel:{{ $customer->phone }}" class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 py-3 rounded-xl font-bold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition flex items-center justify-center gap-2">
                    <span class="material-icons-round text-[18px]">call</span> Gọi
                </a>
                <a href="mailto:{{ $customer->email }}" class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 py-3 rounded-xl font-bold text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition flex items-center justify-center gap-2">
                    <span class="material-icons-round text-[18px]">email</span> Email
                </a>
            </div>

            <a href="{{ route('staff.customers.edit', $customer->id) }}" class="w-full bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white px-4 py-3 rounded-xl text-sm font-bold transition mb-6 flex items-center justify-center gap-2 shadow-lg shadow-slate-900/10">
                <span class="material-icons-round text-[18px]">edit_note</span> Chỉnh Sửa Thông Tin
            </a>

            <div class="text-left space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-2 rounded-lg text-indigo-500">
                            <span class="material-icons-round text-[20px]">smartphone</span>
                        </div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">Điện thoại</span>
                    </div>
                    <span class="text-sm font-mono font-bold text-slate-700 dark:text-slate-200">{{ $customer->phone ?? '---' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="bg-teal-50 dark:bg-teal-900/20 p-2 rounded-lg text-teal-500">
                            <span class="material-icons-round text-[20px]">alternate_email</span>
                        </div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">Email</span>
                    </div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate max-w-[150px]" title="{{ $customer->email }}">{{ $customer->email }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <div class="flex items-center gap-3">
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg text-amber-500">
                            <span class="material-icons-round text-[20px]">calendar_today</span>
                        </div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">Tham gia</span>
                    </div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $customer->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-[#0f172a] dark:bg-[#020617] rounded-2xl p-6 text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition border border-slate-800">
            <div class="relative z-10">
                <p class="text-sm text-slate-400 font-medium mb-1 uppercase tracking-widest">Tổng chi tiêu</p>
                <h3 class="text-3xl font-black tracking-tight mb-2 font-mono">0đ</h3>
                <p class="text-xs text-slate-500 flex items-center gap-1">
                    <span class="material-icons-round text-[14px]">construction</span>
                    Chức năng đang phát triển
                </p>
            </div>
            <span class="material-icons-round absolute -bottom-4 -right-4 text-[120px] text-slate-800 opacity-50 group-hover:text-indigo-900 transition duration-500 rotate-12">account_balance_wallet</span>
        </div>
    </div>

    <!-- Main: Vehicles & History -->
    <div class="flex-1 flex flex-col gap-6 overflow-hidden">
        <header class="flex justify-between items-center bg-white dark:bg-[#1e293b] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div>
                <h2 class="font-black text-xl text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-icons-round text-indigo-500 text-3xl">directions_car</span>
                    Danh Sách Xe
                    <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 text-sm px-2.5 py-0.5 rounded-full font-bold shadow-indigo-500/20 shadow-sm">{{ $customer->vehicles->count() }}</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Quản lý các phương tiện và lịch sử sửa chữa</p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto pr-2 space-y-6 custom-scrollbar pb-10">
            @forelse($customer->vehicles as $vehicle)
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden group">
                <!-- Vehicle Header -->
                <div class="p-5 bg-slate-50 dark:bg-[#0f172a]/50 border-b border-slate-100 dark:border-slate-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#1e293b] shadow-sm flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-slate-100 dark:border-slate-600">
                            <span class="material-icons-round text-2xl">directions_car</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                                {{ $vehicle->model }}
                                <span class="text-sm font-normal text-slate-400 font-mono">({{ $vehicle->year }})</span>
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded border border-slate-300 dark:border-slate-500">
                                    {{ $vehicle->license_plate }}
                                </span>
                                <span class="text-[10px] uppercase font-bold text-slate-400 border border-slate-200 dark:border-slate-700 px-1.5 rounded">{{ $vehicle->type ?? 'Sedan' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 w-full md:w-auto">
                        @can('delete_vehicles')
                        <button onclick="deleteVehicle({{ $vehicle->id }})" class="flex-1 md:flex-none text-xs bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 px-3 py-2 rounded-lg font-bold hover:bg-red-100 dark:hover:bg-red-900/40 transition border border-red-100 dark:border-transparent flex items-center justify-center gap-1">
                            <span class="material-icons-round text-[16px]">delete</span>
                        </button>
                        @endcan
                        <a href="{{ route('staff.vehicles.edit', $vehicle->id) }}" class="flex-1 md:flex-none text-xs bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 px-3 py-2 rounded-lg font-bold hover:bg-amber-100 dark:hover:bg-amber-900/40 transition border border-amber-100 dark:border-transparent flex items-center justify-center gap-1">
                            <span class="material-icons-round text-[16px]">edit</span>
                        </a>

                    </div>
                </div>

                <form id="delete-vehicle-form-{{ $vehicle->id }}" action="{{ route('staff.vehicles.destroy', $vehicle->id) }}" method="POST" class="hidden">
                     @csrf
                     @method('DELETE')
                </form>

                <!-- Repair History -->
                <div class="p-5">
                    <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 flex items-center gap-2">
                        <span class="material-icons-round text-[16px]">history</span> Lịch sử sửa chữa
                    </h4>
                    <div class="space-y-3">
                        @forelse($vehicle->repairOrders as $order)
                        <a href="{{ route('staff.order.show', $order->id) }}" class="block p-4 rounded-xl border border-slate-200 dark:border-slate-700/50 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition group bg-white dark:bg-[#1e293b]">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col items-center min-w-[60px] border-r border-slate-100 dark:border-slate-700 pr-4">
                                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $order->created_at->format('M d') }}</span>
                                    <span class="text-xl font-black text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $order->created_at->format('Y') }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition text-sm mb-1 flex items-center gap-2">
                                                RO-{{ $order->id }} 
                                                <span class="text-slate-400 dark:text-slate-500 font-normal">• {{ $order->service_type ?? 'Bảo dưỡng định kỳ' }}</span>
                                            </p>
                                            <div class="flex items-center gap-2">
                                                 <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border
                                                    {{ $order->status === 'pending' ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 border-slate-200 dark:border-slate-700' : '' }}
                                                    {{ $order->status === 'in_progress' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800' : '' }}
                                                    {{ $order->status === 'completed' ? 'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800' : '' }}
                                                ">
                                                    @if($order->status == 'completed') <span class="material-icons-round text-[12px]">check_circle</span> @endif
                                                    {{ $order->status == 'pending' ? 'Chờ xử lý' : ($order->status == 'in_progress' ? 'Đang thực hiện' : 'Hoàn thành') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-700 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                                            <span class="material-icons-round text-[20px] text-slate-400 group-hover:text-white">arrow_forward</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="text-center py-6 text-slate-400 dark:text-slate-600 text-sm italic border-2 border-dashed border-slate-100 dark:border-slate-700 rounded-xl">
                            Chưa có lịch sử sửa chữa cho xe này
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-[#1e293b] p-12 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 text-center flex flex-col items-center justify-center">
                 <div class="w-20 h-20 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4 text-slate-300 dark:text-slate-600">
                    <span class="material-icons-round text-5xl">no_crash</span>
                 </div>
                 <h3 class="text-lg font-bold text-slate-800 dark:text-white">Chưa có xe nào</h3>
                 <p class="text-slate-500 dark:text-slate-400 font-medium mt-1 mb-6">Khách hàng này chưa đăng ký xe nào vào hệ thống.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteVehicle(id) {
        Swal.fire({
            title: 'Xác nhận xóa xe?',
            text: "Hành động này sẽ xóa xe và không thể hoàn tác. Lịch sử sửa chữa liên quan có thể bị ảnh hưởng.",
            icon: 'warning',
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-vehicle-form-' + id).submit();
            }
        })
    }

    function openAddCarModal(model = '', plate = '') {
         // Logic to open modal (Reuse existing dashboard logic or redirect)
         // For now, redirect to dashboard with query seems easiest, or implement a local modal.
         // Let's redirect to Dashboard with a flag to open Modal
         window.location.href = '/staff/dashboard?open_add_modal=1&customer_id={{ $customer->id }}';
    }
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #475569; }
</style>
@endpush
