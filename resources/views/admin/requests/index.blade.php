@extends('layouts.admin')

@section('title', 'Phê Duyệt Vật Tư')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Yêu cầu hôm nay</p>
            <h3 class="mt-2 text-2xl font-black text-white">{{ $todayCount }}</h3>
            <p class="mt-2 text-xs text-slate-500">Tạo hoặc xử lý trong ngày</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Chờ duyệt</p>
            <h3 class="mt-2 text-2xl font-black text-amber-300">{{ $pendingRequests->count() }}</h3>
            <p class="mt-2 text-xs text-amber-300">Cần xử lý</p>
        </div>
        <div class="glass-panel rounded-xl border border-slate-700/50 p-5">
            <p class="text-sm font-medium text-slate-400">Đã xử lý gần đây</p>
            <h3 class="mt-2 text-2xl font-black text-indigo-300">{{ $historyRequests->count() }}</h3>
            <p class="mt-2 text-xs text-slate-500">30 yêu cầu mới nhất</p>
        </div>
    </div>

    <div>
        <h2 class="flex items-center gap-2 text-2xl font-bold text-white">
            <span class="material-icons-round text-indigo-400">verified_user</span>
            Phê Duyệt Vật Tư
        </h2>
        <p class="mt-1 text-sm text-slate-400">Duyệt vật tư nhân viên yêu cầu mua ngoài hoặc bổ sung vào phiếu sửa chữa.</p>
    </div>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-lg font-bold text-white">
                <span class="material-icons-round animate-pulse text-amber-300">pending</span>
                Chờ duyệt
            </h3>
            <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300">{{ $pendingRequests->count() }} yêu cầu</span>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($pendingRequests as $requestItem)
                <div class="relative overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900/70 p-6 shadow-lg">
                    <div class="absolute bottom-0 left-0 top-0 w-1.5 bg-amber-400"></div>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-1 items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">
                                {{ mb_substr($requestItem->staff->name ?? 'NV', 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <h4 class="text-xl font-bold text-white">{{ $requestItem->part_name }}</h4>
                                    <span class="rounded border border-indigo-500/20 bg-indigo-500/10 px-2 py-0.5 text-xs font-bold text-indigo-300">x{{ $requestItem->quantity }}</span>
                                </div>
                                <div class="mb-3 flex flex-wrap items-center gap-2 text-sm text-slate-400">
                                    <span class="font-medium text-slate-300">{{ $requestItem->staff->name ?? 'Không rõ nhân viên' }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-500"></span>
                                    <span>{{ $requestItem->created_at->diffForHumans() }}</span>
                                    @if($requestItem->repairOrder)
                                        <span class="h-1 w-1 rounded-full bg-slate-500"></span>
                                        <span>Phiếu {{ $requestItem->repairOrder->track_id ?? '#'.$requestItem->repair_order_id }}</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                                    <div class="rounded-lg border border-slate-800 bg-black/20 p-3">
                                        <div class="text-xs text-slate-500">Giá nhập</div>
                                        <div class="font-bold text-red-300">{{ number_format($requestItem->cost_price ?? 0, 0, ',', '.') }}đ</div>
                                    </div>
                                    <div class="rounded-lg border border-slate-800 bg-black/20 p-3">
                                        <div class="text-xs text-slate-500">Giá bán</div>
                                        <div class="font-bold text-emerald-300">{{ number_format($requestItem->unit_price ?? 0, 0, ',', '.') }}đ</div>
                                    </div>
                                    <div class="rounded-lg border border-slate-800 bg-black/20 p-3">
                                        <div class="text-xs text-slate-500">Thành tiền dự kiến</div>
                                        <div class="font-bold text-white">{{ number_format(($requestItem->unit_price ?? 0) * $requestItem->quantity, 0, ',', '.') }}đ</div>
                                    </div>
                                </div>
                                @if($requestItem->reason)
                                    <p class="mt-3 rounded-lg border border-white/5 bg-black/20 p-3 text-sm italic text-slate-300">{{ $requestItem->reason }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3 self-end lg:self-center">
                            <button onclick="openProcessModal({{ $requestItem->id }}, @js($requestItem->part_name), 'approved')" class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-300 transition hover:bg-emerald-600 hover:text-white" title="Duyệt">
                                <span class="material-icons-round">check</span>
                            </button>
                            <button onclick="openProcessModal({{ $requestItem->id }}, @js($requestItem->part_name), 'rejected')" class="flex h-12 w-12 items-center justify-center rounded-full border border-red-500/20 bg-red-500/10 text-red-300 transition hover:bg-red-600 hover:text-white" title="Từ chối">
                                <span class="material-icons-round">close</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 p-10 text-center text-slate-500">Không có yêu cầu nào đang chờ duyệt.</div>
            @endforelse
        </div>
    </section>

    <section class="space-y-4">
        <h3 class="flex items-center gap-2 text-lg font-bold text-white">
            <span class="material-icons-round text-slate-400">history</span>
            Lịch sử xử lý
        </h3>
        <div class="overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900/70">
            <ul class="divide-y divide-slate-800">
                @forelse($historyRequests as $requestItem)
                    <li class="p-4 transition hover:bg-white/[0.03]">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-2.5 w-2.5 rounded-full {{ $requestItem->status === 'approved' ? 'bg-emerald-400' : 'bg-red-400' }}"></div>
                                <div>
                                    <p class="font-medium text-white">{{ $requestItem->part_name }} <span class="ml-1 text-xs text-slate-400">x{{ $requestItem->quantity }}</span></p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $requestItem->staff->name ?? 'Không rõ nhân viên' }} | {{ $requestItem->updated_at->format('d/m/Y H:i') }}</p>
                                    @if($requestItem->admin_note)
                                        <p class="mt-1 text-xs italic text-slate-500">{{ $requestItem->admin_note }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="rounded px-2 py-1 text-xs font-bold {{ $requestItem->status === 'approved' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300' }}">
                                {{ $requestItem->status === 'approved' ? 'Đã duyệt' : 'Từ chối' }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-slate-500">Chưa có lịch sử xử lý.</li>
                @endforelse
            </ul>
        </div>
    </section>
</div>

<dialog id="processModal" class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-700 bg-slate-900 p-0 text-white shadow-2xl backdrop:bg-black/80">
    <div id="modalHeader" class="bg-emerald-600 p-6 text-white">
        <h3 class="flex items-center gap-2 text-xl font-black">
            <span id="modalIcon" class="material-icons-round">check_circle</span>
            <span id="modalTitle">Duyệt yêu cầu</span>
        </h3>
        <p id="modalPartNameDisplay" class="mt-1 text-sm font-medium text-white/80"></p>
    </div>

    <form id="processForm" method="POST" class="p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="modalStatusInput">

        <div class="mb-6">
            <label class="mb-2 block text-sm font-bold uppercase tracking-wider text-slate-400">Ghi chú cho nhân viên</label>
            <textarea name="admin_note" id="admin_note" rows="4" class="w-full resize-none rounded-2xl border border-slate-700 bg-slate-950 p-4 text-white outline-none focus:border-indigo-500" placeholder="Nhập hướng dẫn hoặc lý do từ chối..."></textarea>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="document.getElementById('processModal').close()" class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 font-bold text-slate-300 transition hover:bg-white/10">Hủy</button>
            <button id="modalSubmitBtn" class="rounded-2xl bg-emerald-600 px-8 py-3 font-bold text-white transition hover:bg-emerald-500">Xác nhận</button>
        </div>
    </form>
</dialog>

<script>
function openProcessModal(id, partName, status) {
    const modal = document.getElementById('processModal');
    const form = document.getElementById('processForm');
    const header = document.getElementById('modalHeader');
    const icon = document.getElementById('modalIcon');
    const title = document.getElementById('modalTitle');
    const submit = document.getElementById('modalSubmitBtn');
    const note = document.getElementById('admin_note');

    form.action = `/admin/material-requests/${id}`;
    document.getElementById('modalStatusInput').value = status;
    document.getElementById('modalPartNameDisplay').innerText = partName;
    note.value = '';

    if (status === 'approved') {
        header.className = 'bg-emerald-600 p-6 text-white';
        icon.innerText = 'check_circle';
        title.innerText = 'Duyệt yêu cầu';
        submit.className = 'rounded-2xl bg-emerald-600 px-8 py-3 font-bold text-white transition hover:bg-emerald-500';
        submit.innerText = 'Duyệt';
        note.required = false;
        note.placeholder = 'VD: Đã duyệt, nhận vật tư tại kho.';
    } else {
        header.className = 'bg-red-600 p-6 text-white';
        icon.innerText = 'cancel';
        title.innerText = 'Từ chối yêu cầu';
        submit.className = 'rounded-2xl bg-red-600 px-8 py-3 font-bold text-white transition hover:bg-red-500';
        submit.innerText = 'Từ chối';
        note.required = true;
        note.placeholder = 'Nhập lý do từ chối để nhân viên nắm rõ.';
    }

    modal.showModal();
}
</script>
@endsection
