@extends('layouts.admin')

@section('title', 'Tiếp Nhận Xe')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Tiếp Nhận Xe</h1>
            <p class="mt-1 text-slate-400">Tạo phiếu sửa chữa mới cho khách có tài khoản hoặc khách mới.</p>
        </div>
        <a href="{{ route('admin.repair_orders.index') }}" class="inline-flex items-center gap-2 text-slate-400 transition hover:text-white">
            <span class="material-icons-round">arrow_back</span>
            Quay lại danh sách
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="receptionForm" action="{{ route('admin.repair_orders.store') }}" method="POST" class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        @csrf

        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-700/50 bg-slate-900/70 p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-bold text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20 text-sm font-bold text-indigo-300">1</span>
                    Khách hàng
                </h3>

                <div class="mb-4 grid grid-cols-2 gap-2 rounded-lg bg-slate-950 p-1">
                    <button type="button" onclick="toggleCustomerMode('existing')" id="btnCustExisting" class="rounded-md bg-slate-700 py-2 text-sm font-bold text-white">Tìm khách cũ</button>
                    <button type="button" onclick="toggleCustomerMode('new')" id="btnCustNew" class="rounded-md py-2 text-sm font-bold text-slate-400">Khách mới</button>
                </div>

                <div id="custExistingBlock" class="mb-4">
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Tìm theo SĐT / tên</label>
                    <div class="relative">
                        <input type="text" id="customerSearch" oninput="searchCustomer(this.value)" class="w-full rounded-xl border border-slate-700 bg-slate-950 py-3 pl-10 pr-4 text-white outline-none focus:border-indigo-500" placeholder="Nhập số điện thoại hoặc tên">
                        <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                        <div id="customerSearchResults" class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-700 bg-slate-800 shadow-2xl"></div>
                    </div>
                    <input type="hidden" name="customer_id" id="customerIdInput">
                </div>

                <div class="space-y-3 border-t border-slate-700/50 pt-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Số điện thoại <span class="text-red-400">*</span></label>
                        <input type="text" name="customer_phone" id="custPhoneInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 font-mono text-white outline-none focus:border-indigo-500" placeholder="0909123456" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Tên khách hàng <span class="text-red-400">*</span></label>
                        <input type="text" name="customer_name" id="custNameInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Email</label>
                        <input type="email" name="customer_email" id="custEmailInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-indigo-500" placeholder="email@example.com">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/50 bg-slate-900/70 p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-bold text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20 text-sm font-bold text-emerald-300">2</span>
                    Phương tiện
                </h3>

                <div id="vehicleToggleBlock" class="mb-4 grid grid-cols-2 gap-2 rounded-lg bg-slate-950 p-1 opacity-50 pointer-events-none">
                    <button type="button" onclick="toggleVehicleMode('existing')" id="btnVehExisting" class="rounded-md bg-slate-700 py-2 text-sm font-bold text-white">Chọn xe cũ</button>
                    <button type="button" onclick="toggleVehicleMode('new')" id="btnVehNew" class="rounded-md py-2 text-sm font-bold text-slate-400">Xe mới</button>
                </div>

                <div id="vehExistingBlock" class="mb-4">
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Xe đã lưu</label>
                    <select name="vehicle_id" id="vehicleSelect" onchange="fillVehicleDetails(this)" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-emerald-500" disabled>
                        <option value="">Vui lòng chọn khách trước</option>
                    </select>
                </div>

                <div class="space-y-3 border-t border-slate-700/50 pt-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Biển số xe <span class="text-red-400">*</span></label>
                        <input type="text" name="vehicle_license_plate" id="vehPlateInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 font-bold uppercase tracking-wider text-white outline-none focus:border-emerald-500" placeholder="51A-123.45" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Dòng xe <span class="text-red-400">*</span></label>
                            <input type="text" name="vehicle_model" id="vehModelInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-emerald-500" placeholder="Vios" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Hãng</label>
                            <input type="text" name="vehicle_make" id="vehMakeInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-emerald-500" placeholder="Toyota">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Loại xe</label>
                            <select name="vehicle_type" id="vehTypeInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-emerald-500">
                                <option value="sedan">Sedan</option>
                                <option value="suv">SUV</option>
                                <option value="hatchback">Hatchback</option>
                                <option value="mpv">MPV</option>
                                <option value="pickup">Bán tải</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Năm SX</label>
                            <input type="number" name="vehicle_year" id="vehYearInput" value="{{ date('Y') }}" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-white outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Số khung / VIN</label>
                        <input type="text" name="vehicle_vin" id="vehVinInput" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 font-mono uppercase text-white outline-none focus:border-emerald-500" placeholder="Nếu có">
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex h-full flex-col rounded-2xl border border-slate-700/50 bg-slate-900/70 p-6 shadow-xl">
                <h3 class="mb-6 flex items-center gap-2 border-b border-slate-700/50 pb-4 text-lg font-bold text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20 text-sm font-bold text-indigo-300">3</span>
                    Thông tin tiếp nhận
                </h3>

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-400">Số KM hiện tại</label>
                        <input type="number" name="odometer_reading" min="0" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-indigo-500" placeholder="0">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-400">Thời gian trả dự kiến</label>
                        <input type="datetime-local" name="expected_completion_date" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-indigo-500" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                    </div>
                </div>

                <div class="mb-6 flex-grow">
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-400">Mô tả tình trạng / yêu cầu của khách</label>
                    <textarea name="diagnosis_note" rows="10" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-4 leading-relaxed text-white outline-none focus:border-indigo-500" placeholder="- Xe có tiếng kêu lạ&#10;- Thay nhớt máy&#10;- Kiểm tra phanh sau"></textarea>
                </div>

                <div class="mt-auto flex items-center justify-end gap-4 border-t border-slate-700/50 pt-6">
                    <a href="{{ route('admin.repair_orders.index') }}" class="rounded-xl px-6 py-3 font-bold text-slate-400 transition hover:bg-slate-800 hover:text-white">Hủy bỏ</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
                        <span class="material-icons-round">add_task</span>
                        Tạo phiếu sửa chữa
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const customers = @json($customers);
let vehicleDataCache = [];

