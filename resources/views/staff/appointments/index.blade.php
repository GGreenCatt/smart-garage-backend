@extends('layouts.staff')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-black text-slate-800">Quản Lý Lịch Hẹn</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold border-b border-slate-200">Khách hàng</th>
                        <th class="p-4 font-bold border-b border-slate-200">Xe</th>
                        <th class="p-4 font-bold border-b border-slate-200">Dịch vụ / Lý do</th>
                        <th class="p-4 font-bold border-b border-slate-200">Thời gian</th>
                        <th class="p-4 font-bold border-b border-slate-200">Trạng thái</th>
                        <th class="p-4 font-bold border-b border-slate-200 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($appointments as $appt)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $appt->customer->name ?? 'Khách lẻ' }}</div>
                            <div class="text-xs text-slate-500">{{ $appt->customer->phone ?? '' }}</div>
                        </td>
                        <td class="p-4">
                            @if($appt->vehicle)
                                <div class="font-bold text-slate-700 uppercase">{{ $appt->vehicle->license_plate }}</div>
                                <div class="text-xs text-slate-500">{{ $appt->vehicle->model }}</div>
                            @else
                                <div class="font-bold text-slate-700 uppercase">{{ $appt->license_plate ?? 'Chưa rõ' }}</div>
                                <div class="text-xs text-slate-500">{{ $appt->vehicle_name ?? 'Chưa rõ' }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">{{ $appt->service->name ?? 'Khác' }}</div>
                            @if($appt->reason)
                                <div class="text-xs text-slate-500 max-w-xs truncate" title="{{ $appt->reason }}">{{ $appt->reason }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-indigo-600">{{ $appt->scheduled_at->format('H:i') }}</div>
                            <div class="text-xs text-slate-500">{{ $appt->scheduled_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="p-4">
                            @php
                                $statusClass = match($appt->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'confirmed' => 'bg-green-100 text-green-700',
                                    'completed' => 'bg-blue-100 text-blue-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-700'
                                };
                                $statusText = match($appt->status) {
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'completed' => 'Đã tiếp nhận',
                                    'cancelled' => 'Đã hủy',
                                    default => 'Không rõ'
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-bold uppercase {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                                @if(in_array($appt->status, ['pending']))
                                <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Xác nhận">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if(in_array($appt->status, ['pending', 'confirmed']))
                                <form action="{{ route('staff.appointments.convert', $appt->id) }}" method="POST" onsubmit="return confirm('Tiếp nhận xe và tạo Lệnh Sửa Chữa?');">
                                    @csrf
                                    <button type="submit" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Tạo Lệnh Sửa Chữa (Tiếp nhận xe)">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                </form>
                                <form action="{{ route('staff.appointments.update', $appt->id) }}" method="POST" onsubmit="return confirm('Hủy lịch hẹn này?');">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hủy lịch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif

                                <!-- Nút Sửa -->
                                <button onclick="openEditModal({{ $appt->id }}, '{{ $appt->scheduled_at->format('Y-m-d\TH:i') }}', '{{ $appt->service_id }}', `{{ htmlspecialchars($appt->reason) }}`, `{{ htmlspecialchars($appt->notes) }}`, '{{ $appt->status }}', `{{ $appt->vehicle ? ($appt->vehicle->license_plate . ' • ' . $appt->vehicle->model) : ($appt->license_plate ? $appt->license_plate . ' • ' . ($appt->vehicle_name ?? '') : 'Chưa rõ xe') }}`)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Chỉnh sửa chi tiết">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            <i class="fas fa-calendar-times text-4xl mb-3"></i>
                            <p>Không có lịch hẹn nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa Lịch Hẹn -->
<dialog id="editModal" class="bg-transparent p-0 m-auto w-full max-w-2xl backdrop:bg-slate-900/80 backdrop:backdrop-blur-sm open:animate-[fade-in_0.2s_ease-out]">
    <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden text-slate-200">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-calendar-alt text-primary"></i>
                Chi tiết Lịch Hẹn
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="editForm" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-5 text-sm">
                    <div>
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Phương tiện (Chỉ xem)</span>
                        <input type="text" id="edit_vehicle" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-3 py-2 text-slate-300 outline-none cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Thời gian hẹn</span>
                        <input type="datetime-local" name="scheduled_at" id="edit_scheduled_at" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:border-primary outline-none" required>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Dịch vụ</span>
                    <select name="service_id" id="edit_service_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:border-primary outline-none">
                        <option value="">-- Chưa xác định / Tư vấn thêm --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} ({{ number_format($service->price) }}đ)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                    <div>
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Lý do / Yêu cầu (KH)</span>
                        <textarea name="reason" id="edit_reason" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:border-primary outline-none placeholder-slate-600" placeholder="Chưa có thông tin..."></textarea>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Ghi chú (Nội bộ)</span>
                        <textarea name="notes" id="edit_notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:border-primary outline-none placeholder-slate-600" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2">
                    <div class="flex-1">
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-1">Trạng thái</span>
                        <select name="status" id="edit_status" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 text-slate-200 focus:border-primary outline-none font-semibold">
                            <option value="pending">Chờ xác nhận</option>
                            <option value="confirmed">Đã xác nhận</option>
                            <option value="cancelled">Hủy</option>
                            <option value="completed">Hoàn thành</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2 text-slate-300 hover:text-white transition-colors">Hủy</button>
                    <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2 rounded-lg font-medium shadow-md transition-all">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<script>
    const modal = document.getElementById('editModal');
    
    function openEditModal(id, scheduled_at, service_id, reason, notes, status, vehicle_desc) {
        document.getElementById('editForm').action = `/staff/appointments/${id}`;
        
        document.getElementById('edit_scheduled_at').value = scheduled_at;
        document.getElementById('edit_service_id').value = service_id || '';
        document.getElementById('edit_reason').value = reason || '';
        document.getElementById('edit_notes').value = notes || '';
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_vehicle').value = vehicle_desc;
        
        modal.showModal();
    }
    
    function closeEditModal() {
        modal.close();
    }
</script>
@endsection
