@extends('layouts.staff')

@section('title', 'Yêu Cầu Vật Tư')

@section('content')
<div class="flex flex-col gap-6 h-full">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">Yêu Cầu Của Tôi</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Theo dõi trạng thái các vật tư bạn đã yêu cầu nhập ngoài</p>
        </div>
        <button type="button" onclick="document.getElementById('createRequestModal').showModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
            <span class="material-icons-round text-base">add_circle</span>
            Tạo yêu cầu
        </button>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden flex-1 flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-400 uppercase bg-slate-50 dark:bg-slate-800/80">
                    <tr>
                        <th class="px-6 py-4">Nhân viên</th>
                        <th class="px-6 py-4">Job / Nội dung</th>
                        <th class="px-6 py-4">SL</th>
                        <th class="px-6 py-4">Giá nhập - Giá bán</th>
                        <th class="px-6 py-4">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($requests as $req)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition group cursor-pointer" onclick="openRequestModal({{ json_encode([
                        'id' => $req->id,
                        'part_name' => $req->part_name,
                        'quantity' => $req->quantity,
                        'cost_price' => $req->cost_price ? number_format($req->cost_price).'đ' : '0đ',
                        'unit_price' => $req->unit_price ? number_format($req->unit_price).'đ' : '0đ',
                        'status' => $req->status,
                        'reason' => $req->reason,
                        'admin_note' => $req->admin_note,
                        'created_at' => $req->created_at->format('d/m/Y H:i'),
                        'repair_order_id' => $req->repair_order_id
                    ]) }})">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 dark:text-slate-200">{{ $req->staff->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 dark:text-slate-100">{{ $req->part_name }}</div>
                            @if($req->repair_order_id)
                                <div class="text-xs text-slate-500 mt-1">Job: <a href="{{ route('staff.order.show', $req->repair_order_id) }}" class="text-indigo-600 hover:underline" onclick="event.stopPropagation()">#{{ $req->repair_order_id }}</a></div>
                            @else
                                <div class="text-xs text-slate-500 mt-1">Lệnh Mua Lẻ</div>
                            @endif
                            @if($req->reason)
                                <div class="text-[10px] text-slate-400 mt-1 italic w-48 truncate" title="{{ $req->reason }}">Lý do: {{ $req->reason }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold">{{ $req->quantity }}</td>
                        <td class="px-6 py-4">
                            <div class="text-red-500">{{ number_format($req->cost_price ?? 0) }}đ</div>
                            <div class="text-teal-600 font-bold">{{ number_format($req->unit_price ?? 0) }}đ</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($req->status === 'pending')
                                <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">Chờ duyệt</span>
                            @elseif($req->status === 'approved')
                                <span class="bg-teal-100 text-teal-700 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">Đã duyệt</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">Từ chối</span>
                            @endif
                            @if($req->admin_note)
                                <div class="text-[10px] text-slate-400 italic mt-2" title="{{ $req->admin_note }}"><i class="fas fa-comment-dots mr-1"></i>Có phản hồi</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">Không có yêu cầu vật tư nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<dialog id="createRequestModal" class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/70 dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-100 p-6 dark:border-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Tạo yêu cầu vật tư</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dùng khi cần mua ngoài hoặc bổ sung vật tư chưa có trong kho.</p>
            </div>
            <button type="button" onclick="document.getElementById('createRequestModal').close()" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">
                <span class="material-icons-round">close</span>
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('staff.requests.store') }}" class="space-y-5 p-6">
        @csrf
        <label class="block">
            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Tên vật tư</span>
            <input name="part_name" value="{{ old('part_name') }}" required maxlength="255" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="VD: Cảm biến áp suất lốp">
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Số lượng</span>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="999" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Mã phiếu sửa chữa</span>
                <input type="number" name="repair_order_id" value="{{ old('repair_order_id') }}" min="1" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Bỏ trống nếu mua lẻ">
            </label>
        </div>

        <label class="block">
            <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Lý do / ghi chú</span>
            <textarea name="reason" rows="4" maxlength="1000" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="VD: Kho không còn hàng, cần mua ngoài để hoàn tất sửa chữa.">{{ old('reason') }}</textarea>
        </label>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
            <button type="button" onclick="document.getElementById('createRequestModal').close()" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Hủy</button>
            <button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Gửi yêu cầu</button>
        </div>
    </form>
</dialog>

<!-- Request Details Modal -->
<div id="requestModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md transform scale-95 transition-transform duration-300">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Chi tiết Yêu cầu</h3>
            <button onclick="closeRequestModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-sm">
            <div class="flex justify-between border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                <span class="text-slate-500 dark:text-slate-400">Tên vật tư:</span>
                <span id="modalPartName" class="font-bold text-slate-800 dark:text-slate-100"></span>
            </div>
            <div class="flex justify-between border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                <span class="text-slate-500 dark:text-slate-400">Số lượng:</span>
                <span id="modalQty" class="font-bold text-slate-800 dark:text-slate-100"></span>
            </div>
            <div class="flex justify-between border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                <span class="text-slate-500 dark:text-slate-400">Giá nhập - Giá bán:</span>
                <span class="font-bold"><span id="modalCostPrice" class="text-red-500"></span> - <span id="modalUnitPrice" class="text-teal-600"></span></span>
            </div>
            <div class="flex justify-between border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                <span class="text-slate-500 dark:text-slate-400">Trạng thái:</span>
                <span id="modalStatus" class="font-bold"></span>
            </div>
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                <span class="text-slate-500 dark:text-slate-400 block mb-1">Lý do mua ngoài:</span>
                <p id="modalReason" class="text-slate-700 dark:text-slate-300 italic bg-gray-50 dark:bg-slate-900/50 p-2 rounded-lg break-words min-h-[40px]"></p>
            </div>
            <div class="pt-2">
                <span class="text-slate-500 dark:text-slate-400 block mb-1">Cấp trên phản hồi:</span>
                <p id="modalAdminNote" class="text-slate-700 dark:text-slate-300 font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 p-3 rounded-lg break-words min-h-[40px] border border-amber-200 dark:border-amber-800/30"></p>
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700 text-right">
            <button onclick="closeRequestModal()" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-bold transition">Đóng</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRequestModal(data) {
    document.getElementById('modalPartName').innerText = data.part_name;
    document.getElementById('modalQty').innerText = data.quantity;
    document.getElementById('modalCostPrice').innerText = data.cost_price;
    document.getElementById('modalUnitPrice').innerText = data.unit_price;
    
    document.getElementById('modalReason').innerText = data.reason || 'Không có lý do';
    document.getElementById('modalAdminNote').innerText = data.admin_note || 'Chưa có phản hồi';

    let statusHtml = '';
    if(data.status === 'pending') statusHtml = '<span class="text-amber-600">Chờ duyệt</span>';
    else if(data.status === 'approved') statusHtml = '<span class="text-teal-600">Đã duyệt</span>';
    else statusHtml = '<span class="text-red-600">Từ chối</span>';
    
    document.getElementById('modalStatus').innerHTML = statusHtml;

    const modal = document.getElementById('requestModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.children[0].classList.remove('scale-95');
    }, 10);
}

function closeRequestModal() {
    const modal = document.getElementById('requestModal');
    modal.classList.add('opacity-0');
    modal.children[0].classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>
@endpush
@endsection
