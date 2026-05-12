@extends('layouts.staff')

@section('title', 'Chi tiết cứu hộ SOS')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    #map { height: clamp(22rem, 48vh, 34rem); width: 100%; z-index: 1; }
    .sos-detail-card { border: 1px solid rgba(148, 163, 184, .22); }
    @media (max-width: 767px) {
        #map { height: 20rem; }
    }
</style>
@endpush

@section('content')
@php
    $statusMeta = [
        'pending' => ['label' => 'Chờ tiếp nhận', 'icon' => 'fa-circle-exclamation', 'class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20'],
        'assigned' => ['label' => 'Đã nhận ca', 'icon' => 'fa-truck', 'class' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/20'],
        'in_progress' => ['label' => 'Đang xử lý', 'icon' => 'fa-route', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20'],
        'completed' => ['label' => 'Hoàn thành', 'icon' => 'fa-check-double', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20'],
        'cancelled' => ['label' => 'Đã hủy', 'icon' => 'fa-ban', 'class' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'],
    ];
    $currentStatus = $statusMeta[$sosInfo->status] ?? $statusMeta['pending'];
    $canCall = $sosInfo->display_phone && $sosInfo->display_phone !== 'Không có SĐT';
    $hasLocation = filled($sosInfo->latitude) && filled($sosInfo->longitude);
    $cancelReasonLabels = [
        'customer_cancelled' => 'Khách hủy yêu cầu',
        'duplicate_request' => 'Yêu cầu bị trùng',
        'invalid_location' => 'Vị trí không hợp lệ',
        'unable_to_contact' => 'Không liên hệ được khách',
        'outside_service_area' => 'Ngoài khu vực hỗ trợ',
        'other' => 'Khác',
    ];
    $canCancel = !in_array($sosInfo->status, ['completed', 'cancelled'], true)
        && (!$sosInfo->assigned_staff_id || $sosInfo->assigned_staff_id === auth()->id() || auth()->user()->role === 'admin');
@endphp

<div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <a href="{{ route('staff.sos.index') }}" class="mt-1 flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wide {{ $currentStatus['class'] }}">
                            <i class="fas {{ $currentStatus['icon'] }}"></i>
                            {{ $currentStatus['label'] }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">#{{ $sosInfo->id }}</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white md:text-3xl">Chi tiết yêu cầu cứu hộ</h1>
                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">Gửi lúc {{ $sosInfo->created_at->format('H:i d/m/Y') }} ({{ $sosInfo->created_at->diffForHumans() }})</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:min-w-[34rem] xl:grid-cols-4">
                @if($canCall)
                    <a href="tel:{{ $sosInfo->display_phone }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-500">
                        <i class="fas fa-phone"></i>
                        Gọi khách
                    </a>
                @endif
                @if($hasLocation)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $sosInfo->latitude }},{{ $sosInfo->longitude }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-500">
                        <i class="fas fa-diamond-turn-right"></i>
                        Chỉ đường
                    </a>
                @endif
                @if($sosInfo->status === 'pending')
                    <button type="button" onclick="acceptSos({{ $sosInfo->id }})" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-black text-white transition hover:bg-red-500">
                        <i class="fas fa-hand"></i>
                        Nhận ca
                    </button>
                @elseif(in_array($sosInfo->status, ['assigned', 'in_progress'], true) && $sosInfo->assigned_staff_id === auth()->id())
                    <button type="button" onclick="updateStatus({{ $sosInfo->id }}, 'completed')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3 text-sm font-black text-white transition hover:bg-teal-500">
                        <i class="fas fa-check-double"></i>
                        Hoàn thành
                    </button>
                @endif
                @if($canCancel)
                    <button type="button" onclick="cancelSos({{ $sosInfo->id }})" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                        <i class="fas fa-ban"></i>
                        Hủy SOS
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,.72fr)_minmax(0,1.28fr)]">
        <aside class="space-y-5">
            <div class="sos-detail-card rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="font-black text-slate-900 dark:text-white">Người yêu cầu</h2>
                    @if(!$sosInfo->customer_id)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-300">Khách vãng lai</span>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-lg font-black text-slate-900 dark:text-white">{{ $sosInfo->display_name }}</div>
                        <div class="mt-1 text-sm font-bold text-slate-500 dark:text-slate-400"><i class="fas fa-phone mr-1"></i>{{ $sosInfo->display_phone }}</div>
                    </div>
                </div>
            </div>

            <div class="sos-detail-card rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                <h2 class="mb-4 font-black text-slate-900 dark:text-white">Thông tin xe</h2>
                @if($sosInfo->vehicle)
                    <div class="flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <i class="fas fa-car text-lg"></i>
                        </div>
                        <div>
                            <div class="text-lg font-black uppercase text-slate-900 dark:text-white">{{ $sosInfo->vehicle->license_plate }}</div>
                            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ trim(($sosInfo->vehicle->make ?? '') . ' ' . ($sosInfo->vehicle->model ?? '')) ?: 'Chưa có dòng xe' }}</div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm font-bold text-slate-500 dark:border-slate-700 dark:text-slate-400">Xe ngoài hệ thống hoặc khách chưa chọn xe.</div>
                @endif
            </div>

            <div class="sos-detail-card rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                <h2 class="mb-4 font-black text-slate-900 dark:text-white">Mô tả sự cố</h2>
                <div class="rounded-xl border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium leading-6 text-slate-700 dark:bg-red-500/10 dark:text-slate-200">
                    {!! nl2br(e($sosInfo->description ?: 'Khách chưa nhập mô tả chi tiết.')) !!}
                </div>
            </div>

            @if($sosInfo->status === 'cancelled')
                <div class="sos-detail-card rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                    <h2 class="mb-4 font-black text-slate-900 dark:text-white">Thông tin hủy SOS</h2>
                    <div class="space-y-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                        <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                            <span class="text-slate-400">Lý do</span>
                            <span class="text-right font-black text-slate-800 dark:text-slate-100">{{ $cancelReasonLabels[$sosInfo->cancel_reason] ?? 'Khác' }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            {!! nl2br(e($sosInfo->cancel_note ?: 'Không có ghi chú.')) !!}
                        </div>
                        <div class="text-xs text-slate-400">
                            Hủy bởi {{ $sosInfo->cancelledBy->name ?? 'Hệ thống' }}
                            @if($sosInfo->cancelled_at)
                                lúc {{ $sosInfo->cancelled_at->format('H:i d/m/Y') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(in_array($sosInfo->status, ['assigned', 'in_progress'], true) && $sosInfo->assigned_staff_id === auth()->id())
                <div class="sos-detail-card rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                    <h2 class="mb-1 font-black text-slate-900 dark:text-white">Thao tác xử lý</h2>
                    <p class="mb-4 text-sm font-medium text-slate-500 dark:text-slate-400">Bạn đang phụ trách ca cứu hộ này.</p>
                    <div class="grid gap-3">
                        @if($sosInfo->status === 'assigned')
                            <button type="button" onclick="updateStatus({{ $sosInfo->id }}, 'in_progress')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-400">
                                <i class="fas fa-route"></i>
                                Bắt đầu di chuyển
                            </button>
                            <button type="button" onclick="unassignSos({{ $sosInfo->id }})" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                <i class="fas fa-xmark"></i>
                                Trả ca về danh sách chờ
                            </button>
                        @endif
                    </div>
                </div>
            @elseif($sosInfo->assigned_staff_id && !in_array($sosInfo->status, ['completed', 'cancelled'], true))
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm font-medium text-blue-800 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-200">
                    Nhân viên <strong>{{ $sosInfo->assignedStaff->name ?? 'khác' }}</strong> đang xử lý ca này.
                </div>
            @endif
        </aside>

        <main class="space-y-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-slate-800 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="font-black text-slate-900 dark:text-white">Vị trí cứu hộ</h2>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Dùng nút chỉ đường để mở Google Maps từ vị trí hiện tại đến khách.</p>
                    </div>
                    @if($hasLocation)
                        <div class="flex flex-wrap gap-2 text-xs font-mono text-slate-500 dark:text-slate-400">
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 dark:bg-slate-800">LAT {{ $sosInfo->latitude }}</span>
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 dark:bg-slate-800">LNG {{ $sosInfo->longitude }}</span>
                        </div>
                    @endif
                </div>
                @if($hasLocation)
                    <div id="map"></div>
                @else
                    <div class="flex h-80 items-center justify-center p-6 text-center text-sm font-bold text-slate-500 dark:text-slate-400">Yêu cầu này chưa có tọa độ vị trí.</div>
                @endif
            </div>

            @if($sosInfo->images && is_array($sosInfo->images) && count($sosInfo->images) > 0)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-4 font-black text-slate-900 dark:text-white">Ảnh hiện trường</h2>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach($sosInfo->images as $img)
                            <a href="{{ Storage::url($img) }}" target="_blank" class="group relative block aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                                <img src="{{ Storage::url($img) }}" alt="Ảnh hiện trường SOS" class="h-full w-full object-cover transition group-hover:scale-105">
                                <span class="absolute inset-0 flex items-center justify-center bg-black/45 text-xl text-white opacity-0 transition group-hover:opacity-100">
                                    <i class="fas fa-magnifying-glass-plus"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </main>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hasLocation = @json($hasLocation);
    if (!hasLocation) return;

    const lat = @json((float) $sosInfo->latitude);
    const lng = @json((float) $sosInfo->longitude);
    const customerName = @js($sosInfo->display_name);
    const map = L.map('map').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: 'OpenStreetMap'
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`<b>Vị trí khách báo sự cố</b><br>${customerName}`).openPopup();
    setTimeout(() => map.invalidateSize(), 250);
});

