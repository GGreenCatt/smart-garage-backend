@extends('layouts.customer')

@section('title', 'Yêu Cầu Cứu Hộ Khẩn Cấp (SOS)')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 350px; width: 100%; border-radius: 1rem; z-index: 0; }
    .pulse-ring {
        content: '';
        width: 100px;
        height: 100px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        border-radius: 50%;
        animation: pulse 2s infinite;
        z-index: -1;
        pointer-events: none;
    }
    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(0.5); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: translate(-50%, -50%) scale(1); box-shadow: 0 0 0 30px rgba(239, 68, 68, 0); }
        100% { transform: translate(-50%, -50%) scale(0.5); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto flex flex-col pt-24 pb-8 px-4 h-screen">

    <!-- Header -->
    <div class="bg-red-500 rounded-3xl p-8 text-white relative overflow-hidden shadow-lg shadow-red-500/30 mb-8 shrink-0">
        <div class="relative z-10 flex gap-6 items-center">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex justify-center items-center shrink-0">
                <i class="fas fa-truck-medical text-3xl text-white"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black mb-1">Cứu Hộ Khẩn Cấp</h1>
                <p class="text-red-100 font-medium">Gara chúng tôi sẽ cử nhân viên đến hỗ trợ bạn ngay lập tức!</p>
            </div>
        </div>
        
        <!-- Decorative bg -->
        <i class="fas fa-exclamation-triangle absolute -bottom-10 -right-4 text-9xl text-white/10 transform -rotate-12"></i>
    </div>

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
            <i class="fas fa-times-circle text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('customer.sos.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto no-scrollbar pb-10" id="sosForm">
        @csrf
        <div class="space-y-6">
            
            <!-- Map Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-red-500"></i> Xác Định Vị Trí Sự Cố
                    </h2>
                    <button type="button" id="btnLocate" class="bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
                        <i class="fas fa-crosshairs"></i> Vị Trí Hiện Tại
                    </button>
                </div>
                
                <div class="relative rounded-2xl overflow-hidden border-2 border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 z-0">
                    <div id="map"></div>
                    <div id="mapLoading" class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-[2px] z-10 hidden flex-col justify-center items-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-indigo-500 mb-3 block"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Đang lấy định vị...</p>
                    </div>
                </div>
                
                <input type="hidden" name="latitude" id="lat" required>
                <input type="hidden" name="longitude" id="lng" required>
                
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-4 text-center">
                    Gợi ý: Bạn có thể kéo bản đồ để điều chỉnh vị trí ghim chữ thập tĩnh giữa màn hình (nếu định vị sai lệch).
                </p>
            </div>

            <!-- Details Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-6">
                    <i class="fas fa-info-circle text-indigo-500"></i> Thông Tin Sự Cố
                </h2>
                
                <div class="space-y-5">
                    @if(!auth()->check())
                        <!-- Guest Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Họ và tên của bạn <span class="text-red-500">*</span></label>
                                <input type="text" name="guest_name" required placeholder="Nhập tên để nhân viên dễ gọi..." class="w-full border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition p-3">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                                <input type="text" name="guest_phone" required placeholder="SĐT để nhân viên gọi khi đến nơi..." class="w-full border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition p-3">
                            </div>
                        </div>
                    @else
                        <!-- Vehicle Select -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Xe Đang Bị Lỗi (Tuỳ chọn)</label>
                            <select name="vehicle_id" class="w-full border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition p-3">
                                <option value="">-- Chọn xe bị lỗi trong danh sách của bạn --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->license_plate }} - {{ $v->make }} {{ $v->model }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Mô tả tình trạng lỗi <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="3" required placeholder="Ví dụ: Xe đang đi bị xịt lốp, nổ máy không lên..." class="w-full border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition p-3"></textarea>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Hình Ảnh Sự Cố (Tối đa 3 ảnh)</label>
                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-6 text-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition cursor-pointer relative" id="dropArea">
                            <input type="file" name="images[]" id="fileUpload" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImages(this)">
                            <div class="text-slate-500 dark:text-slate-400">
                                <i class="fas fa-camera text-4xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Bấm để chụp ảnh hoặc tải lên</span>
                                <span class="text-xs">PNG, JPG, HEIC tối đa 5MB</span>
                            </div>
                        </div>
                        <div id="imagePreview" class="grid grid-cols-3 gap-3 mt-4 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" id="btnSubmit" class="w-full bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 text-white rounded-2xl py-4 font-black text-lg shadow-lg shadow-red-500/30 transition transform hover:-translate-y-1 block relative overflow-hidden">
                <span class="relative z-10"><i class="fas fa-paper-plane mr-2"></i> GỬI YÊU CẦU CỨU HỘ NGAY</span>
            </button>
            
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const btnLocate = document.getElementById('btnLocate');
        const loading = document.getElementById('mapLoading');

        // Default Vietnam center
        let defaultLat = 21.028511; 
        let defaultLng = 105.804817;

        const map = L.map('map').setView([defaultLat, defaultLng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Center Crosshair Marker overlay
        const mapContainer = document.getElementById('map');
        const crosshair = document.createElement('div');
        crosshair.className = 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none mt-2';
        crosshair.innerHTML = `
            <div class="relative flex flex-col items-center">
                <div class="pulse-ring"></div>
                <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png" width="30" class="drop-shadow-lg z-10">
                <div class="w-2 h-2 bg-black/30 rounded-full mt-[-6px] z-0 blur-[1px]"></div>
            </div>`;
        mapContainer.appendChild(crosshair);

        // Update inputs on drag end
        map.on('moveend', function() {
            const center = map.getCenter();
            latInput.value = center.lat;
            lngInput.value = center.lng;
        });

        // Set initial value
        latInput.value = defaultLat;
        lngInput.value = defaultLng;

        // Geolocation
        btnLocate.addEventListener('click', () => {
            if (navigator.geolocation) {
                loading.classList.remove('hidden');
                loading.classList.add('flex');
                
                navigator.geolocation.getCurrentPosition(position => {
                    const { latitude, longitude } = position.coords;
                    map.flyTo([latitude, longitude], 17);
                    latInput.value = latitude;
                    lngInput.value = longitude;
                    
                    loading.classList.add('hidden');
                    loading.classList.remove('flex');
                }, error => {
                    loading.classList.add('hidden');
                    loading.classList.remove('flex');
                    Swal.fire({
                        title: 'Không thể lấy vị trí',
                        text: 'Vui lòng kiểm tra quyền truy cập vị trí của trình duyệt hoặc tự kéo bản đồ để chọn điểm.',
                        icon: 'warning',
                        confirmButtonText: 'Đã hiểu',
                        confirmButtonColor: '#4f46e5'
                    });
                }, { enableHighAccuracy: true, timeout: 10000 });
            } else {
                Swal.fire({
                    title: 'Lỗi',
                    text: 'Trình duyệt không hỗ trợ định vị.',
                    icon: 'error',
                    confirmButtonText: 'Đóng'
                });
            }
        });
        
        // Auto locate on first load
        setTimeout(() => btnLocate.click(), 500);

        // Form Submission Ajax
        const form = document.getElementById('sosForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang Gửi...';
            btn.disabled = true;
            btn.classList.add('opacity-75');

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Đã gửi yêu cầu!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || '{{ route("customer.sos.index") }}';
                    });
                } else {
                    Swal.fire('Lỗi!', data.message || 'Gửi yêu cầu thất bại', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    btn.classList.remove('opacity-75');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Lỗi kết nối!', 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            });
        });
    });

    // Image Preview
    function previewImages(input) {
        const previewGrid = document.getElementById('imagePreview');
        previewGrid.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            previewGrid.classList.remove('hidden');
            let count = Math.min(input.files.length, 3); // Max 3 preview
            
            for(let i=0; i<count; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewGrid.innerHTML += `
                        <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 relative group">
                            <img src="${e.target.result}" class="w-full h-full object-cover">
                        </div>`;
                }
                reader.readAsDataURL(input.files[i]);
            }
        } else {
            previewGrid.classList.add('hidden');
        }
    }
</script>
@endpush
