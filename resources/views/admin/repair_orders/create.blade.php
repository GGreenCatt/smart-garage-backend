@extends('layouts.admin')

@section('title', 'Tiếp Nhận Xe')

@section('content')
<!-- Tailwind Plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#6366f1", // Indigo 500
                    "background-light": "#f3f4f6",
                    "background-dark": "#0f172a", // Slate 900
                    "surface-dark": "#1e293b", // Slate 800
                    "glass-dark": "rgba(30, 41, 59, 0.7)",
                    success: "#10b981",
                    danger: "#ef4444",
                    warning: "#f59e0b",
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
            },
        },
    };
</script>

<div class="min-h-screen font-sans text-gray-100 p-4 md:p-8">
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">Tiếp Nhận Xe</h1>
            <p class="text-slate-400 mt-1">Tạo lệnh sửa chữa mới / Tiếp nhận khách vãng lai</p>
        </div>
        <a href="{{ route('admin.repair_orders.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span class="material-icons-round">arrow_back</span>
            Quay lại danh sách
        </a>
    </div>

    <!-- Main Form -->
    <form id="receptionForm" action="{{ route('admin.repair_orders.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        
        <!-- Left Column: Customer & Vehicle Info -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Step 1: Customer -->
            <div class="bg-surface-dark border border-slate-700/50 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-primary">person_add</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="bg-primary/20 text-primary w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">1</span>
                    Khách Hàng
                </h3>
                
                <div class="space-y-4">
                    <!-- Toggle Existing/New -->
                    <div class="flex bg-slate-900 p-1 rounded-lg">
                        <button type="button" onclick="toggleCustomerMode('existing')" id="btnCustExisting" class="flex-1 py-1.5 px-3 rounded-md text-sm font-medium transition-all bg-slate-700 text-white shadow">
                            Tìm Khách Cũ
                        </button>
                        <button type="button" onclick="toggleCustomerMode('new')" id="btnCustNew" class="flex-1 py-1.5 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all">
                            Khách Mới
                        </button>
                    </div>

                    <!-- Existing Mode -->
                    <div id="custExistingBlock" class="block space-y-3">
                         <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tìm theo SĐT / Tên</label>
                         <div class="relative">
                            <input type="text" id="customerSearch" oninput="searchCustomer(this.value)" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder-slate-600" placeholder="Nhập số điện thoại...">
                            <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                            <!-- Dropdown Results -->
                             <div id="customerSearchResults" class="hidden absolute top-full left-0 right-0 mt-2 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-60 overflow-y-auto">
                                 <!-- Dynamic Items -->
                             </div>
                         </div>
                         <input type="hidden" name="customer_id" id="customerIdInput">
                    </div>

                    <!-- Details Fields (Shared for New/Preview) -->
                    <div id="custDetailsBlock" class="space-y-3 pt-2 border-t border-slate-700/50">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Số Điện Thoại <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_phone" id="custPhoneInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none font-mono" placeholder="09xxx..." required>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tên Khách Hàng <span class="text-red-500">*</span></label>
                             <input type="text" name="customer_name" id="custNameInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none" placeholder="Nguyễn Văn A" required>
                        </div>
                         <div>
                             <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Email (Tùy chọn)</label>
                             <input type="email" name="customer_email" id="custEmailInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none" placeholder="email@example.com">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Step 2: Vehicle -->
            <div class="bg-surface-dark border border-slate-700/50 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-emerald-500">directions_car_filled</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="bg-emerald-500/20 text-emerald-500 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">2</span>
                    Phương Tiện
                </h3>

                <div class="space-y-4">
                     <!-- Toggle Existing Only valid if customer selected with vehicles -->
                    <div id="vehicleToggleBlock" class="flex bg-slate-900 p-1 rounded-lg opacity-50 pointer-events-none">
                        <button type="button" onclick="toggleVehicleMode('existing')" id="btnVehExisting" class="flex-1 py-1.5 px-3 rounded-md text-sm font-medium transition-all bg-slate-700 text-white shadow">
                            Chọn Xe Cũ
                        </button>
                         <button type="button" onclick="toggleVehicleMode('new')" id="btnVehNew" class="flex-1 py-1.5 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all">
                            Xe Mới
                        </button>
                    </div>

                     <!-- Existing Select -->
                    <div id="vehExistingBlock" class="block">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Danh Sách Xe Đã Lưu</label>
                         <select name="vehicle_id" id="vehicleSelect" onchange="fillVehicleDetails(this)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <option value="">-- Vui lòng chọn khách trước --</option>
                        </select>
                    </div>

                     <!-- Vehicle Details -->
                     <div id="vehDetailsBlock" class="space-y-3 pt-2 border-t border-slate-700/50">
                        <div>
                             <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Biển Số Xe <span class="text-red-500">*</span></label>
                             <input type="text" name="vehicle_license_plate" id="vehPlateInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none uppercase font-bold tracking-wider placeholder-slate-600" placeholder="29A-12345" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Mẫu Xe (Model) <span class="text-red-500">*</span></label>
                                <input type="text" name="vehicle_model" id="vehModelInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" placeholder="Vios, CX5..." required>
                            </div>
                             <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Hãng (Make)</label>
                                <input type="text" name="vehicle_make" id="vehMakeInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" placeholder="Toyota, Mazda...">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Kiểu Xe (Chọn loại tương ứng mô hình 3D)</label>
                                <input type="hidden" name="vehicle_type" id="vehTypeInput" value="sedan">
                                <div class="grid grid-cols-3 gap-2">
                                    <!-- Sedan -->
                                    <div onclick="selectVehicleType('sedan')" id="type_sedan" class="cursor-pointer bg-primary/20 border-2 border-primary rounded-xl p-2 text-center hover:bg-slate-800 transition-all vehicle-type-option">
                                        <span class="material-icons-round text-2xl text-white mb-1">directions_car</span>
                                        <div class="text-[10px] font-bold text-white uppercase">Sedan</div>
                                    </div>
                                    <!-- SUV -->
                                    <div onclick="selectVehicleType('suv')" id="type_suv" class="cursor-pointer bg-slate-800 border-2 border-slate-700 rounded-xl p-2 text-center hover:bg-slate-700 transition-all vehicle-type-option">
                                        <span class="material-icons-round text-2xl text-slate-400 mb-1">airport_shuttle</span>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">SUV</div>
                                    </div>
                                    <!-- Hatchback -->
                                    <div onclick="selectVehicleType('hatchback')" id="type_hatchback" class="cursor-pointer bg-slate-800 border-2 border-slate-700 rounded-xl p-2 text-center hover:bg-slate-700 transition-all vehicle-type-option">
                                        <span class="material-icons-round text-2xl text-slate-400 mb-1">directions_car_filled</span>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">Hatchback</div>
                                    </div>
                                    <!-- MPV -->
                                    <div onclick="selectVehicleType('mpv')" id="type_mpv" class="cursor-pointer bg-slate-800 border-2 border-slate-700 rounded-xl p-2 text-center hover:bg-slate-700 transition-all vehicle-type-option">
                                        <span class="material-icons-round text-2xl text-slate-400 mb-1">local_taxi</span>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">MPV</div>
                                    </div>
                                    <!-- Pickup -->
                                    <div onclick="selectVehicleType('pickup')" id="type_pickup" class="cursor-pointer bg-slate-800 border-2 border-slate-700 rounded-xl p-2 text-center hover:bg-slate-700 transition-all vehicle-type-option">
                                        <span class="material-icons-round text-2xl text-slate-400 mb-1">local_shipping</span>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">Bán Tải</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Năm SX</label>
                                <input type="number" name="vehicle_year" id="vehYearInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" placeholder="{{ date('Y') }}">
                            </div>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Số Khung (VIN)</label>
                             <input type="text" name="vehicle_vin" id="vehVinInput" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none uppercase font-mono text-xs" placeholder="Optional">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Repair Details -->
        <div class="lg:col-span-2">
            <div class="bg-surface-dark border border-slate-700/50 rounded-2xl p-6 shadow-xl h-full flex flex-col">
                
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2 pb-4 border-b border-slate-700/50">
                    <span class="bg-indigo-500/20 text-indigo-500 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">3</span>
                    Thông Tin Tiếp Nhận
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Số KM Hiện Tại (Odo)</label>
                        <div class="relative">
                            <input type="number" name="odometer_reading" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-4 pr-12 py-3 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none font-mono text-lg" placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold">KM</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Thời Gian Trả Dự Kiến</label>
                        <input type="datetime-local" name="expected_completion_date" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                    </div>
                </div>

                <div class="mb-6 flex-grow">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Mô Tả Tình Trạng / Yêu Cầu Của Khách</label>
                    <div class="relative h-full min-h-[150px]">
                        <textarea name="diagnosis_note" class="w-full h-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none resize-none leading-relaxed" placeholder="- Xe có tiếng kêu lạ ở động cơ&#10;- Thay nhớt máy&#10;- Kiểm tra phanh sau..."></textarea>
                        <div class="absolute bottom-4 right-4 flex gap-2">
                             <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded border border-slate-700 cursor-pointer hover:bg-slate-700 transition" onclick="appendNote('Thay nhớt')">+ Thay nhớt</span>
                             <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded border border-slate-700 cursor-pointer hover:bg-slate-700 transition" onclick="appendNote('Bảo dưỡng định kỳ')">+ Bảo dưỡng</span>
                             <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded border border-slate-700 cursor-pointer hover:bg-slate-700 transition" onclick="appendNote('Rửa xe')">+ Rửa xe</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-700/50 mt-auto">
                    <a href="{{ route('admin.repair_orders.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">Hủy Bỏ</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transform hover:-translate-y-0.5 transition-all text-base flex items-center gap-2">
                        <span class="material-icons-round">add_task</span>
                        Tạo Lệnh Tiếp Nhận
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // --- Initial State ---
    let vehicleDataCache = [];
    document.addEventListener('DOMContentLoaded', () => {
        // Default to "New" mode visually if no customer selected? 
        // Or "Existing" mode active but empty.
        toggleCustomerMode('existing'); // Default tab
    });

    // --- Customer Logic ---
    function toggleCustomerMode(mode) {
        const btnExisting = document.getElementById('btnCustExisting');
        const btnNew = document.getElementById('btnCustNew');
        const existingBlock = document.getElementById('custExistingBlock');
        const idInput = document.getElementById('customerIdInput');
        
        // Form Inputs
        const phone = document.getElementById('custPhoneInput');
        const name = document.getElementById('custNameInput');
        const email = document.getElementById('custEmailInput');

        if (mode === 'existing') {
            btnExisting.classList.add('bg-slate-700', 'text-white', 'shadow');
            btnExisting.classList.remove('text-slate-400');
            btnNew.classList.remove('bg-slate-700', 'text-white', 'shadow');
            btnNew.classList.add('text-slate-400');
            
            existingBlock.classList.remove('hidden');
            
            // Allow editing but they are typically auto-filled.
            // Maybe make them readonly when an ID is set? 
            // For flexibility, let's keep them editable but warn/clear ID if changed?
            // User requested "Must enter info", so editable is key.
            
        } else { // New
            btnNew.classList.add('bg-slate-700', 'text-white', 'shadow');
            btnNew.classList.remove('text-slate-400');
            btnExisting.classList.remove('bg-slate-700', 'text-white', 'shadow');
            btnExisting.classList.add('text-slate-400');
            
            existingBlock.classList.add('hidden');
            
            // Clear ID to ensure creation
            idInput.value = '';
            
            // Clear fields for fresh entry
            phone.value = '';
            name.value = '';
            email.value = '';
            
            // Reset Vehicle Section
            resetVehicleSection();
        }
    }

    // Search Customers (Client-side from a data dump or simple AJAX?)
    // Given the previous setup, we had logic to fetch vehicles. Now we need to search customers.
    // Let's implement a simple debounce search.
    let debounceTimer;
    function searchCustomer(query) {
        clearTimeout(debounceTimer);
        const resultsDiv = document.getElementById('customerSearchResults');
        
        if (query.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }

        debounceTimer = setTimeout(() => {
             // Mocking AJAX - In real app, replace with fetch('/admin/customers/search?q=' + query)
             // We can use the existing `admin.customers.index` via JSON or create a specific search endpoint.
             // Or, since we have $customers injected in the view (from controller create method: User::where('role', 'customer')->get()), 
             // we can search locally!
             
             // Client-side search for simplicity/speed if list isn't huge (User::all was used).
             const customers = @json($customers); 
             const matches = customers.filter(c => 
                c.phone?.includes(query) || 
                c.name.toLowerCase().includes(query.toLowerCase())
             ).slice(0, 5); // Limit 5

             renderSearchResults(matches);
        }, 300);
    }

    function renderSearchResults(matches) {
        const resultsDiv = document.getElementById('customerSearchResults');
        resultsDiv.innerHTML = '';
        
        if (matches.length === 0) {
            resultsDiv.innerHTML = '<div class="p-3 text-slate-500 text-sm italic">Không tìm thấy. Hãy nhập thông tin mới bên dưới.</div>';
        } else {
            matches.forEach(c => {
                const div = document.createElement('div');
                div.className = 'p-3 hover:bg-slate-700 cursor-pointer border-b border-slate-700/50 last:border-0 transition-colors';
                div.innerHTML = `
                    <div class="font-bold text-white text-sm">${c.name}</div>
                    <div class="text-xs text-primary">${c.phone}</div>
                `;
                div.onclick = () => selectCustomer(c);
                resultsDiv.appendChild(div);
            });
        }
        resultsDiv.classList.remove('hidden');
    }

    function selectCustomer(c) {
        // UI
        document.getElementById('customerSearch').value = c.name; // OR phone?
        document.getElementById('customerSearchResults').classList.add('hidden');
        
        // Fill Data
        document.getElementById('customerIdInput').value = c.id;
        document.getElementById('custPhoneInput').value = c.phone;
        document.getElementById('custNameInput').value = c.name;
        document.getElementById('custEmailInput').value = c.email || '';

        // Load Vehicles
        loadVehiclesForCustomer(c.id);
    }

    // --- Vehicle Logic ---
    function resetVehicleSection() {
        const toggleBlock = document.getElementById('vehicleToggleBlock');
        const select = document.getElementById('vehicleSelect');
        
        toggleBlock.classList.add('opacity-50', 'pointer-events-none');
        select.innerHTML = '<option value="">-- Vui lòng nhập thông tin khách --</option>';
        select.disabled = true;
        select.value = "";
        
        // Clear Details
        clearVehicleDetails();
        toggleVehicleMode('new'); // Force new mode visually? Or reset to neutral.
    }

    function loadVehiclesForCustomer(customerId) {
        const select = document.getElementById('vehicleSelect');
        const toggleBlock = document.getElementById('vehicleToggleBlock');
        
        select.innerHTML = '<option>Đang tải...</option>';
        select.disabled = true;

        fetch(`/admin/customers/${customerId}/vehicles-json`)
            .then(res => res.json())
            .then(vehicles => {
                vehicleDataCache = vehicles; // Store for auto-fill
                
                select.innerHTML = '<option value="">-- Chọn xe đã lưu --</option>';
                vehicles.forEach(v => {
                    select.innerHTML += `<option value="${v.id}">${v.license_plate} - ${v.model}</option>`;
                });
                select.disabled = false;
                
                // Enable Toggle
                toggleBlock.classList.remove('opacity-50', 'pointer-events-none');
                
                // If vehicles exist, default to existing tab. Else New.
                if (vehicles.length > 0) {
                    toggleVehicleMode('existing');
                } else {
                    toggleVehicleMode('new');
                }
            })
            .catch(err => {
                console.error(err);
                select.innerHTML = '<option>Lỗi tải xe</option>';
            });
    }

    function toggleVehicleMode(mode) {
        const btnExisting = document.getElementById('btnVehExisting');
        const btnNew = document.getElementById('btnVehNew');
        const existingBlock = document.getElementById('vehExistingBlock');
        const select = document.getElementById('vehicleSelect');
        
        // Inputs
        const plate = document.getElementById('vehPlateInput');
        const model = document.getElementById('vehModelInput');

        if (mode === 'existing') {
            btnExisting.classList.add('bg-slate-700', 'text-white', 'shadow');
            btnExisting.classList.remove('text-slate-400');
            btnNew.classList.remove('bg-slate-700', 'text-white', 'shadow');
            btnNew.classList.add('text-slate-400');
            
            existingBlock.classList.remove('hidden');
            
            // If creation was intended, we might want to clear inputs?
            // But if user selects a vehicle, we fill inputs.
            
        } else { // New
            btnNew.classList.add('bg-slate-700', 'text-white', 'shadow');
            btnNew.classList.remove('text-slate-400');
            btnExisting.classList.remove('bg-slate-700', 'text-white', 'shadow');
            btnExisting.classList.add('text-slate-400');
            
            existingBlock.classList.add('hidden');
            select.value = ""; // Deselect
            
            clearVehicleDetails();
        }
    }

    function fillVehicleDetails(select) {
        const id = select.value;
        const vehicle = vehicleDataCache.find(v => v.id == id);
        
        if (vehicle) {
            document.getElementById('vehPlateInput').value = vehicle.license_plate;
            document.getElementById('vehModelInput').value = vehicle.model;
            document.getElementById('vehMakeInput').value = vehicle.make;
            document.getElementById('vehYearInput').value = vehicle.year;
            document.getElementById('vehVinInput').value = vehicle.vin || '';
            
            // Set Type
            if (vehicle.type) {
                selectVehicleType(vehicle.type);
            }
        }
    }

    function clearVehicleDetails() {
        document.getElementById('vehPlateInput').value = '';
        document.getElementById('vehModelInput').value = '';
        document.getElementById('vehMakeInput').value = '';
        document.getElementById('vehYearInput').value = new Date().getFullYear();
        document.getElementById('vehVinInput').value = '';
        selectVehicleType('sedan'); // Reset default
    }

    function selectVehicleType(type) {
        // Update Hidden Input
        document.getElementById('vehTypeInput').value = type;

        // Visual Update
        const allTypes = ['sedan', 'suv', 'hatchback', 'mpv', 'pickup'];
        allTypes.forEach(t => {
            const el = document.getElementById('type_' + t);
            const icon = el.querySelector('.material-icons-round');
            const text = el.querySelector('div');

            if (t === type) {
                // Active State
                el.classList.remove('bg-slate-800', 'border-slate-700');
                el.classList.add('bg-primary/20', 'border-primary');
                
                icon.classList.remove('text-slate-400');
                icon.classList.add('text-white');
                
                text.classList.remove('text-slate-400');
                text.classList.add('text-white');
            } else {
                // Inactive State
                el.classList.add('bg-slate-800', 'border-slate-700');
                el.classList.remove('bg-primary/20', 'border-primary');
                
                icon.classList.add('text-slate-400');
                icon.classList.remove('text-white');
                
                text.classList.add('text-slate-400');
                text.classList.remove('text-white');
            }
        });
    }

    function appendNote(text) {
        const textarea = document.querySelector('textarea[name="diagnosis_note"]');
        const currentVal = textarea.value;
        if (currentVal) {
            textarea.value = currentVal + '\n- ' + text;
        } else {
            textarea.value = '- ' + text;
        }
        textarea.focus();
    }
</script>
@endsection
