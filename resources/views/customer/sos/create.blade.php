@extends('layouts.customer')

@section('title', 'Yêu cầu cứu hộ SOS')

@section('body_class', 'overflow-x-hidden bg-slate-950')

@section('footer')
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    .sos-shell { min-height: 100dvh; background: #0f172a; }
    .sos-map { height: clamp(18rem, 42vh, 28rem); width: 100%; z-index: 0; }
    .sos-card { border: 1px solid rgba(148, 163, 184, .18); background: rgba(15, 23, 42, .92); }
    .sos-field { width: 100%; border: 1px solid rgba(148, 163, 184, .24); background: rgba(2, 6, 23, .72); color: #fff; border-radius: 1rem; padding: .9rem 1rem; outline: none; }
    .sos-field:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .18); }
    .sos-quick-chip { border: 1px solid rgba(148, 163, 184, .24); background: rgba(255, 255, 255, .06); color: #e2e8f0; }
    .sos-quick-chip.is-selected { border-color: rgba(248, 113, 113, .8); background: rgba(239, 68, 68, .18); color: #fff; }
    .sos-crosshair { filter: drop-shadow(0 14px 20px rgba(15, 23, 42, .5)); }
    @media (max-width: 767px) {
        .sos-map { height: 18rem; }
        .sos-bottom-bar { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
    }
</style>
@endpush

@section('content')
<div class="sos-shell pt-20 text-white">
    <form action="{{ route('customer.sos.store') }}" method="POST" enctype="multipart/form-data" id="sosForm" class="mx-auto grid min-h-[calc(100dvh-5rem)] max-w-7xl grid-cols-1 gap-4 px-4 pb-28 pt-4 md:grid-cols-[minmax(0,1.08fr)_minmax(23rem,.92fr)] md:px-6 md:pb-8">
        @csrf

        <section class="sos-card overflow-hidden rounded-2xl">
            <div class="flex flex-col gap-4 border-b border-white/10 p-4 md:flex-row md:items-center md:justify-between md:p-5">
                <div class="min-w-0">
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-red-400/30 bg-red-500/15 px-3 py-1 text-xs font-black uppercase tracking-wide text-red-100">
                        <span class="size-2 rounded-full bg-red-400"></span>
                        SOS 24/7
                    </div>
                    <h1 class="text-2xl font-black tracking-tight md:text-4xl">Cứu hộ khẩn cấp</h1>
                    <p class="mt-1 max-w-2xl text-sm font-medium text-slate-300">Gửi vị trí, tình trạng xe và ảnh hiện trường để nhân viên đến hỗ trợ nhanh hơn.</p>
                </div>
                <a href="tel:19001234" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-4 text-sm font-black text-emerald-100 transition hover:bg-emerald-500/25">
                    <i class="fas fa-phone"></i>
                    Gọi gara
                </a>
            </div>

            <div class="relative">
                <div id="map" class="sos-map bg-slate-900"></div>
                <div class="pointer-events-none absolute left-1/2 top-1/2 z-[400] -translate-x-1/2 -translate-y-full sos-crosshair">
                    <div class="flex flex-col items-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-red-500 text-white shadow-2xl shadow-red-950/50 ring-8 ring-red-500/20">
                            <i class="fas fa-location-dot text-xl"></i>
                        </div>
                        <div class="-mt-1 size-3 rotate-45 bg-red-500"></div>
                    </div>
                </div>
                <div id="mapLoading" class="absolute inset-0 z-[450] hidden flex-col items-center justify-center bg-slate-950/65 backdrop-blur-sm">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-red-300"></i>
                    <p class="mt-3 text-sm font-bold text-slate-100">Đang lấy vị trí hiện tại...</p>
                </div>
                <div class="absolute bottom-4 left-4 right-4 z-[420] flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div id="locationText" class="rounded-xl bg-slate-950/80 px-3 py-2 text-xs font-bold text-slate-200 backdrop-blur">Kéo bản đồ để chọn chính xác vị trí xe</div>
                    <button type="button" id="btnLocate" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-white px-4 text-sm font-black text-slate-950 shadow-lg transition hover:bg-red-50">
                        <i class="fas fa-crosshairs text-red-500"></i>
                        Định vị tôi
                    </button>
                </div>
            </div>

            <div id="locationAlert" class="hidden border-t border-amber-400/20 bg-amber-500/10 p-4 text-sm text-amber-100">
                <div class="flex gap-3">
                    <i class="fas fa-triangle-exclamation mt-1 text-amber-300"></i>
                    <div>
                        <p class="font-black">Không lấy được vị trí tự động.</p>
                        <p class="mt-1 text-amber-100/80">Hãy cấp quyền vị trí cho trình duyệt hoặc kéo bản đồ đến đúng nơi xe đang dừng.</p>
                    </div>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="sos-card rounded-2xl p-4 md:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black">Thông tin sự cố</h2>
                    <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-100">Bắt buộc</span>
                </div>

                <input type="hidden" name="latitude" id="lat" required>
                <input type="hidden" name="longitude" id="lng" required>

                <div class="space-y-4">
                    @if(!auth()->check())
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase text-slate-400">Họ tên</label>
                                <input type="text" name="guest_name" required class="sos-field" placeholder="Tên của bạn">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase text-slate-400">Số điện thoại</label>
                                <input type="tel" name="guest_phone" required class="sos-field" placeholder="Số để nhân viên gọi">
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase text-slate-400">Xe gặp sự cố</label>
                            <select name="vehicle_id" class="sos-field">
                                <option value="">Xe ngoài danh sách / chưa chọn</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate }} - {{ trim(($vehicle->make ?? '').' '.$vehicle->model) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase text-slate-400">Tình trạng nhanh</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Xe không nổ máy', 'Xịt lốp / bể lốp', 'Hết bình ắc quy', 'Tai nạn / va chạm'] as $preset)
                                <button type="button" class="sos-quick-chip rounded-xl px-3 py-2 text-left text-sm font-bold transition" data-sos-preset="{{ $preset }}">{{ $preset }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase text-slate-400">Mô tả chi tiết</label>
                        <textarea name="description" id="description" rows="4" required class="sos-field resize-none" placeholder="VD: Xe dừng ở lề đường, không nổ máy, có trẻ em đi cùng..."></textarea>
                    </div>
                </div>
            </section>

            <section class="sos-card rounded-2xl p-4 md:p-5">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black">Ảnh hiện trường</h2>
                    <span class="text-xs font-bold text-slate-400">Tối đa 3 ảnh</span>
                </div>
                <label for="fileUpload" class="flex cursor-pointer items-center gap-3 rounded-2xl border border-dashed border-slate-500/50 bg-white/5 p-4 transition hover:bg-white/10">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-200">
                        <i class="fas fa-camera text-xl"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-black">Chụp hoặc tải ảnh lên</span>
                        <span id="fileHint" class="block truncate text-sm text-slate-400">Ảnh giúp nhân viên chuẩn bị đúng dụng cụ.</span>
                    </span>
                    <input type="file" name="images[]" id="fileUpload" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                </label>
                <div id="imagePreview" class="mt-3 hidden grid-cols-3 gap-2"></div>
            </section>
        </aside>

        <div class="sos-bottom-bar fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-slate-950/95 p-3 backdrop-blur md:hidden">
            <div class="mx-auto flex max-w-7xl gap-2">
                <a href="tel:19001234" class="flex h-12 w-14 shrink-0 items-center justify-center rounded-xl border border-emerald-400/30 bg-emerald-500/15 text-emerald-100">
                    <i class="fas fa-phone"></i>
                </a>
                <button type="submit" id="btnSubmitMobile" class="h-12 flex-1 rounded-xl bg-red-600 text-sm font-black text-white shadow-lg shadow-red-950/40">
                    Gửi yêu cầu SOS
                </button>
            </div>
        </div>

        <button type="submit" id="btnSubmit" class="hidden h-14 items-center justify-center gap-2 rounded-2xl bg-red-600 text-base font-black text-white shadow-xl shadow-red-950/40 transition hover:bg-red-500 md:flex md:col-start-2">
            <i class="fas fa-paper-plane"></i>
            Gửi yêu cầu cứu hộ
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const btnLocate = document.getElementById('btnLocate');
    const loading = document.getElementById('mapLoading');
    const locationAlert = document.getElementById('locationAlert');
    const locationText = document.getElementById('locationText');
    const form = document.getElementById('sosForm');
    const submitButtons = [document.getElementById('btnSubmit'), document.getElementById('btnSubmitMobile')].filter(Boolean);

    let defaultLat = 21.028511;
    let defaultLng = 105.804817;
    const map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLng], 15);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: 'OpenStreetMap'
    }).addTo(map);

    function setPosition(lat, lng, label = 'Vị trí đã chọn') {
        latInput.value = lat;
        lngInput.value = lng;
        locationText.textContent = `${label}: ${Number(lat).toFixed(5)}, ${Number(lng).toFixed(5)}`;
    }

    map.on('moveend', () => {
        const center = map.getCenter();
        setPosition(center.lat, center.lng, 'Vị trí trên bản đồ');
    });
    setPosition(defaultLat, defaultLng, 'Vị trí mặc định');

    btnLocate.addEventListener('click', () => {
        if (!navigator.geolocation) {
            Swal.fire('Không hỗ trợ định vị', 'Trình duyệt của bạn không hỗ trợ lấy vị trí.', 'error');
            return;
        }

        loading.classList.remove('hidden');
        loading.classList.add('flex');
        locationAlert.classList.add('hidden');

        navigator.geolocation.getCurrentPosition(position => {
            const { latitude, longitude } = position.coords;
            map.flyTo([latitude, longitude], 17);
            setPosition(latitude, longitude, 'Vị trí hiện tại');
            loading.classList.add('hidden');
            loading.classList.remove('flex');
        }, () => {
            loading.classList.add('hidden');
            loading.classList.remove('flex');
            locationAlert.classList.remove('hidden');
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 });
    });

    document.querySelectorAll('[data-sos-preset]').forEach(button => {
        button.addEventListener('click', () => {
            const textarea = document.getElementById('description');
            const value = button.dataset.sosPreset;
            document.querySelectorAll('[data-sos-preset]').forEach(item => item.classList.remove('is-selected'));
            button.classList.add('is-selected');
            textarea.value = textarea.value.trim() ? `${value}. ${textarea.value}` : value;
            textarea.focus();
        });
    });

    setTimeout(() => btnLocate.click(), 400);

    form.addEventListener('submit', event => {
        event.preventDefault();
        const original = submitButtons.map(button => button.innerHTML);
        submitButtons.forEach(button => {
            button.disabled = true;
            button.classList.add('opacity-70');
            button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i>Đang gửi...';
        });

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Đã gửi yêu cầu',
                    text: data.message,
                    icon: 'success',
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => window.location.href = data.redirect || '{{ route("customer.sos.index") }}');
                return;
            }

            Swal.fire('Không gửi được', data.message || 'Vui lòng thử lại.', 'error');
            submitButtons.forEach((button, index) => {
                button.disabled = false;
                button.classList.remove('opacity-70');
                button.innerHTML = original[index];
            });
        })
        .catch(() => {
            Swal.fire('Lỗi kết nối', 'Vui lòng kiểm tra mạng và thử lại.', 'error');
            submitButtons.forEach((button, index) => {
                button.disabled = false;
                button.classList.remove('opacity-70');
                button.innerHTML = original[index];
            });
        });
    });
});

function previewImages(input) {
    const previewGrid = document.getElementById('imagePreview');
    const fileHint = document.getElementById('fileHint');
    previewGrid.innerHTML = '';

    if (!input.files || input.files.length === 0) {
        previewGrid.classList.add('hidden');
        previewGrid.classList.remove('grid');
        fileHint.textContent = 'Ảnh giúp nhân viên chuẩn bị đúng dụng cụ.';
        return;
    }

    const files = Array.from(input.files).slice(0, 3);
    fileHint.textContent = `${files.length} ảnh đã chọn`;
    previewGrid.classList.remove('hidden');
    previewGrid.classList.add('grid');

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = event => {
            previewGrid.insertAdjacentHTML('beforeend', `
                <div class="aspect-square overflow-hidden rounded-xl border border-white/10 bg-slate-900">
                    <img src="${event.target.result}" class="h-full w-full object-cover" alt="">
                </div>
            `);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
