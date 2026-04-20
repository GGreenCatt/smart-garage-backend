@extends('layouts.staff')

@section('title', 'Quản lý Cứu Hộ (SOS)')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #masterMap { height: 350px; width: 100%; border-radius: 1rem; z-index: 1; }
</style>
@endpush

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-truck-medical text-red-500"></i> Cứu Hộ Khẩn Cấp (SOS)
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Quản lý và tiếp nhận các yêu cầu cứu hộ từ khách hàng.</p>
        </div>
        <div class="flex gap-4 text-center">
            <div class="bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-xl border border-red-100 dark:border-red-800/30">
                <p class="text-xs font-bold text-red-400 dark:text-red-500 uppercase">Chờ Xử Lý</p>
                <p class="text-xl font-black text-red-600 dark:text-red-400">{{ $pendingRequests->count() }}</p>
            </div>
            <div class="bg-teal-50 dark:bg-teal-900/20 px-4 py-2 rounded-xl border border-teal-100 dark:border-teal-800/30">
                <p class="text-xs font-bold text-teal-400 dark:text-teal-500 uppercase">Đang Làm</p>
                <p class="text-xl font-black text-teal-600 dark:text-teal-400">{{ $myRequests->count() }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700">
                <p class="text-xs font-bold text-slate-400 uppercase">Đã Hoàn Thành</p>
                <p class="text-xl font-black text-slate-700 dark:text-slate-300">{{ $completedCount }}</p>
            </div>
        </div>
    </div>

    <!-- Master Map -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-2 relative z-0">
        <div id="masterMap"></div>
        
        <!-- Map Controls Overlay -->
        <div class="absolute top-4 right-4 z-[400] flex flex-col gap-2">
            <div id="locationBanner" class="hidden bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 p-3 rounded-xl shadow-lg backdrop-blur-md max-w-[200px]">
                <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">Cần cấp quyền</p>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mb-2">Trình duyệt chưa có quyền truy cập vị trí.</p>
                <button onclick="requestLocationPermission()" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-1.5 rounded-lg text-xs font-bold transition">Cấp quyền ngay</button>
            </div>

            <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur rounded-2xl p-4 shadow-xl border border-slate-100 dark:border-slate-700 w-48 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Chia sẻ vị trí</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="locationToggle" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                <div id="sharingStatus" class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase">
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                    <span>Đang tắt</span>
                </div>
            </div>
        </div>

        <div class="absolute top-4 left-4 z-[400] bg-white/90 dark:bg-slate-800/90 backdrop-blur rounded-xl p-3 shadow text-xs font-bold border border-slate-100 dark:border-slate-700">
            <h4 class="text-slate-500 mb-2 uppercase italic opacity-70">Chú Giải Bản Đồ</h4>
            <div class="space-y-2">
                <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-red-500 shadow-sm shadow-red-500/50"></div><span class="text-slate-700 dark:text-slate-300">Khách Chờ Tiếp Nhận</span></div>
                <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-teal-500 shadow-sm shadow-teal-500/50"></div><span class="text-slate-700 dark:text-slate-300">Khách Đang Xử Lý</span></div>
                <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-indigo-600 shadow-sm shadow-indigo-600/50"></div><span class="text-slate-700 dark:text-slate-300 font-bold">Đồng Nghiệp</span></div>
            </div>
        </div>
    </div>

    <!-- Lists -->
    <div class="flex flex-col md:flex-row gap-6 h-full overflow-hidden pb-4">
        
        <!-- Yêu cầu Chờ Xử Lý -->
        <div class="flex-1 flex flex-col bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 h-full overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>Chờ Tiếp Nhận (Mới)</h2>
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $pendingRequests->count() }}</span>
            </div>
            <div class="p-4 flex-1 overflow-y-auto space-y-4">
                @forelse($pendingRequests as $sos)
                    <div onclick="focusOnSos({{ $sos->id }}, {{ $sos->latitude ?? 0 }}, {{ $sos->longitude ?? 0 }})" class="bg-white dark:bg-slate-800 rounded-xl border border-red-200 dark:border-red-900 shadow-sm hover:shadow-md transition p-4 relative group cursor-pointer hover:border-red-400">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500 rounded-l-xl"></div>
                        <div class="pl-2">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-lg">
                                        {{ $sos->display_name }}
                                        @if(!$sos->customer_id)
                                            <span class="ml-2 text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-500 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">Khách Vãng Lai</span>
                                        @endif
                                    </h3>
                                    <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mb-1"><i class="fas fa-car mr-1"></i> {{ $sos->vehicle->license_plate ?? 'Xe ngoài hệ thống' }} ({{ $sos->vehicle->model ?? 'N/A' }})</p>
                                </div>
                                <span class="bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold px-2 py-1 rounded uppercase animate-pulse">
                                    {{ $sos->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 italic mb-4">"{{ $sos->description ?? 'Không có mô tả.' }}"</p>
                            
                            <div class="flex gap-2">
                                <a href="{{ route('staff.sos.show', $sos->id) }}" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white py-2 rounded-lg text-sm font-bold shadow-sm transition">
                                    <i class="fas fa-eye mr-1"></i> Xem Chi Tiết
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl text-slate-400"></i>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">Không có yêu cầu cứu hộ nào mới.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Nhiệm vụ Của Tôi -->
        <div class="w-full md:w-[400px] flex flex-col bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 h-full overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-hammer text-teal-500 mr-2"></i>Nhiệm Vụ Của Tôi</h2>
                <span class="bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-xs font-bold px-2 py-1 rounded-full">{{ $myRequests->count() }}</span>
            </div>
            <div class="p-4 flex-1 overflow-y-auto space-y-4">
                @forelse($myRequests as $sos)
                    <div onclick="focusOnSos({{ $sos->id }}, {{ $sos->latitude ?? 0 }}, {{ $sos->longitude ?? 0 }})" class="bg-white dark:bg-slate-800 rounded-xl border {{ $sos->status == 'in_progress' ? 'border-teal-400 shadow-teal-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-sm p-4 relative cursor-pointer hover:border-teal-500 transition-colors">
                        @if($sos->status == 'in_progress')
                            <div class="absolute top-0 right-0 bg-teal-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg">ĐANG XỬ LÝ</div>
                        @endif
                        
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">
                                    {{ $sos->display_name }}
                                    @if(!$sos->customer_id)
                                        <i class="fas fa-user-tag text-slate-400 ml-1 text-[10px]" title="Khách vãng lai"></i>
                                    @endif
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><i class="fas fa-clock mr-1"></i>Nhận {{ $sos->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('staff.sos.show', $sos->id) }}" class="block w-full text-center bg-teal-50 hover:bg-teal-100 text-teal-700 dark:bg-teal-900/20 dark:hover:bg-teal-900/40 dark:text-teal-400 py-2 rounded-lg text-sm font-bold transition border border-teal-100 dark:border-teal-800">
                            Chi Tiết
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-slate-400 dark:text-slate-500 text-sm italic">Bạn chưa nhận yêu cầu cứu hộ nào.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('masterMap').setView([21.028511, 105.804817], 12); // Default to Hanoi
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Custom markers
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

        const markers = {}; // Changed from array to object for easier direct access
        const allMarkers = []; // For fitBounds

        // Data from Controller
        const pending = @json($pendingRequests);
        const myTasks = @json($myRequests);

        // Plot Pending
        pending.forEach(sos => {
            if(sos.latitude && sos.longitude) {
                const marker = L.marker([sos.latitude, sos.longitude], {icon: redIcon}).addTo(map);
                const name = sos.display_name;
                marker.bindPopup(`<b>Chờ xử lý #${sos.id}</b><br>${name}<br><a href="{{ url('staff/sos') }}/${sos.id}" class="text-blue-500 underline mt-1 block">Chi Tiết</a>`);
                markers[sos.id] = marker;
                allMarkers.push(marker);
            }
        });

        // Plot My Tasks
        myTasks.forEach(sos => {
            if(sos.latitude && sos.longitude && sos.status !== 'completed') {
                const marker = L.marker([sos.latitude, sos.longitude], {icon: tealIcon}).addTo(map);
                const name = sos.display_name;
                marker.bindPopup(`<b>Đang xử lý #${sos.id}</b><br>${name}<br><a href="{{ url('staff/sos') }}/${sos.id}" class="text-blue-500 underline mt-1 block">Chi Tiết</a>`);
                markers[sos.id] = marker;
                allMarkers.push(marker);
            }
        });

        window.focusOnSos = function(id, lat, lng) {
            if (markers[id]) {
                map.flyTo([lat, lng], 17);
                markers[id].openPopup();
            } else {
                // If marker doesn't exist (e.g. invalid lat/lng), just fly to map center
                map.flyTo([lat, lng], 17);
                console.warn("Marker not found for SOS ID:", id);
            }
        }

        // Staff management logic
        const staffIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        let staffMarkers = {};
        const locationToggle = document.getElementById('locationToggle');
        const sharingStatus = document.getElementById('sharingStatus');
        const locationBanner = document.getElementById('locationBanner');
        let updateTimer = null;
        let fetchTimer = null;

        // Check current sharing state from server or localStorage
        locationToggle.checked = @json(auth()->user()->is_sharing_location);
        updateStatusUI(locationToggle.checked);

        locationToggle.addEventListener('change', function() {
            if (this.checked) {
                checkAndStartSharing();
            } else {
                stopSharing();
            }
        });

        function updateStatusUI(sharing) {
            if (sharing) {
                sharingStatus.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div><span>Đang chia sẻ</span>';
                sharingStatus.classList.remove('text-slate-400');
                sharingStatus.classList.add('text-green-500');
            } else {
                sharingStatus.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div><span>Đang tắt</span>';
                sharingStatus.classList.remove('text-green-500');
                sharingStatus.classList.add('text-slate-400');
            }
        }

        async function checkAndStartSharing() {
            if (!navigator.geolocation) {
                Swal.fire('Lỗi', 'Trình duyệt không hỗ trợ định vị.', 'error');
                locationToggle.checked = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                () => {
                    locationBanner.classList.add('hidden');
                    startSharing();
                },
                (error) => {
                    console.warn('Geolocation error:', error);
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
        }

        function startSharing() {
            updateStatusUI(true);
            // Initial update
            pushLocation();
            // Timers
            updateTimer = setInterval(pushLocation, 30000);
            fetchTimer = setInterval(fetchStaff, 30000);
            fetchStaff(); // Initial fetch
        }

        function stopSharing() {
            updateStatusUI(false);
            clearInterval(updateTimer);
            clearInterval(fetchTimer);
            // Inform server
            fetch("{{ route('staff.sos.location.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ is_sharing_location: false })
            });
            // Clear staff markers
            Object.values(staffMarkers).forEach(m => map.removeLayer(m));
            staffMarkers = {};
        }

        function pushLocation() {
            navigator.geolocation.getCurrentPosition(position => {
                fetch("{{ route('staff.sos.location.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
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
                if (result.success) {
                    processStaffData(result.data);
                }
            } catch (e) {
                console.error("Fetch staff error:", e);
            }
        }

        function processStaffData(data) {
            const currentIds = data.map(s => s.id.toString());
            
            // Remove inactive
            Object.keys(staffMarkers).forEach(id => {
                if (!currentIds.includes(id)) {
                    map.removeLayer(staffMarkers[id]);
                    delete staffMarkers[id];
                }
            });

            // Update/Add
            data.forEach(staff => {
                const id = staff.id.toString();
                if (staffMarkers[id]) {
                    staffMarkers[id].setLatLng([staff.latitude, staff.longitude]);
                } else {
                    staffMarkers[id] = L.marker([staff.latitude, staff.longitude], {icon: staffIcon})
                        .addTo(map)
                        .bindPopup(`<b>Đồng nghiệp: ${staff.name}</b><br><span class="text-[10px] text-slate-500 italic">Vừa cập nhật xong</span>`);
                }
            });
        }

        // Start if already sharing
        if (locationToggle.checked) {
            startSharing();
        }

        // Fit bounds if markers exist
        if(allMarkers.length > 0) {
            const group = new L.featureGroup(allMarkers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    });
</script>
@endpush
