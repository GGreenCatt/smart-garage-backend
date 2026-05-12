@extends('layouts.staff')

@section('title', 'Điều phối cứu hộ SOS')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    #masterMap { height: clamp(22rem, 46vh, 34rem); width: 100%; z-index: 1; }
    .sos-map-panel { min-height: clamp(22rem, 46vh, 34rem); }
    .sos-card { border: 1px solid rgba(148, 163, 184, .20); }
    .sos-card:hover { border-color: rgba(239, 68, 68, .45); }
    .sos-line-clamp { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    @media (max-width: 767px) {
        #masterMap { height: 20rem; }
        .sos-map-panel { min-height: 20rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-300">
                    <span class="size-2 rounded-full bg-red-500 {{ $pendingRequests->isNotEmpty() ? 'animate-pulse' : '' }}"></span>
                    Trung tâm điều phối
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white md:text-3xl">Cứu hộ khẩn cấp SOS</h1>
                <p class="mt-1 max-w-3xl text-sm font-medium text-slate-500 dark:text-slate-400">Theo dõi vị trí khách, nhận ca cứu hộ và chia sẻ vị trí nhân viên để phối hợp nhanh hơn.</p>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[28rem]">
                <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-3 dark:border-red-500/20 dark:bg-red-500/10">
                    <div class="text-[10px] font-black uppercase tracking-wide text-red-500">Chờ nhận</div>
                    <div class="mt-1 text-2xl font-black text-red-600 dark:text-red-300">{{ $pendingRequests->count() }}</div>
                </div>
                <div class="rounded-xl border border-teal-200 bg-teal-50 px-3 py-3 dark:border-teal-500/20 dark:bg-teal-500/10">
                    <div class="text-[10px] font-black uppercase tracking-wide text-teal-600 dark:text-teal-300">Ca của tôi</div>
                    <div class="mt-1 text-2xl font-black text-teal-700 dark:text-teal-300">{{ $myRequests->count() }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800">
                    <div class="text-[10px] font-black uppercase tracking-wide text-slate-500">Hoàn tất</div>
                    <div class="mt-1 text-2xl font-black text-slate-700 dark:text-slate-200">{{ $completedCount }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-slate-800 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-black text-slate-900 dark:text-white">Bản đồ điều phối</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Đỏ: yêu cầu mới, xanh lá: ca bạn đang xử lý, xanh dương: đồng nghiệp đang chia sẻ vị trí.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div id="sharingStatus" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-black uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <span class="size-2 rounded-full bg-slate-400"></span>
                        Đang tắt
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <span>Chia sẻ vị trí</span>
                        <input type="checkbox" id="locationToggle" class="peer sr-only">
                        <span class="relative h-5 w-10 rounded-full bg-slate-300 transition peer-checked:bg-indigo-600 dark:bg-slate-700">
                            <span class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="sos-map-panel relative">
                <div id="masterMap"></div>
                <div id="locationBanner" class="absolute right-4 top-4 z-[400] hidden max-w-xs rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm shadow-lg dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="font-black text-amber-700 dark:text-amber-200">Cần cấp quyền vị trí</div>
                    <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-100/80">Trình duyệt chưa cho phép truy cập vị trí.</p>
                    <button onclick="requestLocationPermission()" class="mt-3 w-full rounded-lg bg-amber-500 px-3 py-2 text-xs font-black text-white transition hover:bg-amber-600">Cấp quyền ngay</button>
                </div>
                <div class="absolute bottom-4 left-4 z-[400] hidden rounded-xl border border-slate-200 bg-white/95 p-3 text-xs font-bold shadow-lg backdrop-blur dark:border-slate-700 dark:bg-slate-900/95 md:block">
                    <div class="mb-2 text-[10px] font-black uppercase tracking-wide text-slate-400">Chú giải</div>
                    <div class="space-y-1.5 text-slate-600 dark:text-slate-300">
                        <div class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-red-500"></span>Khách chờ tiếp nhận</div>
                        <div class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-teal-500"></span>Ca của tôi</div>
                        <div class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-indigo-600"></span>Đồng nghiệp</div>
                    </div>
                </div>
            </div>
        </div>

        <aside class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-1">
            <div class="rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-500/20 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-red-100 p-4 dark:border-red-500/20">
                    <h2 class="font-black text-slate-900 dark:text-white"><i class="fas fa-triangle-exclamation mr-2 text-red-500"></i>Yêu cầu mới</h2>
                    <span class="rounded-full bg-red-600 px-2.5 py-1 text-xs font-black text-white">{{ $pendingRequests->count() }}</span>
                </div>
                <div class="max-h-[34rem] space-y-3 overflow-y-auto p-3">
                    @forelse($pendingRequests as $sos)
                        <article class="sos-card rounded-xl bg-white p-4 shadow-sm transition dark:bg-slate-800" data-sos-card="{{ $sos->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-base font-black text-slate-900 dark:text-white">{{ $sos->display_name }}</h3>
                                        @if(!$sos->customer_id)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase text-slate-500 dark:bg-slate-700 dark:text-slate-300">Khách vãng lai</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                                        <i class="fas fa-car mr-1"></i>{{ $sos->vehicle->license_plate ?? 'Xe ngoài hệ thống' }}
                                        <span class="mx-1">·</span>
                                        <i class="fas fa-clock mr-1"></i>{{ $sos->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); focusOnSos({{ $sos->id }}, {{ $sos->latitude ?? 0 }}, {{ $sos->longitude ?? 0 }})" class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300">
                                    <i class="fas fa-location-crosshairs"></i>
                                </button>
                            </div>
                            <p class="sos-line-clamp mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $sos->description ?: 'Không có mô tả.' }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('staff.sos.show', $sos->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-black text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
                                    <i class="fas fa-eye"></i>
                                    Chi tiết
                                </a>
                                <button type="button" onclick="event.stopPropagation(); acceptSosFromIndex({{ $sos->id }})" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-black text-white transition hover:bg-red-500">
                                    <i class="fas fa-hand"></i>
                                    Nhận ca
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-700">
                            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <i class="fas fa-check text-xl"></i>
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-500 dark:text-slate-400">Không có yêu cầu mới.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-teal-200 bg-white shadow-sm dark:border-teal-500/20 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-teal-100 p-4 dark:border-teal-500/20">
                    <h2 class="font-black text-slate-900 dark:text-white"><i class="fas fa-user-shield mr-2 text-teal-500"></i>Ca của tôi</h2>
                    <span class="rounded-full bg-teal-100 px-2.5 py-1 text-xs font-black text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">{{ $myRequests->count() }}</span>
                </div>
                <div class="max-h-[24rem] space-y-3 overflow-y-auto p-3">
                    @forelse($myRequests as $sos)
                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-black text-slate-900 dark:text-white">{{ $sos->display_name }}</h3>
                                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">Nhận {{ $sos->updated_at->diffForHumans() }}</p>
                                </div>
                                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $sos->status === 'in_progress' ? 'bg-teal-500 text-white' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' }}">
                                    {{ $sos->status === 'in_progress' ? 'Đang đi' : 'Đã nhận' }}
                                </span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <button type="button" onclick="focusOnSos({{ $sos->id }}, {{ $sos->latitude ?? 0 }}, {{ $sos->longitude ?? 0 }})" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                                    <i class="fas fa-map-location-dot"></i>
                                    Bản đồ
                                </button>
                                <a href="{{ route('staff.sos.show', $sos->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-sm font-black text-white transition hover:bg-teal-500">
                                    Chi tiết
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm font-bold text-slate-400 dark:border-slate-700">Bạn chưa nhận ca cứu hộ nào.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-slate-800 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-slate-900 dark:text-white"><i class="fas fa-clock-rotate-left mr-2 text-slate-400"></i>Lịch sử SOS</h2>
                <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Các yêu cầu đã hoàn tất hoặc đã hủy gần đây của bạn.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $historyRequests->count() }} mục</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($historyRequests as $sos)
                <article class="grid gap-3 p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/60 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('staff.sos.show', $sos->id) }}" class="truncate text-sm font-black text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300">
                                #{{ $sos->id }} - {{ $sos->display_name }}
                            </a>
                            @if($sos->status === 'completed')
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black uppercase text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Hoàn tất</span>
                            @else
                                <span class="rounded-full bg-rose-50 px-2 py-1 text-[10px] font-black uppercase text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">Đã hủy</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                            <span><i class="fas fa-car mr-1"></i>{{ $sos->vehicle->license_plate ?? 'Xe ngoài hệ thống' }}</span>
                            <span><i class="fas fa-phone mr-1"></i>{{ $sos->display_phone }}</span>
                            <span><i class="fas fa-clock mr-1"></i>{{ optional($sos->completed_at ?? $sos->cancelled_at ?? $sos->updated_at)->format('H:i d/m/Y') }}</span>
                        </div>
                        @if($sos->status === 'cancelled')
                            <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-300">
                                Lý do hủy: {{ $sos->cancel_note ?: 'Chưa có nội dung hủy.' }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('staff.sos.show', $sos->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-black text-slate-700 transition hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <i class="fas fa-eye"></i>
                        Xem chi tiết
                    </a>
                </article>
            @empty
                <div class="p-8 text-center text-sm font-bold text-slate-400">Chưa có lịch sử SOS hoàn tất hoặc đã hủy.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('masterMap').setView([21.028511, 105.804817], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: 'OpenStreetMap'
    }).addTo(map);

    const redIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    });
    const tealIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    });
    const staffIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    });

    const markers = {};
    const allMarkers = [];
    const pending = @json($pendingRequests);
    const myTasks = @json($myRequests);

    pending.forEach(sos => {
        if (!sos.latitude || !sos.longitude) return;
        const marker = L.marker([sos.latitude, sos.longitude], { icon: redIcon }).addTo(map);
        marker.bindPopup(`<b>Chờ tiếp nhận #${sos.id}</b><br>${sos.display_name}<br><a href="{{ url('staff/sos') }}/${sos.id}" class="text-blue-500 underline mt-1 block">Chi tiết</a>`);
        markers[sos.id] = marker;
        allMarkers.push(marker);
    });

    myTasks.forEach(sos => {
        if (!sos.latitude || !sos.longitude || sos.status === 'completed') return;
        const marker = L.marker([sos.latitude, sos.longitude], { icon: tealIcon }).addTo(map);
        marker.bindPopup(`<b>Ca của tôi #${sos.id}</b><br>${sos.display_name}<br><a href="{{ url('staff/sos') }}/${sos.id}" class="text-blue-500 underline mt-1 block">Chi tiết</a>`);
        markers[sos.id] = marker;
        allMarkers.push(marker);
    });

    window.focusOnSos = function(id, lat, lng) {
        if (!lat || !lng) return;
        map.flyTo([lat, lng], 17);
        if (markers[id]) markers[id].openPopup();
        document.querySelectorAll('[data-sos-card]').forEach(card => card.classList.remove('ring-2', 'ring-red-400'));
        document.querySelector(`[data-sos-card="${id}"]`)?.classList.add('ring-2', 'ring-red-400');
    };

    let staffMarkers = {};
    const locationToggle = document.getElementById('locationToggle');
    const sharingStatus = document.getElementById('sharingStatus');
    const locationBanner = document.getElementById('locationBanner');
    let updateTimer = null;
    let fetchTimer = null;

    locationToggle.checked = @json(auth()->user()->is_sharing_location);
    updateStatusUI(locationToggle.checked);

    locationToggle.addEventListener('change', function() {
        this.checked ? checkAndStartSharing() : stopSharing();
    });

    function updateStatusUI(sharing) {
        sharingStatus.innerHTML = sharing
            ? '<span class="size-2 rounded-full bg-green-500 animate-pulse"></span>Đang chia sẻ'
            : '<span class="size-2 rounded-full bg-slate-400"></span>Đang tắt';
        sharingStatus.classList.toggle('text-green-600', sharing);
        sharingStatus.classList.toggle('dark:text-green-300', sharing);
        sharingStatus.classList.toggle('text-slate-500', !sharing);
    }

    function checkAndStartSharing() {
        if (!navigator.geolocation) {
            Swal.fire('Lỗi', 'Trình duyệt không hỗ trợ định vị.', 'error');
            locationToggle.checked = false;
            updateStatusUI(false);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            () => {
                locationBanner.classList.add('hidden');
                startSharing();
            },
            () => {
                locationBanner.classList.remove('hidden');
                locationToggle.checked = false;
                updateStatusUI(false);
            }
        );
    }

    window.requestLocationPermission = function() {
        navigator.geolocation.getCurrentPosition(() => {
            locationBanner.classList.add('hidden');
            locationToggle.checked = true;
            startSharing();
        });
    };

    function startSharing() {
        updateStatusUI(true);
        pushLocation();
        clearInterval(updateTimer);
        clearInterval(fetchTimer);
        updateTimer = setInterval(pushLocation, 30000);
        fetchTimer = setInterval(fetchStaff, 30000);
        fetchStaff();
    }

    function stopSharing() {
        updateStatusUI(false);
        clearInterval(updateTimer);
        clearInterval(fetchTimer);
        fetch("{{ route('staff.sos.location.update') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ is_sharing_location: false })
        });
        Object.values(staffMarkers).forEach(marker => map.removeLayer(marker));
        staffMarkers = {};
    }

    function pushLocation() {
        navigator.geolocation.getCurrentPosition(position => {
            fetch("{{ route('staff.sos.location.update') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    is_sharing_location: true
                })
            });
        });
    }

    async function fetchStaff() {
        try {
            const response = await fetch("{{ route('staff.sos.location.staff-members') }}");
            const result = await response.json();
            if (result.success) processStaffData(result.data);
        } catch (error) {
            console.error('Fetch staff error:', error);
        }
    }

    function processStaffData(data) {
        const currentIds = data.map(staff => staff.id.toString());
        Object.keys(staffMarkers).forEach(id => {
            if (!currentIds.includes(id)) {
                map.removeLayer(staffMarkers[id]);
                delete staffMarkers[id];
            }
        });

        data.forEach(staff => {
            const id = staff.id.toString();
            if (staffMarkers[id]) {
                staffMarkers[id].setLatLng([staff.latitude, staff.longitude]);
                return;
            }
            staffMarkers[id] = L.marker([staff.latitude, staff.longitude], { icon: staffIcon })
                .addTo(map)
                .bindPopup(`<b>Đồng nghiệp: ${staff.name}</b><br><span class="text-[10px] text-slate-500 italic">Vừa cập nhật vị trí</span>`);
        });
    }

    if (locationToggle.checked) startSharing();
    if (allMarkers.length > 0) {
        const group = new L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.14));
    }
    setTimeout(() => map.invalidateSize(), 250);
});

function acceptSosFromIndex(id) {
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
                window.location.href = data.redirect || `{{ url('staff/sos') }}/${id}`;
                return;
            }
            Swal.fire('Không thể nhận ca', data.message || 'Vui lòng thử lại.', 'error');
        })
        .catch(() => Swal.fire('Lỗi kết nối', 'Không thể nhận ca lúc này.', 'error'));
    });
}
</script>
@endpush
