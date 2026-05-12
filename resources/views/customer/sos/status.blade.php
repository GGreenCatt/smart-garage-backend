@extends('layouts.customer')

@section('title', 'Trạng thái cứu hộ SOS')

@section('body_class', 'overflow-x-hidden bg-slate-950')

@section('footer')
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    .sos-status-shell { min-height: 100dvh; background: #0f172a; }
    .sos-status-map { height: clamp(17rem, 38vh, 28rem); width: 100%; z-index: 0; }
    .sos-panel { border: 1px solid rgba(148, 163, 184, .18); background: rgba(15, 23, 42, .92); }
    .sos-step { border-color: rgba(148, 163, 184, .2); color: #94a3b8; }
    .sos-step.is-done { border-color: rgba(45, 212, 191, .45); background: rgba(20, 184, 166, .12); color: #ccfbf1; }
    .sos-step.is-current { border-color: rgba(251, 191, 36, .65); background: rgba(245, 158, 11, .16); color: #fef3c7; }
    .sos-flow-item { border-color: rgba(148, 163, 184, .16); background: rgba(255, 255, 255, .045); }
    .sos-flow-item.is-current { border-color: rgba(251, 191, 36, .45); background: rgba(245, 158, 11, .12); }
    @media (max-width: 767px) {
        .sos-status-map { height: 17rem; }
        .sos-bottom-actions { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
    }
</style>
@endpush

@section('content')
@php
    $statusIndex = [
        'pending' => 1,
        'assigned' => 2,
        'in_progress' => 3,
        'completed' => 4,
        'cancelled' => 0,
    ][$activeSos->status] ?? 1;

    $statusMeta = [
        'pending' => ['title' => 'Đang tìm nhân viên', 'text' => 'Yêu cầu của bạn đã được gửi đến đội cứu hộ. Chúng tôi đang điều phối nhân viên phù hợp.', 'icon' => 'fa-magnifying-glass-location', 'tone' => 'from-amber-500 to-orange-500'],
        'assigned' => ['title' => 'Đã có nhân viên phụ trách', 'text' => 'Nhân viên cứu hộ đã nhận thông tin và sẽ liên hệ với bạn để xác nhận vị trí.', 'icon' => 'fa-user-check', 'tone' => 'from-blue-600 to-indigo-500'],
        'in_progress' => ['title' => 'Nhân viên đang đến', 'text' => 'Nhân viên cứu hộ đang di chuyển đến vị trí sự cố. Hãy giữ điện thoại liên lạc và đứng ở nơi an toàn.', 'icon' => 'fa-truck-fast', 'tone' => 'from-teal-500 to-emerald-500'],
    ][$activeSos->status] ?? ['title' => 'Đang xử lý SOS', 'text' => 'Hệ thống đang cập nhật trạng thái mới nhất.', 'icon' => 'fa-truck-medical', 'tone' => 'from-slate-600 to-slate-500'];

    $steps = [
        1 => ['label' => 'Đã gửi', 'icon' => 'fa-paper-plane', 'desc' => 'Hệ thống đã nhận yêu cầu.'],
        2 => ['label' => 'Đã tiếp nhận', 'icon' => 'fa-user-check', 'desc' => 'Đã có nhân viên phụ trách.'],
        3 => ['label' => 'Đang đến', 'icon' => 'fa-route', 'desc' => 'Nhân viên đang di chuyển.'],
        4 => ['label' => 'Hoàn tất', 'icon' => 'fa-circle-check', 'desc' => 'Yêu cầu đã được xử lý.'],
    ];
@endphp

<div class="sos-status-shell pt-20 text-white">
    <main class="mx-auto grid min-h-[calc(100dvh-5rem)] max-w-7xl grid-cols-1 gap-4 px-4 pb-28 pt-4 md:grid-cols-[minmax(0,.92fr)_minmax(0,1.08fr)] md:px-6 md:pb-8">
        <section class="sos-panel overflow-hidden rounded-2xl md:order-2">
            <div class="relative bg-gradient-to-br {{ $statusMeta['tone'] }} p-5 md:p-6">
                <div class="relative z-10 flex items-start gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white/18 text-white backdrop-blur">
                        <i class="fas {{ $statusMeta['icon'] }} text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-black uppercase tracking-widest text-white/70">SOS #{{ $activeSos->id }}</div>
                        <h1 class="mt-1 text-2xl font-black tracking-tight md:text-4xl">{{ $statusMeta['title'] }}</h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold text-white/82">{{ $statusMeta['text'] }}</p>
                    </div>
                </div>
                <i class="fas fa-truck-medical absolute -bottom-10 -right-5 text-9xl text-white/10"></i>
            </div>

            <div class="grid grid-cols-4 gap-2 border-b border-white/10 p-4">
                @foreach($steps as $number => $step)
                    <div class="sos-step rounded-xl border p-3 text-center {{ $number < $statusIndex ? 'is-done' : ($number === $statusIndex ? 'is-current' : '') }}">
                        <i class="fas {{ $step['icon'] }} text-base"></i>
                        <div class="mt-2 text-[11px] font-black uppercase">{{ $step['label'] }}</div>
                        <div class="mt-1 hidden text-[10px] font-semibold leading-4 opacity-75 md:block">{{ $step['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="relative">
                <div id="mapStatus" class="sos-status-map bg-slate-900"></div>
                <div class="absolute bottom-4 left-4 right-4 z-[420] rounded-xl bg-slate-950/82 px-3 py-2 text-xs font-bold text-slate-100 backdrop-blur">
                    Vị trí sự cố: {{ number_format($activeSos->latitude, 5) }}, {{ number_format($activeSos->longitude, 5) }}
                </div>
            </div>
        </section>

        <aside class="space-y-4 md:order-1">
            @if($activeSos->assignedStaff)
                <section class="sos-panel rounded-2xl p-4 md:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black">Nhân viên phụ trách</h2>
                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-black text-emerald-100">Đã nhận ca</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-500 text-xl font-black text-white">
                            {{ mb_substr($activeSos->assignedStaff->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-lg font-black">{{ $activeSos->assignedStaff->name }}</div>
                            <div class="mt-1 text-sm text-slate-400">{{ $activeSos->assignedStaff->phone ?: 'Chưa có số điện thoại' }}</div>
                        </div>
                    </div>
                    @if($activeSos->assignedStaff->phone)
                        <a href="tel:{{ $activeSos->assignedStaff->phone }}" class="mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 text-sm font-black text-white transition hover:bg-emerald-400">
                            <i class="fas fa-phone"></i>
                            Gọi nhân viên
                        </a>
                    @endif
                </section>
            @else
                <section class="sos-panel rounded-2xl p-5 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-200">
                        <i class="fas fa-circle-notch fa-spin text-2xl"></i>
                    </div>
                    <h2 class="mt-4 text-lg font-black">Đang điều phối nhân viên gần nhất</h2>
                    <p class="mt-2 text-sm text-slate-400">Trang này tự cập nhật. Bạn có thể gọi gara nếu cần trao đổi gấp.</p>
                    <a href="tel:19001234" class="mt-4 flex h-12 items-center justify-center gap-2 rounded-xl bg-emerald-500 text-sm font-black text-white">
                        <i class="fas fa-phone"></i>
                        Gọi gara
                    </a>
                </section>
            @endif

            <section class="sos-panel rounded-2xl p-4 md:p-5">
                <h2 class="mb-4 text-lg font-black">Luồng xử lý SOS</h2>
                <div class="space-y-2">
                    @foreach($steps as $number => $step)
                        <div class="sos-flow-item rounded-xl border p-3 {{ $number === $statusIndex ? 'is-current' : '' }}">
                            <div class="flex items-start gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $number < $statusIndex ? 'bg-teal-500 text-white' : ($number === $statusIndex ? 'bg-amber-400 text-slate-950' : 'bg-white/10 text-slate-400') }}">
                                    <i class="fas {{ $step['icon'] }} text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black">{{ $step['label'] }}</div>
                                    <div class="mt-1 text-xs font-medium leading-5 text-slate-400">{{ $step['desc'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="sos-panel rounded-2xl p-4 md:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black">Thông tin yêu cầu</h2>
                    <span class="text-xs font-bold text-slate-400">{{ $activeSos->created_at->format('H:i d/m') }}</span>
                </div>

                @if($activeSos->vehicle)
                    <div class="mb-3 rounded-xl border border-white/10 bg-white/5 p-3">
                        <div class="text-xs font-black uppercase text-slate-400">Xe gặp sự cố</div>
                        <div class="mt-1 font-black">{{ $activeSos->vehicle->license_plate }} - {{ trim(($activeSos->vehicle->make ?? '').' '.$activeSos->vehicle->model) }}</div>
                    </div>
                @endif

                <div class="rounded-xl border border-red-400/20 bg-red-500/10 p-4 text-sm font-semibold leading-relaxed text-red-50">
                    {{ $activeSos->description }}
                </div>

                @if($activeSos->images && count($activeSos->images) > 0)
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach($activeSos->images as $image)
                            <a href="{{ Storage::url($image) }}" target="_blank" class="aspect-square overflow-hidden rounded-xl border border-white/10 bg-slate-900">
                                <img src="{{ Storage::url($image) }}" alt="SOS" class="h-full w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            @if($activeSos->status === 'pending')
                <button type="button" onclick="cancelSos({{ $activeSos->id }})" class="hidden h-12 w-full items-center justify-center gap-2 rounded-xl border border-red-400/30 bg-red-500/10 text-sm font-black text-red-100 transition hover:bg-red-500/20 md:flex">
                    <i class="fas fa-xmark"></i>
                    Hủy yêu cầu
                </button>
            @endif
        </aside>

        <div class="sos-bottom-actions fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-slate-950/95 p-3 backdrop-blur md:hidden">
            <div class="mx-auto flex max-w-7xl gap-2">
                @if($activeSos->assignedStaff?->phone)
                    <a href="tel:{{ $activeSos->assignedStaff->phone }}" class="flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-500 text-sm font-black text-white">
                        <i class="fas fa-phone"></i>
                        Gọi nhân viên
                    </a>
                @else
                    <a href="tel:19001234" class="flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-500 text-sm font-black text-white">
                        <i class="fas fa-phone"></i>
                        Gọi gara
                    </a>
                @endif
                @if($activeSos->status === 'pending')
                    <button type="button" onclick="cancelSos({{ $activeSos->id }})" class="flex h-12 w-28 items-center justify-center rounded-xl border border-red-400/30 bg-red-500/10 text-sm font-black text-red-100">
                        Hủy
                    </button>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const lat = {{ $activeSos->latitude }};
    const lng = {{ $activeSos->longitude }};
    const map = L.map('mapStatus', { zoomControl: false }).setView([lat, lng], 16);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: 'OpenStreetMap'
    }).addTo(map);

    const marker = L.divIcon({
        className: '',
        html: '<div class="flex size-11 items-center justify-center rounded-full bg-red-500 text-white shadow-2xl shadow-red-950/50 ring-8 ring-red-500/20"><i class="fas fa-location-dot"></i></div>',
        iconSize: [44, 44],
        iconAnchor: [22, 22]
    });

    L.marker([lat, lng], { icon: marker }).addTo(map).bindPopup('Vị trí sự cố').openPopup();
    setTimeout(() => map.invalidateSize(), 250);
});

function cancelSos(id) {
    Swal.fire({
        title: 'Hủy yêu cầu SOS?',
        text: 'Chỉ hủy khi nhân viên chưa nhận ca.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Hủy yêu cầu',
        cancelButtonText: 'Đóng'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`{{ url('customer/sos') }}/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Đã hủy', data.message, 'success').then(() => {
                    window.location.href = '{{ route('customer.sos.index') }}';
                });
                return;
            }
            Swal.fire('Không thể hủy', data.message || 'Yêu cầu đang được xử lý.', 'error');
        })
        .catch(() => Swal.fire('Lỗi kết nối', 'Vui lòng thử lại.', 'error'));
    });
}

const currentStatus = @json($activeSos->status);
setInterval(() => {
    fetch('{{ route('customer.sos.index') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) return;
        if (!data.data || data.data.status !== currentStatus) {
            window.location.reload();
        }
    })
    .catch(() => {});
}, 5000);
</script>
@endpush