function toggleCustomerMode(mode) {
    document.getElementById('btnCustExisting').classList.toggle('bg-slate-700', mode === 'existing');
    document.getElementById('btnCustExisting').classList.toggle('text-white', mode === 'existing');
    document.getElementById('btnCustNew').classList.toggle('bg-slate-700', mode === 'new');
    document.getElementById('btnCustNew').classList.toggle('text-white', mode === 'new');
    document.getElementById('custExistingBlock').classList.toggle('hidden', mode === 'new');
    if (mode === 'new') {
        document.getElementById('customerIdInput').value = '';
        document.getElementById('customerSearch').value = '';
        document.getElementById('custPhoneInput').value = '';
        document.getElementById('custNameInput').value = '';
        document.getElementById('custEmailInput').value = '';
        resetVehicleSection();
    }
}

function searchCustomer(query) {
    const box = document.getElementById('customerSearchResults');
    if (query.length < 2) {
        box.classList.add('hidden');
        return;
    }
    const lower = query.toLowerCase();
    const matches = customers.filter(c => (c.phone || '').includes(query) || (c.name || '').toLowerCase().includes(lower)).slice(0, 6);
    box.innerHTML = matches.length ? '' : '<div class="p-3 text-sm text-slate-500">Không tìm thấy khách phù hợp.</div>';
    matches.forEach(c => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'block w-full border-b border-slate-700/50 p-3 text-left transition hover:bg-slate-700';
        item.innerHTML = `<div class="font-bold text-white">${c.name}</div><div class="text-xs text-indigo-300">${c.phone || 'Chưa có SĐT'}</div>`;
        item.onclick = () => selectCustomer(c);
        box.appendChild(item);
    });
    box.classList.remove('hidden');
}

function selectCustomer(customer) {
    document.getElementById('customerSearch').value = customer.name;
    document.getElementById('customerSearchResults').classList.add('hidden');
    document.getElementById('customerIdInput').value = customer.id;
    document.getElementById('custPhoneInput').value = customer.phone || '';
    document.getElementById('custNameInput').value = customer.name || '';
    document.getElementById('custEmailInput').value = customer.email || '';
    loadVehiclesForCustomer(customer.id);
}

function resetVehicleSection() {
    document.getElementById('vehicleToggleBlock').classList.add('opacity-50', 'pointer-events-none');
    const select = document.getElementById('vehicleSelect');
    select.innerHTML = '<option value="">Vui lòng chọn khách trước</option>';
    select.disabled = true;
    clearVehicleDetails();
}

function loadVehiclesForCustomer(customerId) {
    const select = document.getElementById('vehicleSelect');
    select.innerHTML = '<option>Đang tải...</option>';
    fetch(`/admin/customers/${customerId}/vehicles-json`)
        .then(res => res.json())
        .then(vehicles => {
            vehicleDataCache = vehicles;
            select.innerHTML = '<option value="">Chọn xe đã lưu</option>';
            vehicles.forEach(v => select.innerHTML += `<option value="${v.id}">${v.license_plate} - ${v.make || ''} ${v.model || ''}</option>`);
            select.disabled = false;
            document.getElementById('vehicleToggleBlock').classList.remove('opacity-50', 'pointer-events-none');
            toggleVehicleMode(vehicles.length ? 'existing' : 'new');
        })
        .catch(() => select.innerHTML = '<option value="">Không tải được danh sách xe</option>');
}

function toggleVehicleMode(mode) {
    document.getElementById('btnVehExisting').classList.toggle('bg-slate-700', mode === 'existing');
    document.getElementById('btnVehExisting').classList.toggle('text-white', mode === 'existing');
    document.getElementById('btnVehNew').classList.toggle('bg-slate-700', mode === 'new');
    document.getElementById('btnVehNew').classList.toggle('text-white', mode === 'new');
    document.getElementById('vehExistingBlock').classList.toggle('hidden', mode === 'new');
    if (mode === 'new') {
        document.getElementById('vehicleSelect').value = '';
        clearVehicleDetails();
    }
}

function fillVehicleDetails(select) {
    const vehicle = vehicleDataCache.find(v => v.id == select.value);
    if (!vehicle) return;
    document.getElementById('vehPlateInput').value = vehicle.license_plate || '';
    document.getElementById('vehModelInput').value = vehicle.model || '';
    document.getElementById('vehMakeInput').value = vehicle.make || '';
    document.getElementById('vehTypeInput').value = vehicle.type || 'sedan';
    document.getElementById('vehYearInput').value = vehicle.year || new Date().getFullYear();
    document.getElementById('vehVinInput').value = vehicle.vin || '';
}

function clearVehicleDetails() {
    document.getElementById('vehPlateInput').value = '';
    document.getElementById('vehModelInput').value = '';
    document.getElementById('vehMakeInput').value = '';
    document.getElementById('vehTypeInput').value = 'sedan';
    document.getElementById('vehYearInput').value = new Date().getFullYear();
    document.getElementById('vehVinInput').value = '';
}
</script>
@endsection
