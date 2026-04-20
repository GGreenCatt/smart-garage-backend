@extends('layouts.customer')

@section('title', 'Trạng Thái Cứu Hộ')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapStatus { height: 250px; width: 100%; border-radius: 1rem; z-index: 0; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto flex flex-col pt-24 pb-8 px-4 min-h-screen">
    
    <!-- Status Banner -->
    <div class="rounded-3xl p-8 text-white mb-6 relative overflow-hidden shadow-lg 
        @if($activeSos->status == 'pending') bg-gradient-to-r from-amber-500 to-orange-400 shadow-amber-500/30
        @elseif($activeSos->status == 'assigned') bg-gradient-to-r from-blue-600 to-indigo-500 shadow-blue-500/30
        @else bg-gradient-to-r from-teal-500 to-emerald-400 shadow-teal-500/30 @endif shrink-0">
        
        <div class="relative z-10 flex gap-6 items-center">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex justify-center items-center shrink-0">
                @if($activeSos->status == 'pending') <i class="fas fa-hourglass-half text-3xl text-white"></i>
                @elseif($activeSos->status == 'assigned') <i class="fas fa-user-check text-3xl text-white"></i>
                @else <i class="fas fa-truck-fast text-3xl text-white"></i> @endif
            </div>
            <div>
                <h1 class="text-3xl font-black mb-1">
                    @if($activeSos->status == 'pending') Đang Chờ Tiếp Nhận
                    @elseif($activeSos->status == 'assigned') Đã Phân Công
                    @else Đang Di Chuyển Đến
                    @endif
                </h1>
                <p class="text-white/80 font-medium">Yêu cầu SOS #{{ $activeSos->id }} gửi lúc {{ $activeSos->created_at->format('H:i d/m') }}</p>
            </div>
        </div>
        
        <i class="fas fa-truck-medical absolute -bottom-10 -right-4 text-9xl text-white/10 transform rotate-12"></i>
    </div>

    <!-- Main Content Grid -->
    <div class="flex-1 overflow-y-auto no-scrollbar pb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Left Info -->
            <div class="space-y-6">
                <!-- Staff Info -->
                @if($activeSos->assignedStaff)
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4"><i class="fas fa-user-tie text-indigo-500 mr-2"></i> Nhân Viên Phụ Trách</h2>
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold text-xl uppercase">
                            {{ substr($activeSos->assignedStaff->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-lg">{{ $activeSos->assignedStaff->name }}</h3>
                            <a href="tel:{{ $activeSos->assignedStaff->phone }}" class="text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-2 mt-1 hover:underline">
                                <i class="fas fa-phone"></i> {{ $activeSos->assignedStaff->phone }}
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-6 border border-dashed border-slate-200 dark:border-slate-700 text-center">
                    <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-search text-2xl animate-pulse"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 dark:text-slate-300">Đang tìm nhân viên cứu hộ</h3>
                    <p class="text-sm text-slate-500 mt-1">Hệ thống đang điều phối nhân viên gần nhất đến hỗ trợ bạn.</p>
                </div>
                @endif

                <!-- Problem Details -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4"><i class="fas fa-file-alt text-slate-400 mr-2"></i> Chi Tiết Sự Cố</h2>
                    
                    @if($activeSos->vehicle)
                        <div class="mb-4 pb-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <span class="text-slate-500">Xe gặp sự cố:</span>
                            <span class="font-bold bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-lg">{{ $activeSos->vehicle->license_plate }}</span>
                        </div>
                    @endif

                    <div class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 p-4 rounded-r-xl text-slate-700 dark:text-slate-300">
                        {{ $activeSos->description }}
                    </div>

                    @if($activeSos->images && count($activeSos->images) > 0)
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <h3 class="text-xs font-bold text-slate-400 uppercase mb-3">Hình &Aring;nh Đính Kèm</h3>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($activeSos->images as $img)
                                    <a href="{{ Storage::url($img) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden hover:opacity-80 transition">
                                        <img src="{{ Storage::url($img) }}" alt="SOS Image" class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right: Map -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Vị Trí Của Bạn</h2>
                <div class="relative bg-slate-100 dark:bg-slate-900 rounded-2xl overflow-hidden flex-1 min-h-[250px] z-0">
                    <div id="mapStatus" class="absolute inset-0"></div>
                </div>
                
                @if($activeSos->status == 'pending')
                <div class="mt-6">
                    <button type="button" onclick="cancelSos({{ $activeSos->id }})" class="w-full py-3 px-4 bg-slate-100 hover:bg-red-50 dark:bg-slate-700 dark:hover:bg-red-900/20 text-slate-600 hover:text-red-600 dark:text-slate-300 dark:hover:text-red-400 font-bold rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i> Hủy Yêu Cầu Này
                    </button>
                    <p class="text-xs text-center text-slate-400 mt-2">Bạn chỉ có thể hủy khi nhân viên chưa bấm nhận ca.</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $activeSos->latitude }};
        const lng = {{ $activeSos->longitude }};
        
        const map = L.map('mapStatus').setView([lat, lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        L.marker([lat, lng], {icon: redIcon}).addTo(map)
            .bindPopup('<b>Vị trí sự cố</b><br>Nhân viên sẽ đến đây.')
            .openPopup();
            
        // Disable grabbing
        map.dragging.disable();

        // Fix tile loading issue for absolute/flex containers
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    });

    function cancelSos(id) {
        Swal.fire({
            title: 'Hủy yêu cầu?',
            text: "Bạn có chắc chắn muốn hủy yêu cầu cứu hộ này không?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý Hủy',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('customer/sos') }}/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', data.message, 'success').then(() => {
                            window.location.href = "{{ route('customer.sos.index') }}";
                        });
                    } else {
                        Swal.fire('Lỗi!', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi!', 'Lỗi mạng khi hủy yêu cầu.', 'error');
                });
            }
        });
    }

    // Auto polling for status updates
    const currentStatus = "{{ $activeSos->status }}";
    const sosId = {{ $activeSos->id }};
    
    setInterval(() => {
        fetch("{{ route('customer.sos.index') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && data.data && data.data.status !== currentStatus) {
                window.location.reload();
            }
        })
        .catch(err => console.log('Polling error:', err));
    }, 5000);
</script>
@endpush
