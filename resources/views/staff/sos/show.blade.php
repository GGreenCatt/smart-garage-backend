@extends('layouts.staff')

@section('title', 'Chi Tiết Cứu Hộ (SOS)')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 400px; width: 100%; border-radius: 0.75rem; z-index: 1; }
</style>
@endpush

@section('content')
<div class="h-full flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('staff.sos.index') }}" class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                    Chi Tiết Yêu Cầu Cứu Hộ #{{ $sosInfo->id }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gửi lúc {{ $sosInfo->created_at->format('H:i d/m/Y') }} ({{ $sosInfo->created_at->diffForHumans() }})</p>
            </div>
        </div>

        <div>
            @if($sosInfo->status == 'pending')
                <span class="bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-4 py-2 rounded-xl font-bold uppercase border border-red-200 dark:border-red-800">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Chờ Tiếp Nhận
                </span>
            @elseif($sosInfo->status == 'assigned')
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-4 py-2 rounded-xl font-bold uppercase border border-blue-200 dark:border-blue-800">
                    <i class="fas fa-truck mr-2"></i> Đã Phân Công
                </span>
            @elseif($sosInfo->status == 'in_progress')
                <span class="bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 px-4 py-2 rounded-xl font-bold uppercase border border-teal-200 dark:border-teal-800">
                    <i class="fas fa-tools mr-2"></i> Đang Xử Lý
                </span>
            @elseif($sosInfo->status == 'completed')
                <span class="bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 px-4 py-2 rounded-xl font-bold uppercase border border-slate-200 dark:border-slate-700">
                    <i class="fas fa-check mr-2"></i> Đã Hoàn Thành
                </span>
            @elseif($sosInfo->status == 'cancelled')
                <span class="bg-slate-100 dark:bg-slate-800/80 text-slate-500 px-4 py-2 rounded-xl font-bold uppercase border border-slate-200 dark:border-slate-700">
                    <i class="fas fa-ban mr-2"></i> Đã Hủy
                </span>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Details & Actions -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Thông Tin Khách Hàng</h3>
                
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-xl text-slate-500 dark:text-slate-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-100 text-lg">
                            {{ $sosInfo->display_name }}
                            @if(!$sosInfo->customer_id)
                                <span class="ml-2 text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-500 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">Khách Vãng Lai</span>
                            @endif
                        </p>
                        <p class="text-slate-500 dark:text-slate-400 text-sm"><i class="fas fa-phone mr-1"></i> {{ $sosInfo->display_phone }}</p>
                    </div>
                </div>

                @if($sosInfo->display_phone && $sosInfo->display_phone !== 'Không có SĐT')
                <a href="tel:{{ $sosInfo->display_phone }}" class="block w-full text-center bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/40 text-green-600 dark:text-green-400 py-3 rounded-xl font-bold transition border border-green-200 dark:border-green-800/30">
                    <i class="fas fa-phone-alt mr-2"></i> Gọi Điện Thoại Ngay
                </a>
                @endif
            </div>

            <!-- Description Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Mô Tả Sự Cố</h3>
                <div class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 p-4 rounded-r-xl">
                    <p class="text-slate-700 dark:text-slate-300 italic">
                        {!! nl2br(e($sosInfo->description ?? 'Không có mô tả chi tiết.')) !!}
                    </p>
                </div>
            </div>

            <!-- Vehicle Info -->
            @if($sosInfo->vehicle)
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Thông Tin Xe</h3>
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                    <div>
                        <p class="font-black text-slate-800 dark:text-slate-100 uppercase text-lg">{{ $sosInfo->vehicle->license_plate }}</p>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $sosInfo->vehicle->make }} {{ $sosInfo->vehicle->model }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            @if($sosInfo->status == 'pending')
                <button onclick="acceptSos({{ $sosInfo->id }})" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl py-4 font-black shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-1">
                    <i class="fas fa-hand-paper mr-2"></i> NHẬN XỬ LÝ CA NÀY
                </button>
            @elseif(in_array($sosInfo->status, ['assigned', 'in_progress']) && $sosInfo->assigned_staff_id === auth()->id())
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                    <p class="text-center text-slate-500 dark:text-slate-400 mb-4 text-sm">Bạn đang phụ trách ca cứu hộ này.</p>
                    <div class="space-y-3">
                        @if($sosInfo->status == 'assigned')
                        <button onclick="updateStatus({{ $sosInfo->id }}, 'in_progress')" class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded-xl py-3 font-bold transition">
                            <i class="fas fa-play mr-2"></i> Bắt Đầu Di Chuyển / Xử Lý
                        </button>
                        <button onclick="unassignSos({{ $sosInfo->id }})" class="w-full bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl py-3 font-bold transition">
                            <i class="fas fa-times mr-2"></i> Hủy Nhận Ca Này
                        </button>
                        @endif
                        <button onclick="updateStatus({{ $sosInfo->id }}, 'completed')" class="w-full bg-teal-500 hover:bg-teal-600 text-white rounded-xl py-3 font-bold transition">
                            <i class="fas fa-check-double mr-2"></i> Báo Cáo Hoàn Thành
                        </button>
                    </div>
                </div>
            @elseif($sosInfo->assigned_staff_id && $sosInfo->status !== 'completed' && $sosInfo->status !== 'cancelled')
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800/30 text-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/50 rounded-full flex justify-center items-center mx-auto mb-3 text-blue-500 dark:text-blue-400">
                        <i class="fas fa-user-hard-hat text-xl"></i>
                    </div>
                    <p class="text-blue-800 dark:text-blue-300 font-medium">Nhân viên <strong class="text-blue-600 dark:text-blue-400">{{ $sosInfo->assignedStaff->name ?? 'khác' }}</strong> đang xử lý ca này.</p>
                </div>
            @endif

        </div>

        <!-- Right: Map & Images -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Map Container -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-1 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-900 p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Vị Trí Cứu Hộ</h3>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $sosInfo->latitude }},{{ $sosInfo->longitude }}" target="_blank" class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-directions text-blue-500"></i> Chỉ Đường Google Maps
                    </a>
                </div>
                <div id="map"></div>
                <!-- Coordinates fallback -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between text-xs text-slate-500 font-mono">
                    <span>LAT: {{ $sosInfo->latitude }}</span>
                    <span>LNG: {{ $sosInfo->longitude }}</span>
                </div>
            </div>

            <!-- Images -->
            @if($sosInfo->images && is_array($sosInfo->images) && count($sosInfo->images) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Hình Ảnh Hiện Trường</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($sosInfo->images as $img)
                        <a href="{{ Storage::url($img) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:opacity-80 transition group relative">
                            <img src="{{ Storage::url($img) }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-2xl"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $sosInfo->latitude }};
        const lng = {{ $sosInfo->longitude }};
        
        // Initialize Map
        const map = L.map('map').setView([lat, lng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Marker
        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup("<b>Vị trí khách hàng báo sự cố</b><br>Đang chờ cứu hộ.").openPopup();
    });

    // Accept SOS
    function acceptSos(id) {
        Swal.fire({
            title: 'Nhận ca cứu hộ?',
            text: "Bạn có chắc chắn muốn nhận xử lý ca cứu hộ này?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('staff/sos') }}/${id}/accept`, {
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
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Lỗi!', data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi!', 'Lỗi mạng, vui lòng thử lại.', 'error');
                });
            }
        });
    }

    // Update Status
    function updateStatus(id, newStatus) {
        let title = newStatus === 'in_progress' ? 'Bắt đầu di chuyển?' : 'Hoàn thành ca cứu hộ?';
        let text = newStatus === 'in_progress' 
            ? 'Xác nhận bạn đã bắt đầu di chuyển đến hiện trường.' 
            : 'Xác nhận Đã Hoàn Thành ca cứu hộ này?';
            
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus === 'in_progress' ? '#f59e0b' : '#14b8a6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('staff/sos') }}/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Thành công!', 'Đã cập nhật trạng thái.', 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Lỗi!', data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi!', 'Lỗi mạng, vui lòng thử lại.', 'error');
                });
            }
        });
    }

    // Unassign SOS
    function unassignSos(id) {
        Swal.fire({
            title: 'Hủy nhận ca?',
            text: "Bạn chắc chắn muốn trả ca cứu hộ này về danh sách Chờ Tiếp Nhận?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Chắc chắn hủy',
            cancelButtonText: 'Quay lại'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('staff/sos') }}/${id}/unassign`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Đã hủy!', data.message, 'success').then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Lỗi!', data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Lỗi!', 'Lỗi mạng, vui lòng thử lại.', 'error');
                });
            }
        });
    }
</script>
@endpush
