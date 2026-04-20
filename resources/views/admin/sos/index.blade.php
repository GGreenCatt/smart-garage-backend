@extends('layouts.admin')

@section('title', 'Bản Đồ Cứu Hộ SOS')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    /* Hide the text instructions, we only want the line */
    .leaflet-routing-container { display: none !important; }
</style>

<div class="h-[calc(100vh-140px)] flex gap-6 -mx-4 -my-4 p-4">
    
    <!-- Sidebar / Logs -->
    <div class="w-96 bg-slate-900/80 border border-slate-700/50 rounded-2xl flex flex-col overflow-hidden backdrop-blur-xl">
        <div class="p-4 border-b border-slate-700 bg-slate-900">
            <h2 class="font-black text-white text-lg flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> SOS LOGS
            </h2>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="sos-list">
            <!-- Loading State -->
            <div class="text-center text-slate-500 text-sm py-4">Đang tải dữ liệu...</div>
        </div>

        <div class="p-4 border-t border-slate-700 bg-slate-900">
            <div class="flex justify-between text-xs font-bold uppercase text-slate-500 mb-2">
                <span>Nhân Viên Online</span>
                <span id="staff-count">0</span>
            </div>
            <div class="flex -space-x-2 overflow-hidden" id="staff-avatars">
                <!-- Avatars injected here -->
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="flex-1 bg-slate-900 rounded-2xl overflow-hidden relative border border-slate-700/50 shadow-2xl">
        <div id="map" class="w-full h-full z-0"></div>
        
        <!-- Map Controls Overlay -->
        <div class="absolute top-4 right-4 z-[999] flex flex-col gap-2">
            <button onclick="map.setView([10.7769, 106.7009], 15)" class="bg-slate-900 text-white p-3 rounded-lg border border-slate-700 shadow-lg hover:bg-indigo-600 transition" title="Về Gara">
                <i class="fas fa-home"></i>
            </button>
            <button onclick="fetchMapData()" class="bg-slate-900 text-white p-3 rounded-lg border border-slate-700 shadow-lg hover:bg-indigo-600 transition" title="Làm mới">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    let map;
    let markers = {
        garage: null,
        staff: {},
        sos: {}
    };

    const garageIcon = L.divIcon({
        html: '<div class="w-10 h-10 bg-indigo-600 rounded-xl border-2 border-white shadow-lg flex items-center justify-center text-white text-lg"><i class="fas fa-warehouse"></i></div>',
        className: 'custom-icon',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    const staffIcon = L.divIcon({
        html: '<div class="w-8 h-8 bg-blue-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs"><i class="fas fa-wrench"></i></div>',
        className: 'custom-icon',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    const sosIcon = L.divIcon({
        html: '<div class="relative"><div class="absolute -inset-2 bg-red-500/50 rounded-full animate-ping"></div><div class="w-8 h-8 bg-red-600 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs relative z-10"><i class="fas fa-exclamation"></i></div></div>',
        className: 'custom-icon',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    function initMap() {
        // Default Center
        map = L.map('map').setView([10.7769, 106.7009], 14);

        // Google Maps Streets
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; <a href="https://www.google.com/maps">Google Maps</a>'
        }).addTo(map);

        fetchMapData();
        setInterval(fetchMapData, 5000); // Poll every 5s
    }

    async function fetchMapData() {
        try {
            const response = await fetch("{{ route('admin.api.sos.map-data') }}");
            const data = await response.json();
            
            updateGarage(data.garage);
            updateStaff(data.staffs);
            updateSOS(data.sos);
            updateSidebar(data);

        } catch (error) {
            console.error("Map Update Error:", error);
        }
    }

    function updateGarage(garage) {
        if (!markers.garage) {
            markers.garage = L.marker([garage.lat, garage.lng], {icon: garageIcon})
                .addTo(map)
                .bindPopup("<b>Gara Trung Tâm</b><br>Trụ sở chính");
        }
    }

    function updateStaff(staffs) {
        // Create map of current IDs for cleanup
        const activeIds = new Set();

        staffs.forEach(staff => {
            activeIds.add(staff.id);
            const popupContent = `<b>${staff.name}</b><br>Lat: ${staff.latitude}, Lng: ${staff.longitude}<br>HĐ: ${staff.last_location_update}`;

            if (markers.staff[staff.id]) {
                // Update position
                markers.staff[staff.id].setLatLng([staff.latitude, staff.longitude]);
                markers.staff[staff.id].getPopup().setContent(popupContent);
            } else {
                // Create new
                markers.staff[staff.id] = L.marker([staff.latitude, staff.longitude], {icon: staffIcon})
                    .addTo(map)
                    .bindPopup(popupContent);
            }
        });
        
        // Remove offline markers
        // (Simplified: keep them for history or fade them? For now, we only get active ones from API)
    }

    let currentRoutingControl = null; // Store Routing Control

    function updateSOS(requests) {
        // Clear list
        const listEl = document.getElementById('sos-list');
        listEl.innerHTML = '';

        if(requests.length === 0) {
            listEl.innerHTML = '<div class="text-center text-slate-500 text-sm py-4">Không có yêu cầu cứu hộ nào.</div>';
        }

        requests.forEach(req => {
            // Update Map Marker
            const popupContent = `<b>SOS #${req.id}</b><br>${req.description}<br>Khách: ${req.customer.name}<br>SĐT: ${req.customer.phone}`;
            
            if (markers.sos[req.id]) {
                 // Update Logic if needed
            } else {
                markers.sos[req.id] = L.marker([req.latitude, req.longitude], {icon: sosIcon})
                    .addTo(map)
                    .bindPopup(popupContent);
            }

            // Update Sidebar List
            const item = document.createElement('div');
            item.className = 'bg-slate-800/50 p-3 rounded-xl border border-slate-700/50 cursor-pointer hover:bg-slate-800 transition group';
            // Click Handler
            item.onclick = () => {
                map.flyTo([req.latitude, req.longitude], 16);
                
                // ROUTING LOGIC (Real Roads)
                if (currentRoutingControl) {
                    map.removeControl(currentRoutingControl);
                    currentRoutingControl = null;
                }

                if (req.assigned_staff) {
                     const staff = req.assigned_staff;
                     let staffPos = L.latLng(staff.latitude, staff.longitude);
                     
                     if (markers.staff[staff.id]) {
                         staffPos = markers.staff[staff.id].getLatLng();
                     }

                     currentRoutingControl = L.Routing.control({
                        waypoints: [
                            staffPos, // Start
                            L.latLng(req.latitude, req.longitude) // End
                        ],
                        lineOptions: {
                            styles: [{color: '#6366f1', opacity: 0.8, weight: 6}]
                        },
                        createMarker: function() { return null; }, // No extra markers
                        addWaypoints: false,
                        draggableWaypoints: false,
                        fitSelectedRoutes: true,
                        show: false // Hide text instructions
                    }).addTo(map);
                }
            };

            item.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="font-bold text-white text-sm">SOS #${req.id}</div>
                    <span class="text-[10px] font-bold bg-red-500/20 text-red-400 px-2 py-0.5 rounded uppercase">${req.status}</span>
                </div>
                <div class="text-xs text-slate-400 mb-2 line-clamp-2">${req.description || 'Không có mô tả'}</div>
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-400">
                    <i class="fas fa-user-circle"></i> ${req.customer.name}
                    <span class="text-slate-600">|</span>
                    <i class="fas fa-phone"></i> ${req.customer.phone}
                </div>
                ${req.assigned_staff ? `<div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center gap-2 text-[10px] text-green-400"><i class="fas fa-shipping-fast"></i> Đang đến: ${req.assigned_staff.name}</div>` : ''}
            `;
            listEl.appendChild(item);
        });
    }
    
    function updateSidebar(data) {
        document.getElementById('staff-count').innerText = data.staffs.length;
        const avatars = document.getElementById('staff-avatars');
        avatars.innerHTML = '';
        data.staffs.forEach(staff => {
             avatars.innerHTML += `<img src="https://ui-avatars.com/api/?name=${staff.name}" class="inline-block h-8 w-8 rounded-full ring-2 ring-slate-900 bg-white" title="${staff.name}">`;
        });
    }

    document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection
