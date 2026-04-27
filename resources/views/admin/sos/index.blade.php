@extends('layouts.admin')

@section('title', 'Bản Đồ Cứu Hộ')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    .leaflet-routing-container { display: none !important; }
    .sos-map .leaflet-control-attribution { font-size: 10px; }
</style>

<div class="-m-4 flex h-[calc(100vh-112px)] flex-col gap-4 p-4 xl:flex-row">
    <aside class="flex w-full flex-col overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900/90 backdrop-blur-xl xl:w-[420px]">
        <div class="border-b border-slate-700 p-4">
            <h2 class="flex items-center gap-2 text-lg font-black text-white">
                <span class="h-2 w-2 animate-pulse rounded-full bg-red-500"></span>
                Điều phối cứu hộ
            </h2>
            <p class="mt-1 text-xs text-slate-400">Theo dõi yêu cầu SOS và vị trí nhân viên đang chia sẻ.</p>
        </div>

        <div class="grid grid-cols-3 gap-2 border-b border-slate-800 p-4">
            <div class="rounded-xl bg-slate-800/70 p-3 text-center">
                <div class="text-xl font-black text-amber-300">{{ $stats['pending'] }}</div>
                <div class="text-[10px] font-bold uppercase text-slate-500">Chờ nhận</div>
            </div>
            <div class="rounded-xl bg-slate-800/70 p-3 text-center">
                <div class="text-xl font-black text-blue-300">{{ $stats['active'] }}</div>
                <div class="text-[10px] font-bold uppercase text-slate-500">Đang xử lý</div>
            </div>
            <div class="rounded-xl bg-slate-800/70 p-3 text-center">
                <div class="text-xl font-black text-emerald-300">{{ $stats['completed_today'] }}</div>
                <div class="text-[10px] font-bold uppercase text-slate-500">Xong hôm nay</div>
            </div>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto p-4" id="sos-list">
            <div class="py-4 text-center text-sm text-slate-500">Đang tải dữ liệu...</div>
        </div>

        <div class="border-t border-slate-700 bg-slate-900 p-4">
            <div class="mb-2 flex justify-between text-xs font-bold uppercase text-slate-500">
                <span>Nhân viên đang chia sẻ vị trí</span>
                <span id="staff-count">0</span>
            </div>
            <div class="flex flex-wrap gap-2" id="staff-avatars"></div>
        </div>
    </aside>

    <div class="sos-map relative min-h-[520px] flex-1 overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-900 shadow-2xl">
        <div id="map" class="z-0 h-full w-full"></div>
        <div class="absolute right-4 top-4 z-[999] flex flex-col gap-2">
            <button onclick="centerGarage()" class="rounded-lg border border-slate-700 bg-slate-900 p-3 text-white shadow-lg transition hover:bg-indigo-600" title="Về gara">
                <i class="fas fa-home"></i>
            </button>
            <button onclick="fetchMapData()" class="rounded-lg border border-slate-700 bg-slate-900 p-3 text-white shadow-lg transition hover:bg-indigo-600" title="Làm mới">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
let map;
let garage = { lat: 10.7769, lng: 106.7009, name: 'Smart Garage' };
let currentRoutingControl = null;
const markers = { garage: null, staff: {}, sos: {} };

