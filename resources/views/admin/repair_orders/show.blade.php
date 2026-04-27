@extends('layouts.admin')

@section('title', 'Chi Tiết Phiếu Sửa Chữa')

@section('content')
@php
    $statusStyles = [
        'pending' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
        'pending_approval' => 'bg-orange-500/10 text-orange-300 border-orange-500/20',
        'approved' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20',
        'in_progress' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
        'completed' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
        'cancelled' => 'bg-red-500/10 text-red-300 border-red-500/20',
    ];
    $locked = $repairOrder->isLockedForStaffChanges();
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-700 bg-slate-900/50 p-6 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-black text-white">{{ $repairOrder->track_id }}</h2>
                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusStyles[$repairOrder->status] ?? 'bg-slate-700 text-slate-300 border-slate-600' }}">
                    {{ $statusLabels[$repairOrder->status] ?? $repairOrder->status }}
                </span>
            </div>
            <p class="text-sm text-slate-400">
                Tạo lúc {{ $repairOrder->created_at->format('H:i d/m/Y') }} bởi {{ $repairOrder->advisor->name ?? 'Chưa rõ' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            @unless($locked)
                <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 font-bold text-white transition hover:bg-indigo-500">
                    <i class="fas fa-plus"></i>
                    Thêm hạng mục
                </button>

                <form action="{{ route('admin.repair_orders.status', $repairOrder) }}" method="POST">
                    @csrf
                    @if($repairOrder->status === 'pending')
                        <input type="hidden" name="status" value="pending_approval">
                        <button class="rounded-xl bg-orange-600 px-5 py-2.5 font-bold text-white transition hover:bg-orange-500" type="submit">Chờ khách duyệt</button>
                    @elseif($repairOrder->status === 'pending_approval')
                        <input type="hidden" name="status" value="approved">
                        <button class="rounded-xl bg-emerald-600 px-5 py-2.5 font-bold text-white transition hover:bg-emerald-500" type="submit">Đánh dấu đã duyệt</button>
                    @elseif($repairOrder->status === 'approved')
                        <input type="hidden" name="status" value="in_progress">
                        <button class="rounded-xl bg-blue-600 px-5 py-2.5 font-bold text-white transition hover:bg-blue-500" type="submit">Bắt đầu sửa</button>
                    @elseif($repairOrder->status === 'in_progress')
                        <input type="hidden" name="status" value="completed">
                        <button class="rounded-xl bg-green-600 px-5 py-2.5 font-bold text-white transition hover:bg-green-500" type="submit">Hoàn thành</button>
                    @endif
                </form>
            @endunless

            <a href="{{ route('admin.repair_orders.invoice', $repairOrder) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-2.5 font-bold text-white transition hover:bg-slate-600">
                <i class="fas fa-print"></i>
                In hóa đơn
            </a>
            <a href="{{ route('admin.repair_orders.index') }}" class="rounded-xl bg-slate-800 px-4 py-2.5 font-bold text-slate-300 transition hover:text-white">Quay lại</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-slate-400">
                        <i class="fas fa-tasks text-indigo-400"></i>
                        Công việc ({{ $repairOrder->tasks->where('status', 'completed')->count() }}/{{ $repairOrder->tasks->count() }})
                    </h3>
                    @unless($locked)
                        <button onclick="document.getElementById('addTaskModal').classList.remove('hidden')" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-700">
                            <i class="fas fa-plus mr-1"></i>
                            Thêm việc
                        </button>
                    @endunless
                </div>

                <div class="space-y-3">
                    @forelse($repairOrder->tasks->whereNull('parent_id') as $task)
                        <div class="overflow-hidden rounded-xl border border-slate-700 bg-slate-800/50">
                            <div class="flex items-center justify-between gap-3 p-3">
                                <div class="flex items-center gap-3">
                                    @unless($locked)
                                        <form id="task-form-{{ $task->id }}" action="{{ route('admin.repair_tasks.update', $task) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $task->status === 'completed' ? 'pending' : 'completed' }}">
                                            <button type="button" onclick="toggleTask({{ $task->id }}, '{{ $task->status }}', {{ $task->children->where('status', '!=', 'completed')->count() }})" class="flex h-5 w-5 items-center justify-center rounded border-2 {{ $task->status === 'completed' ? 'border-green-500 bg-green-500 text-white' : 'border-slate-600 hover:border-indigo-400' }}">
                                                @if($task->status === 'completed') <i class="fas fa-check text-[10px]"></i> @endif
                                            </button>
                                        </form>
                                    @endunless
                                    <div>
                                        <div class="font-bold text-slate-200 {{ $task->status === 'completed' ? 'line-through opacity-60' : '' }}">{{ $task->title }}</div>
                                        @if($task->description)
                                            <div class="text-xs text-slate-500">{{ $task->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-500">{{ $task->status === 'completed' ? 'Đã xong' : 'Chưa xong' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-slate-500">Chưa có công việc nào.</div>
                    @endforelse
                </div>
            </div>

            <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
                <div class="border-b border-slate-700/50 p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Hạng mục báo giá / chi phí</h3>
                </div>
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-slate-900/60 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Hạng mục</th>
                            <th class="px-6 py-4 text-center">SL</th>
                            <th class="px-6 py-4 text-right">Đơn giá</th>
                            <th class="px-6 py-4 text-right">Thành tiền</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($repairOrder->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $item->itemable->name ?? $item->name ?? 'Hạng mục không còn tồn tại' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->itemable_type === \App\Models\Service::class ? 'Dịch vụ' : ($item->itemable_type === \App\Models\Part::class ? 'Vật tư trong kho' : 'Vật tư ngoài') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-white">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-white">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-right">
                                    @unless($locked)
                                        <form action="{{ route('admin.repair_orders.items.destroy', [$repairOrder, $item]) }}" method="POST" onsubmit="return confirm('Xóa hạng mục này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-400 hover:text-red-300"><i class="fas fa-times"></i></button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">Chưa có hạng mục nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-slate-700 bg-slate-900/80">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-slate-400">Tạm tính</td>
                            <td class="px-6 py-3 text-right font-mono text-white">{{ number_format($repairOrder->subtotal ?? 0, 0, ',', '.') }}đ</td>
                            <td></td>
                        </tr>
                        @if(($repairOrder->discount_amount ?? 0) > 0)
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-slate-400">Giảm giá</td>
                                <td class="px-6 py-3 text-right font-mono text-green-300">-{{ number_format($repairOrder->discount_amount, 0, ',', '.') }}đ</td>
                                <td></td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-300">Tổng cộng</td>
                            <td class="px-6 py-4 text-right text-xl font-black text-indigo-300">{{ number_format($repairOrder->total_amount ?? 0, 0, ',', '.') }}đ</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-6">
                <h3 class="mb-2 text-sm font-bold uppercase text-slate-500">Chẩn đoán / yêu cầu của khách</h3>
                <p class="leading-relaxed text-slate-300">{{ $repairOrder->diagnosis_note ?: 'Chưa có ghi chú.' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-indigo-300">Thanh toán</h3>
                <div class="text-sm text-slate-400">Trạng thái</div>
                <div class="mt-1 font-bold {{ $repairOrder->payment_status === 'paid' ? 'text-green-300' : 'text-red-300' }}">
                    {{ $repairOrder->payment_status === 'paid' ? 'Đã thanh toán' : ($repairOrder->payment_status === 'partial' ? 'Thanh toán một phần' : 'Chưa thanh toán') }}
                </div>
                <p class="mt-4 text-xs text-slate-500">Theo quy trình hiện tại, khách thanh toán tại quầy và nhân viên cập nhật thanh toán.</p>
            </div>

            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-indigo-300">Khách hàng</h3>
                <div class="font-bold text-white">{{ $repairOrder->customer->name ?? 'Khách vãng lai' }}</div>
                <div class="text-sm text-slate-400">{{ $repairOrder->customer->phone ?? 'Chưa có SĐT' }}</div>
                <div class="mt-2 text-sm text-slate-500">{{ $repairOrder->customer->email ?? 'Chưa có email' }}</div>
            </div>

            <div class="glass-panel rounded-2xl border border-slate-700/50 p-6">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-indigo-300">Phương tiện</h3>
                <div class="text-xl font-black text-white">{{ trim(($repairOrder->vehicle->make ?? '').' '.($repairOrder->vehicle->model ?? '')) ?: 'Chưa rõ xe' }}</div>
                <div class="mt-1 inline-block rounded border border-slate-700 bg-slate-900 px-3 py-1 font-mono text-sm text-slate-300">{{ $repairOrder->vehicle->license_plate ?? 'NO-PLATE' }}</div>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-500">VIN</span>
                        <span class="font-mono text-slate-300">{{ $repairOrder->vehicle->vin ?: 'Chưa có' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-500">Số KM</span>
                        <span class="text-slate-300">{{ number_format($repairOrder->odometer_reading ?? 0) }} km</span>
                    </div>
                </div>
                @if($repairOrder->vehicle)
                    <button onclick="open3DModal('{{ route('admin.vehicles.3d', $repairOrder->vehicle->id) }}', '{{ $repairOrder->vehicle->model }}')" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 py-2.5 text-xs font-bold text-white transition hover:bg-slate-700">
                        <i class="fas fa-cube text-teal-300"></i>
                        Xem mô hình 3D
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="addItemModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 p-6">
                <h3 class="text-lg font-bold text-white">Thêm hạng mục</h3>
                <button onclick="document.getElementById('addItemModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.repair_orders.items.store', $repairOrder) }}" method="POST" class="space-y-4 p-6">
                @csrf
                <div class="grid grid-cols-2 gap-2 rounded-lg bg-slate-800 p-1">
                    <button type="button" onclick="switchTab('service')" id="tabService" class="rounded-md bg-indigo-600 py-2 text-sm font-bold text-white">Dịch vụ</button>
                    <button type="button" onclick="switchTab('part')" id="tabPart" class="rounded-md py-2 text-sm font-bold text-slate-400">Vật tư</button>
                </div>
                <input type="hidden" name="type" id="itemType" value="service">
                <div id="serviceSelectDiv">
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Chọn dịch vụ</label>
                    <select name="item_id" id="serviceInput" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} - {{ number_format($service->base_price, 0, ',', '.') }}đ</option>
                        @endforeach
                    </select>
                </div>
                <div id="partSelectDiv" class="hidden">
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Chọn vật tư</label>
                    <select name="item_id_disabled" id="partInput" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white">
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}">{{ $part->name }} - {{ number_format($part->selling_price, 0, ',', '.') }}đ - Tồn: {{ $part->stock_quantity }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Số lượng</label>
                    <input type="number" name="quantity" value="1" min="1" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white">
                </div>
                <button class="w-full rounded-lg bg-indigo-600 py-3 font-bold text-white transition hover:bg-indigo-500">Thêm vào phiếu</button>
            </form>
        </div>
    </div>

    <div id="addTaskModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 p-6">
                <h3 class="text-lg font-bold text-white">Thêm công việc</h3>
                <button onclick="document.getElementById('addTaskModal').classList.add('hidden')" class="text-slate-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.repair_orders.tasks.store', $repairOrder) }}" method="POST" class="space-y-4 p-6">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Tên công việc</label>
                    <input name="title" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white" placeholder="VD: Kiểm tra phanh">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Mô tả</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-white" placeholder="Chi tiết công việc"></textarea>
                </div>
                <button class="w-full rounded-lg bg-indigo-600 py-3 font-bold text-white transition hover:bg-indigo-500">Thêm công việc</button>
            </form>
        </div>
    </div>

    <div id="modal3D" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-md">
        <div class="flex h-[95vh] w-[95vw] flex-col overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
                <h3 class="text-lg font-bold text-white" id="modal3DTitle">Mô hình 3D</h3>
                <button onclick="close3DModal()" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="relative flex-grow bg-black">
                <div id="loader3D" class="absolute inset-0 flex items-center justify-center text-teal-400"><i class="fas fa-circle-notch fa-spin text-4xl"></i></div>
                <iframe id="iframe3D" src="" class="h-full w-full border-0" onload="document.getElementById('loader3D').classList.add('hidden')"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(type) {
    document.getElementById('itemType').value = type;
    const service = document.getElementById('serviceInput');
    const part = document.getElementById('partInput');
    document.getElementById('serviceSelectDiv').classList.toggle('hidden', type !== 'service');
    document.getElementById('partSelectDiv').classList.toggle('hidden', type !== 'part');
    document.getElementById('tabService').classList.toggle('bg-indigo-600', type === 'service');
    document.getElementById('tabService').classList.toggle('text-white', type === 'service');
    document.getElementById('tabPart').classList.toggle('bg-indigo-600', type === 'part');
    document.getElementById('tabPart').classList.toggle('text-white', type === 'part');
    service.name = type === 'service' ? 'item_id' : 'item_id_disabled';
    part.name = type === 'part' ? 'item_id' : 'item_id_disabled';
}

function toggleTask(id, currentStatus, incompleteChildren) {
    if (currentStatus !== 'completed' && incompleteChildren > 0) {
        Swal.fire({ icon: 'error', title: 'Chưa thể hoàn thành', text: 'Vui lòng hoàn thành tất cả công việc con trước.', confirmButtonColor: '#6366f1', background: '#1e293b', color: '#fff' });
        return;
    }
    document.getElementById('task-form-' + id).submit();
}

function open3DModal(url, title) {
    document.getElementById('modal3DTitle').innerText = 'Mô hình 3D: ' + title;
    document.getElementById('loader3D').classList.remove('hidden');
    document.getElementById('modal3D').classList.remove('hidden');
    document.getElementById('iframe3D').src = url + (url.includes('?') ? '&' : '?') + 'iframe=1';
}

function close3DModal() {
    document.getElementById('modal3D').classList.add('hidden');
    document.getElementById('iframe3D').src = 'about:blank';
}
</script>
@endsection