function acceptSos(id) {
    Swal.fire({
        title: 'Nhận ca cứu hộ?',
        text: 'Bạn sẽ phụ trách xử lý yêu cầu SOS này.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Nhận ca',
        cancelButtonText: 'Hủy'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`{{ url('staff/sos') }}/${id}/accept`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Đã nhận ca', data.message || 'Bạn đã nhận ca cứu hộ.', 'success').then(() => window.location.reload());
                return;
            }
            Swal.fire('Không thể nhận ca', data.message || 'Vui lòng thử lại.', 'error');
        })
        .catch(() => Swal.fire('Lỗi kết nối', 'Không thể nhận ca lúc này.', 'error'));
    });
}

function updateStatus(id, newStatus) {
    const isMoving = newStatus === 'in_progress';
    Swal.fire({
        title: isMoving ? 'Bắt đầu di chuyển?' : 'Hoàn thành ca cứu hộ?',
        text: isMoving ? 'Xác nhận bạn đã bắt đầu di chuyển đến khách.' : 'Xác nhận ca cứu hộ đã hoàn thành.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: isMoving ? '#f59e0b' : '#14b8a6',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`{{ url('staff/sos') }}/${id}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Đã cập nhật', data.message || 'Trạng thái ca cứu hộ đã được cập nhật.', 'success').then(() => window.location.reload());
                return;
            }
            Swal.fire('Không thể cập nhật', data.message || 'Vui lòng thử lại.', 'error');
        })
        .catch(() => Swal.fire('Lỗi kết nối', 'Không thể cập nhật trạng thái lúc này.', 'error'));
    });
}

function cancelSos(id) {
    window.dispatchEvent(new CustomEvent('staff-sos-alert:pause'));

    Swal.fire({
        title: 'Hủy yêu cầu cứu hộ?',
        html: `
            <div class="space-y-3 text-left">
                <label class="block text-sm font-bold text-slate-700">Lý do hủy</label>
                <select id="sosCancelReason" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Chọn lý do</option>
                    <option value="customer_cancelled">Khách hủy yêu cầu</option>
                    <option value="duplicate_request">Yêu cầu bị trùng</option>
                    <option value="invalid_location">Vị trí không hợp lệ</option>
                    <option value="unable_to_contact">Không liên hệ được khách</option>
                    <option value="outside_service_area">Ngoài khu vực hỗ trợ</option>
                    <option value="other">Khác</option>
                </select>
                <label class="block text-sm font-bold text-slate-700">Nội dung hủy</label>
                <textarea id="sosCancelNote" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Nhập rõ lý do để Admin xem lại trong nhật ký..."></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Hủy SOS',
        cancelButtonText: 'Quay lại',
        focusConfirm: false,
        didOpen: () => {
            document.getElementById('sosCancelReason')?.focus();
        },
        preConfirm: () => {
            const reason = document.getElementById('sosCancelReason')?.value;
            const note = document.getElementById('sosCancelNote')?.value.trim();

            if (!reason) {
                Swal.showValidationMessage('Vui lòng chọn lý do hủy.');
                return false;
            }

            if (!note || note.length < 5) {
                Swal.showValidationMessage('Vui lòng nhập nội dung hủy tối thiểu 5 ký tự.');
                return false;
            }

            return { cancel_reason: reason, cancel_note: note };
        }
    }).then(result => {
        if (!result.isConfirmed) {
            window.dispatchEvent(new CustomEvent('staff-sos-alert:resume'));
            return;
        }

        fetch(`{{ url('staff/sos') }}/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(result.value)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Đã hủy SOS', data.message || 'Yêu cầu cứu hộ đã được hủy.', 'success').then(() => {
                    window.location.href = data.redirect || window.location.href;
                });
                return;
            }
            window.dispatchEvent(new CustomEvent('staff-sos-alert:resume'));
            Swal.fire('Không thể hủy SOS', data.message || 'Vui lòng thử lại.', 'error');
        })
        .catch(() => {
            window.dispatchEvent(new CustomEvent('staff-sos-alert:resume'));
            Swal.fire('Lỗi kết nối', 'Không thể hủy SOS lúc này.', 'error');
        });
    });
}

function unassignSos(id) {
    Swal.fire({
        title: 'Trả ca cứu hộ?',
        text: 'Ca này sẽ quay lại danh sách chờ tiếp nhận.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Trả ca',
        cancelButtonText: 'Giữ lại'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`{{ url('staff/sos') }}/${id}/unassign`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Đã trả ca', data.message || 'Ca cứu hộ đã được trả về danh sách chờ.', 'success').then(() => {
                    window.location.href = data.redirect || '{{ route('staff.sos.index') }}';
                });
                return;
            }
            Swal.fire('Không thể trả ca', data.message || 'Vui lòng thử lại.', 'error');
        })
        .catch(() => Swal.fire('Lỗi kết nối', 'Không thể trả ca lúc này.', 'error'));
    });
}
</script>
@endpush