const garageIcon = L.divIcon({
    html: '<div class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white bg-indigo-600 text-lg text-white shadow-lg"><i class="fas fa-warehouse"></i></div>',
    className: 'custom-icon',
    iconSize: [40, 40],
    iconAnchor: [20, 20]
});
const staffIcon = L.divIcon({
    html: '<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-xs text-white shadow-lg"><i class="fas fa-wrench"></i></div>',
    className: 'custom-icon',
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});
const sosIcon = L.divIcon({
    html: '<div class="relative"><div class="absolute -inset-2 animate-ping rounded-full bg-red-500/50"></div><div class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-red-600 text-xs text-white shadow-lg"><i class="fas fa-exclamation"></i></div></div>',
    className: 'custom-icon',
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

function initMap() {
    map = L.map('map').setView([garage.lat, garage.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    fetchMapData();
    setInterval(fetchMapData, 10000);
}

function centerGarage() {
    map.setView([garage.lat, garage.lng], 15);
}

async function fetchMapData() {
    try {
        const response = await fetch("{{ route('admin.api.sos.map-data') }}", { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Không tải được dữ liệu bản đồ');
        const data = await response.json();
        garage = data.garage || garage;
        updateGarage(garage);
        updateStaff(data.staffs || []);
        updateSOS(data.sos || []);
        updateSidebar(data.staffs || []);
    } catch (error) {
        document.getElementById('sos-list').innerHTML = '<div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">Không tải được dữ liệu cứu hộ.</div>';
        console.error(error);
    }
}

function updateGarage(data) {
    const popup = `<b>${escapeHtml(data.name || 'Smart Garage')}</b><br>Vị trí gara`;
    if (!markers.garage) {
        markers.garage = L.marker([data.lat, data.lng], { icon: garageIcon }).addTo(map).bindPopup(popup);
    } else {
        markers.garage.setLatLng([data.lat, data.lng]);
        markers.garage.getPopup().setContent(popup);
    }
}

function updateStaff(staffs) {
    const activeIds = new Set(staffs.map(staff => String(staff.id)));
    staffs.forEach(staff => {
        const popup = `<b>${escapeHtml(staff.name)}</b><br>${escapeHtml(staff.phone || 'Không có SĐT')}<br>Cập nhật: ${escapeHtml(staff.last_location_update || 'Không rõ')}`;
        if (markers.staff[staff.id]) {
            markers.staff[staff.id].setLatLng([staff.latitude, staff.longitude]);
            markers.staff[staff.id].getPopup().setContent(popup);
        } else {
            markers.staff[staff.id] = L.marker([staff.latitude, staff.longitude], { icon: staffIcon }).addTo(map).bindPopup(popup);
        }
    });
    Object.keys(markers.staff).forEach(id => {
        if (!activeIds.has(id)) {
            map.removeLayer(markers.staff[id]);
            delete markers.staff[id];
        }
    });
}

function updateSOS(requests) {
    const listEl = document.getElementById('sos-list');
    const activeIds = new Set(requests.map(req => String(req.id)));
    listEl.innerHTML = '';

    if (requests.length === 0) {
        listEl.innerHTML = '<div class="py-6 text-center text-sm text-slate-500">Không có yêu cầu cứu hộ đang hoạt động.</div>';
    }

    requests.forEach(req => {
        const popup = `<b>SOS #${req.id}</b><br>${escapeHtml(req.description || 'Không có mô tả')}<br>Khách: ${escapeHtml(req.display_name)}<br>SĐT: ${escapeHtml(req.display_phone)}`;
        if (markers.sos[req.id]) {
            markers.sos[req.id].setLatLng([req.latitude, req.longitude]);
            markers.sos[req.id].getPopup().setContent(popup);
        } else {
            markers.sos[req.id] = L.marker([req.latitude, req.longitude], { icon: sosIcon }).addTo(map).bindPopup(popup);
        }

        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'block w-full rounded-xl border border-slate-700/50 bg-slate-800/50 p-3 text-left transition hover:bg-slate-800';
        item.onclick = () => focusSos(req);
        item.innerHTML = `
            <div class="mb-2 flex items-start justify-between gap-3">
                <div class="font-bold text-white text-sm">SOS #${req.id}</div>
                <span class="rounded bg-red-500/20 px-2 py-0.5 text-[10px] font-bold uppercase text-red-300">${escapeHtml(req.status_label)}</span>
            </div>
            <div class="mb-2 line-clamp-2 text-xs text-slate-400">${escapeHtml(req.description || 'Không có mô tả')}</div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-indigo-300">
                <i class="fas fa-user-circle"></i> ${escapeHtml(req.display_name)}
                <span class="text-slate-600">|</span>
                <i class="fas fa-phone"></i> ${escapeHtml(req.display_phone)}
            </div>
            ${req.vehicle ? `<div class="mt-2 text-[11px] text-slate-500"><i class="fas fa-car mr-1"></i>${escapeHtml(req.vehicle.license_plate || '')} ${escapeHtml(req.vehicle.name || '')}</div>` : ''}
            ${req.assigned_staff ? `<div class="mt-2 border-t border-slate-700/50 pt-2 text-[11px] text-emerald-300"><i class="fas fa-shipping-fast mr-1"></i>Nhân viên: ${escapeHtml(req.assigned_staff.name)}</div>` : ''}
        `;
        listEl.appendChild(item);
    });

    Object.keys(markers.sos).forEach(id => {
        if (!activeIds.has(id)) {
            map.removeLayer(markers.sos[id]);
            delete markers.sos[id];
        }
    });
}

function focusSos(req) {
    map.flyTo([req.latitude, req.longitude], 16);
    if (currentRoutingControl) {
        map.removeControl(currentRoutingControl);
        currentRoutingControl = null;
    }
    if (req.assigned_staff && req.assigned_staff.latitude && req.assigned_staff.longitude) {
        currentRoutingControl = L.Routing.control({
            waypoints: [L.latLng(req.assigned_staff.latitude, req.assigned_staff.longitude), L.latLng(req.latitude, req.longitude)],
            lineOptions: { styles: [{ color: '#6366f1', opacity: 0.85, weight: 6 }] },
            createMarker: () => null,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false
        }).addTo(map);
    }
}

function updateSidebar(staffs) {
    document.getElementById('staff-count').innerText = staffs.length;
    const avatars = document.getElementById('staff-avatars');
    avatars.innerHTML = '';
    staffs.forEach(staff => {
        const img = document.createElement('img');
        img.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(staff.name)}&background=2563eb&color=fff`;
        img.className = 'h-8 w-8 rounded-full bg-white ring-2 ring-slate-900';
        img.title = staff.name;
        avatars.appendChild(img);
    });
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
}

document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection
