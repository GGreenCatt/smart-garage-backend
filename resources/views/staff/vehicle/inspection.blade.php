@extends((request('iframe') || request('fullscreen')) ? 'layouts.plain' : 'layouts.staff')

@section('title', '3D Inspection - ' . $vehicle->license_plate)

@section('main_class', 'flex flex-row overflow-hidden p-0 absolute inset-0 w-full h-full')

@section('styles')
<style>
    #webgl-container { width: 100%; height: 100%; cursor: grab; }
    #webgl-container:active { cursor: grabbing; }
    .glass-panel { 
        background: rgba(255, 255, 255, 0.95); 
        backdrop-filter: blur(16px); 
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    .defect-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .defect-card:hover {
        transform: translateX(-4px);
        box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.15);
    }
    @keyframes pulse-ring {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .pin-active::before {
        content: '';
        position: absolute;
        inset: -4px;
        border: 2px solid #3b82f6;
        border-radius: 50%;
        animation: pulse-ring 1.5s ease-out infinite;
    }
</style>
@endsection

@section('header_actions')
<div class="flex items-center gap-4">
    <a href="{{ $backUrl ?? route('staff.dashboard') }}" class="text-slate-400 hover:text-white transition flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700" title="Back">
        <i class="fas fa-arrow-left"></i>
        <span class="hidden md:inline text-sm font-semibold">Quay Lại</span>
    </a>
    <div class="h-6 w-px bg-slate-700"></div>
    <div class="flex flex-col">
        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ $vehicle->license_plate }}</span>
        <span class="text-sm font-bold text-white leading-none">{{ $vehicle->model }}</span>
    </div>
</div>
@endsection

@section('content')
<!-- Fullscreen Header (Only visible in fullscreen mode) -->
@if(request('iframe') || request('fullscreen'))
<nav class="absolute top-0 left-0 right-0 z-50 p-4 pointer-events-none">
    <div class="pointer-events-auto inline-flex items-center gap-4 bg-slate-900/80 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 shadow-2xl">
        <a href="{{ $backUrl ?? route('staff.dashboard') }}" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div class="h-6 w-px bg-white/10"></div>
        <div>
            <h1 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inspection Mode</h1>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white">{{ $vehicle->license_plate }}</span>
                <span class="text-xs text-slate-500">•</span>
                <span class="text-sm text-slate-300">{{ $vehicle->model }}</span>
            </div>
        </div>
    </div>
</nav>
@endif
<!-- Center: 3D Canvas -->
<div class="flex-1 bg-gradient-to-br from-slate-50 to-slate-100 relative overflow-hidden">
    <div id="webgl-container" class="absolute inset-0 w-full h-full"></div>

    <!-- Enhanced Floating Tool Panel -->
    <div class="absolute bottom-8 left-8 flex flex-col gap-3 z-50">
        <!-- Interaction Modes -->
        <div class="glass-panel p-2 rounded-2xl shadow-xl flex flex-col gap-2 w-16">
            <button id="btnView" onclick="setMode('view')"
                class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg transition active:scale-95"
                title="Rotate/Zoom">
                <i class="fas fa-arrows-alt text-lg"></i>
            </button>
            <button id="btnDefect" onclick="setMode('defect')"
                class="w-12 h-12 rounded-xl bg-white text-red-500 hover:bg-red-50 flex items-center justify-center transition border border-gray-100 active:scale-95 relative"
                title="Ghim Lỗi">
                <i class="fas fa-wrench text-lg"></i>
            </button>
            <div class="h-px bg-gray-200 mx-2"></div>
            <button id="btnColor" onclick="setMode('color')"
                class="w-12 h-12 rounded-xl bg-white text-blue-500 hover:bg-blue-50 flex items-center justify-center transition border border-gray-100 active:scale-95"
                title="Customize Paint">
                <i class="fas fa-paint-brush text-lg"></i>
            </button>
            <button id="btnRotate" onclick="toggleAutoRotate()"
                class="w-12 h-12 rounded-xl bg-white text-gray-500 hover:bg-gray-50 flex items-center justify-center transition border border-gray-100 active:scale-95"
                title="Auto Rotate">
                <i class="fas fa-sync-alt text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Customization Panel (Hidden by default) -->
    <div id="custom-panel" 
         class="absolute bottom-8 left-28 glass-panel p-4 rounded-2xl shadow-2xl w-64 hidden z-20 transform transition-all duration-300 origin-bottom-left">
        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Xưởng Sơn</span>
            <button onclick="setMode('view')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Chọn Bộ Phận</label>
                <select id="part-select" 
                    class="w-full bg-white/50 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Chọn bộ phận...</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Chọn Màu Sắc</label>
                <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-gray-100">
                    <input type="color" id="color-picker" 
                           class="w-10 h-10 rounded-lg border-none cursor-pointer bg-transparent" value="#3b82f6">
                    <div id="color-hex" class="text-xs font-mono text-gray-500 font-bold">#3B82F6</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-50 flex flex-col items-center justify-center transition-opacity duration-500">
        <div class="relative w-24 h-24 mb-4">
            <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
            <i class="fas fa-cube absolute inset-0 flex items-center justify-center text-blue-500 text-2xl animate-pulse"></i>
        </div>
        <h3 class="text-slate-700 font-bold text-lg mb-2">Loading Vehicle Scan</h3>
        <div class="w-64 h-2 bg-slate-100 rounded-full overflow-hidden">
            <div id="loading-bar" class="h-full bg-blue-500 w-0 transition-all duration-300 ease-out"></div>
        </div>
        <p id="loading-error" class="hidden mt-4 text-red-500 text-sm font-semibold bg-red-50 px-4 py-2 rounded-lg border border-red-100 animate-bounce">
            <i class="fas fa-exclamation-triangle mr-1"></i> Check Connection
        </p>
    </div>

    <!-- Interactive Tooltip -->
    <div id="tooltip" class="absolute hidden bg-slate-800 text-white text-xs px-3 py-1.5 rounded-lg shadow-lg pointer-events-none z-30 whitespace-nowrap opacity-0 transform translate-x-2">
        Part Name
    </div>

    <!-- Mode Indicator -->
    <div id="modeIndicator" 
         class="absolute top-6 left-1/2 -translate-x-1/2 glass-panel px-6 py-2 rounded-full shadow-sm flex items-center gap-3 text-xs z-10 transition-all">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
            <span class="font-bold text-slate-700">View Mode</span>
        </div>
    </div>
</div>

<!-- Right Sidebar: Defect List -->
<aside class="w-96 bg-white border-l border-slate-100 flex flex-col z-20 shadow-[0_0_40px_rgba(0,0,0,0.05)] font-sans h-full overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center bg-white sticky top-0 z-10">
        <div>
            <h2 class="font-bold text-slate-800 text-lg tracking-tight">Báo Cáo Kiểm Tra</h2>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5 opacity-80">3D Visual Check</p>
        </div>
        <div class="flex items-center gap-3">
             <span id="saveIndicator" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider transition-opacity duration-300 opacity-0">
                Đã lưu nháp
             </span>
             <span id="defectCount" class="bg-slate-100 text-slate-600 text-[10px] px-2.5 py-1 rounded-full font-bold">
                0 Lỗi
            </span>
        </div>
    </div>

    <!-- Stats Summary (Minimalist) -->
    <div class="px-6 py-2 bg-white flex justify-between items-center border-b border-slate-50 gap-2">
        <div class="flex-1 flex flex-col items-center py-2 px-3 rounded-lg hover:bg-red-50/50 transition-colors group cursor-default">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-red-400 transition-colors">Nghiêm trọng</span>
            <span id="stat-critical" class="text-xl font-bold text-slate-700 leading-none mt-1 group-hover:text-red-500 transition-colors">0</span>
        </div>
        <div class="w-px h-8 bg-slate-100"></div>
        <div class="flex-1 flex flex-col items-center py-2 px-3 rounded-lg hover:bg-orange-50/50 transition-colors group cursor-default">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-orange-400 transition-colors">Trung bình</span>
            <span id="stat-medium" class="text-xl font-bold text-slate-700 leading-none mt-1 group-hover:text-orange-500 transition-colors">0</span>
        </div>
        <div class="w-px h-8 bg-slate-100"></div>
        <div class="flex-1 flex flex-col items-center py-2 px-3 rounded-lg hover:bg-blue-50/50 transition-colors group cursor-default">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-blue-400 transition-colors">Nhẹ</span>
            <span id="stat-minor" class="text-xl font-bold text-slate-700 leading-none mt-1 group-hover:text-blue-500 transition-colors">0</span>
        </div>
    </div>

    <!-- Defect List -->
    <div id="defectList" class="flex-1 min-h-0 overflow-y-auto px-6 py-4 space-y-3 bg-white scroll-smooth relative">
        <!-- Empty State -->
        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-300 pb-20 pointer-events-none">
            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 border border-slate-100 transform rotate-3">
                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
            </div>
            <p class="text-sm font-semibold text-slate-400">Chưa có dữ liệu</p>
        </div>
    </div>

    <!-- Action Area -->
    <div class="p-4 md:p-5 bg-white border-t border-slate-50 space-y-2.5">
        <button onclick="window.saveInspection('draft')"
            class="w-full bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 rounded-lg border border-slate-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group" title="Chỉ nhân viên Garage mới xem được">
            <i class="fas fa-save text-slate-400 group-hover:text-blue-500 transition-colors"></i>
            <span>Lưu Nội Bộ (Chỉ Garage)</span>
        </button>
        <button onclick="window.saveInspection('published')"
            class="w-full bg-slate-900 hover:bg-black text-white font-bold py-2.5 rounded-lg shadow-lg shadow-slate-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group" title="Khách hàng sẽ thấy báo cáo này">
            <i class="fas fa-cloud-upload-alt text-slate-400 group-hover:text-blue-400 transition-colors"></i>
            <span>Lưu & Gửi Khách Hàng</span>
        </button>
        <button onclick="window.exportScreenshot()"
            class="w-full bg-white hover:bg-slate-50 text-slate-600 font-bold py-2.5 rounded-lg border border-slate-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
            <i class="fas fa-camera text-slate-400"></i>
            <span>Xuất Hình Ảnh</span>
        </button>
    </div>
</aside>
@endsection

@push('scripts')
<script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
        }
    }
</script>

<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { RGBELoader } from 'three/addons/loaders/RGBELoader.js';
    
    // --- CONFIGURATION ---
    const vehicleType = "{{ strtolower($vehicle->type) }}";
    let modelFile = 'sedan.glb';
    if (vehicleType.includes('suv')) modelFile = 'suv.glb';
    else if (vehicleType.includes('truck') || vehicleType.includes('pickup')) modelFile = 'Pick-up.glb';
    else if (vehicleType.includes('hatchback')) modelFile = 'hatchback.glb';
    else if (vehicleType.includes('mpv')) modelFile = 'MPV.glb';
    
    const MODEL_URL = "/assets/models/" + modelFile;
    console.log('Target Model URL:', MODEL_URL);

    const API_FETCH = "{{ route('staff.vhc.fetch', ['id' => $vehicle->id, 'order_id' => request('order_id')]) }}";
    const API_SAVE = "{{ route('staff.vhc.save', ['id' => $vehicle->id, 'order_id' => request('order_id')]) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    // --- STATE ---
    let currentMode = 'view'; 
    let carModel = null;
    let defects = [];
    let raycaster = new THREE.Raycaster();
    let mouse = new THREE.Vector2();
    let pinMarkers = [];
    let autoRotate = false;
    let initialCameraPos = null;
    
    // --- DOM ---
    const container = document.getElementById('webgl-container');
    const tooltip = document.getElementById('tooltip');
    const defectListEl = document.getElementById('defectList');
    const defectCountEl = document.getElementById('defectCount');
    const modeIndicator = document.getElementById('modeIndicator');
    
    // --- THREE.JS INIT ---
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf1f5f9); // Match UI background roughly
    scene.environment = null;
    
    const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(6, 4, 8);
    initialCameraPos = camera.position.clone();
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap; // Softer shadows
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.0;
    container.appendChild(renderer.domElement);
    
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.minDistance = 3;
    controls.maxDistance = 15;
    controls.maxPolarAngle = Math.PI / 2 - 0.05; // Prevent going under floor

    // --- ENVIRONMENT & LIGHTING (Enhanced) ---
    
    // 1. HDRI Environment (Reflections)
    new RGBELoader()
        .setPath('') // Use absolute URL
        .load('https://dl.polyhaven.org/file/ph-assets/HDRIs/hdr/1k/royal_esplanade_1k.hdr', function (texture) {
            texture.mapping = THREE.EquirectangularReflectionMapping;
            scene.environment = texture;
            // scene.background = texture; // Optional: viewing the HDRI itself
            console.log("HDRI Loaded. Reflections enabled.");
        }, undefined, (err) => {
            console.error("Failed to load HDRI:", err);
            // Fallback: Add more lights if HDRI fails
            const amb = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(amb);
        });

    // 2. Main Directional Light (Sun)
    const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
    dirLight.position.set(5, 12, 8);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.width = 1024;
    dirLight.shadow.mapSize.height = 1024;
    dirLight.shadow.bias = -0.0001;
    scene.add(dirLight);

    // 3. Fill Lights (Softness)
    const fillLight = new THREE.DirectionalLight(0xeef2ff, 0.5);
    fillLight.position.set(-5, 5, -5);
    scene.add(fillLight);

    // 4. Floor & Grid (New Visuals)
    const grid = new THREE.GridHelper(30, 30, 0xcbd5e1, 0xe2e8f0);
    grid.position.y = 0.001; // Slightly above 0
    scene.add(grid);

    const floorGeo = new THREE.PlaneGeometry(50, 50);
    const floorMat = new THREE.MeshStandardMaterial({ 
        color: 0xf8fafc,
        roughness: 0.1, 
        metalness: 0.1 
    });
    // Or use ShadowMaterial if we want background color to show through:
    // const floorMat = new THREE.ShadowMaterial({ opacity: 0.05 });
    
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.receiveShadow = true;
    // scene.add(floor); // Grid acts as floor visual, shadow plane below

    const shadowPlane = new THREE.Mesh(
        new THREE.PlaneGeometry(50, 50),
        new THREE.ShadowMaterial({ opacity: 0.15 })
    );
    shadowPlane.rotation.x = -Math.PI / 2;
    shadowPlane.position.y = 0.002; // Above grid
    shadowPlane.receiveShadow = true;
    scene.add(shadowPlane);


    // --- LOAD MODEL ---
    const loader = new GLTFLoader();
    const loadBar = document.getElementById('loading-bar');
    const loadOverlay = document.getElementById('loading-overlay');

    // Manual fetch and load with Blob to handle missing Content-Type
    console.log('Starting manual fetch...');
    
    // Timeout Handling
    const fetchTimeout = setTimeout(() => {
        document.getElementById('loading-error').innerHTML = `
            <span class="block mb-1">⏰ Loading Timed Out</span>
            <span class="text-xs font-normal opacity-80">Connection or Model file issue. Please refresh or check console.</span>
        `;
        document.getElementById('loading-error').classList.remove('hidden');
    }, 15000); // 15s timeout

    fetch(MODEL_URL)
        .then(res => {
            clearTimeout(fetchTimeout); // Clear timeout on first byte
            console.log('Manual fetch response:', res.status);
            if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
            const len = res.headers.get('content-length');
            if (len) {
                console.log('Content-Length:', len);
                loadBar.style.width = '50%';
            }
            return res.arrayBuffer();
        })
        .then(buffer => {
            console.log('Buffer received, size:', buffer.byteLength);
            // Create Blob with explicit MIME type
            const blob = new Blob([buffer], { type: 'model/gltf-binary' });
            const objectUrl = URL.createObjectURL(blob);
            
            loader.load(
                objectUrl,
                (gltf) => {
                    console.log('Parse successful');
                    URL.revokeObjectURL(objectUrl); // Clean up
                    
                    carModel = gltf.scene;

                    // --- AUTO-CENTER & SCALE ---
                    const box = new THREE.Box3().setFromObject(carModel);
                    const size = box.getSize(new THREE.Vector3());
                    const center = box.getCenter(new THREE.Vector3());

                    // Center the model
                    // Center the model
                    carModel.position.x += (carModel.position.x - center.x);
                    carModel.position.y -= box.min.y; // Sit on floor
                    carModel.position.z += (carModel.position.z - center.z);
                    
                    // Adjust camera to fit
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const fov = camera.fov * (Math.PI / 180);
                    let cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2));
                    cameraZ *= 1.5; // Zoom out a bit
                    
                    camera.position.set(cameraZ, cameraZ * 0.5, cameraZ);
                    camera.lookAt(0, 0, 0);
                    controls.target.set(0, 0, 0);
                    controls.update();
                    initialCameraPos = camera.position.clone();

                    carModel.traverse(child => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                            // Adjust materials for HDRI
                            if (child.material) {
                                // Enable envMap
                                child.material.envMapIntensity = 1.0; 
                                child.material.needsUpdate = true;
                                
                                // Restore metalness if it was 0
                                if(child.material.metalness === 0) {
                                     child.material.metalness = 0.8; // Shiny car paint
                                     child.material.roughness = 0.2;
                                }
                                
                                // Save original for un-highlight
                                child.userData.originalEmissive = child.material.emissive ? child.material.emissive.getHex() : 0x000000;
                            }
                            
                            // Populate Dropdown
                            if (partSelect && child.name) {
                                const opt = document.createElement('option');
                                opt.value = child.name;
                                opt.textContent = child.name;
                                partSelect.appendChild(opt);
                            }
                        }
                    });
                    scene.add(carModel);
                    loadBar.style.width = '100%';
                    setTimeout(() => {
                        loadOverlay.style.opacity = '0';
                        setTimeout(() => loadOverlay.style.display = 'none', 500);
                    }, 300);
                    
                    // Load existing defects
                    fetch(API_FETCH)
                        .then(res => res.json())
                        .then(data => {
                            if(data.defects && data.defects.length > 0) {
                                const isPublished = data.status === 'published';
                                // Map API data (flat) to Frontend structure (nested pos)
                                defects = data.defects.map(d => ({
                                    id: d.id,
                                    part: d.title || d.part, // DB uses title
                                    description: d.description,
                                    severity: d.severity,
                                    status: 'damage',
                                    isPublished: isPublished,
                                    pos: { 
                                        x: parseFloat(d.pos_x || d.pos.x), 
                                        y: parseFloat(d.pos_y || d.pos.y), 
                                        z: parseFloat(d.pos_z || d.pos.z) 
                                    }
                                }));

                                defects.forEach((d, i) => {
                                    if(d.pos) addDefectToScene(new THREE.Vector3(d.pos.x, d.pos.y, d.pos.z), d.id, d.part, d.severity || 'medium', i);
                                });
                                renderDefects();
                            }
                        });
                },
                (xhr) => {
                   // Progress already handled by fetch sort of, but this is parsing progress
                },
                (error) => {
                    console.error('Error parsing model:', error);
                    URL.revokeObjectURL(objectUrl);
                    document.getElementById('loading-error').textContent = 'Parse error: ' + error.message;
                    document.getElementById('loading-error').classList.remove('hidden');
                }
            );
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('loading-error').textContent = 'Fetch error: ' + error.message;
            document.getElementById('loading-error').classList.remove('hidden');
        });

    // --- ADVANCED STATE ---
    let hoveredPart = null;
    let activeHighlight = null;
    
    // --- DOM ELEMENTS (Dynamic) ---
    const partSelect = document.getElementById('part-select');
    const colorPicker = document.getElementById('color-picker');
    const colorHex = document.getElementById('color-hex');
    const customPanel = document.getElementById('custom-panel');

    // --- MODE SWITCHING ---
    window.setMode = (mode) => {
        try {
            currentMode = mode;
            
            // Update Buttons
            const buttons = {
                view: document.getElementById('btnView'),
                defect: document.getElementById('btnDefect'),
                color: document.getElementById('btnColor'),
                rotate: document.getElementById('btnRotate')
            };
            
            // Reset styles
            Object.values(buttons).forEach(btn => {
                if(!btn) return;
                btn.className = 'w-12 h-12 rounded-xl bg-white text-slate-500 hover:bg-slate-50 flex items-center justify-center transition border border-gray-100 active:scale-95 relative';
            });

            // Set Active Style
            if (mode === 'view') {
                buttons.view.className = 'w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg transition active:scale-95 relative';
            } else if (mode === 'defect') {
                buttons.defect.className = 'w-12 h-12 rounded-xl bg-red-500 text-white flex items-center justify-center shadow-lg transition active:scale-95 relative pin-active';
            } else if (mode === 'color') {
                buttons.color && (buttons.color.className = 'w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-lg transition active:scale-95 relative');
            }

            // Toggle Panels
            if (customPanel) {
                if (mode === 'color') customPanel.classList.remove('hidden');
                else customPanel.classList.add('hidden');
            }

            // Update Indicator
            // Update Indicator
            const modeName = mode === 'view' ? 'Xem 3D' : (mode === 'defect' ? 'Ghim Lỗi' : 'Màu Sắc');
            modeIndicator.innerHTML = `
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full ${mode === 'defect' ? 'bg-red-500' : 'bg-blue-500'} animate-pulse"></div>
                    <span class="font-bold text-slate-700 uppercase tracking-wider">Chế độ ${modeName}</span>
                </div>`;
            
            // ALWAYS ENABLE CONTROLS to allow zoom/pan/rotate in all modes
            controls.enabled = true;
            controls.enableRotate = true; 
            
            // Cursor still indicates mode
            container.style.cursor = (mode === 'defect') ? 'crosshair' : 'grab'; 

            // Reset Highlight on mode change
            if (activeHighlight) unhighlightPart(activeHighlight);
        } catch(e) {
            console.error('Error switching mode:', e);
            // Fallback
             controls.enabled = true;
             container.style.cursor = 'grab';
        }
    };

    window.toggleAutoRotate = () => {
        autoRotate = !autoRotate;
        controls.autoRotate = autoRotate;
        const btn = document.getElementById('btnRotate');
        if(autoRotate) {
             btn.className = 'w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-lg transition active:scale-95 relative';
        } else {
             btn.className = 'w-12 h-12 rounded-xl bg-white text-slate-500 hover:bg-slate-50 flex items-center justify-center transition border border-gray-100 active:scale-95 relative';
        }
    };

    window.resetCamera = () => {
        animateCamera(initialCameraPos, new THREE.Vector3(0,0,0));
        showToast('Đã đặt lại góc nhìn');
    };

    // --- INTERACTION STATE ---
    let mouseDownTime = 0;
    let mouseDownPos = new THREE.Vector2();

    container.addEventListener('mousedown', (e) => {
        mouseDownTime = Date.now();
        mouseDownPos.set(e.clientX, e.clientY);
        if(currentMode !== 'defect') container.style.cursor = 'grabbing';
    });
    
    container.addEventListener('mouseup', () => {
        if(currentMode !== 'defect') container.style.cursor = 'grab';
    });

    // --- INTERACTION ---
    container.addEventListener('mousemove', (e) => {
        // Tooltip Positioning
        tooltip.style.left = (e.clientX + 15) + 'px';
        tooltip.style.top = (e.clientY + 15) + 'px';

        const rect = container.getBoundingClientRect();
        mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        
        raycaster.setFromCamera(mouse, camera);

        // 1. Hovering Car Parts
        if (carModel) {
            // Only highlight if not rotating (approximated by mouse down)
            // Ideally we check drag state, but simple hover is fine
            const intersects = raycaster.intersectObject(carModel, true);
            if (intersects.length > 0) {
                const object = intersects[0].object;
                const partName = object.name || "Unknown Part";
                
                // Show Tooltip
                tooltip.textContent = partName;
                tooltip.classList.remove('hidden', 'opacity-0');
                tooltip.classList.add('opacity-100');
                // Cursor logic: Only crosshair if in defect mode AND not dragging
                if(currentMode === 'defect') container.style.cursor = 'crosshair';
                else container.style.cursor = 'pointer';

                // Highlight Logic
                if (activeHighlight !== object) {
                    if (activeHighlight) unhighlightPart(activeHighlight);
                    highlightPart(object);
                    activeHighlight = object;
                }
            } else {
                tooltip.classList.add('hidden', 'opacity-0');
                tooltip.classList.remove('opacity-100');
                
                if(currentMode === 'defect') container.style.cursor = 'crosshair';
                else container.style.cursor = 'grab'; // Default back to grab
                
                if (activeHighlight) {
                    unhighlightPart(activeHighlight);
                    activeHighlight = null;
                }
            }

            // 2. Hovering Markers (Hitbox Check) - Priority Cursor
            const intersectsMarkers = raycaster.intersectObjects(pinMarkers, true);
            if (intersectsMarkers.length > 0) {
                container.style.cursor = 'pointer';
                // Show tooltip for marker?
                const marker = intersectsMarkers[0].object;
                // Identify marker...
            }
        }
    });

    container.addEventListener('click', (e) => {
        if (!carModel) return;

        // Raycast is already set from mousemove, but set again for safety
        raycaster.setFromCamera(mouse, camera);

        // 0. Check for Marker/Label Interaction (Priority over adding new defects)
        // Check intersections with all pinMarkers and their children (sprites)
        const intersectsMarkers = raycaster.intersectObjects(pinMarkers, true);
        if (intersectsMarkers.length > 0) {
            // Find the root marker object (since we might click the sprite child)
            let targetObj = intersectsMarkers[0].object;
            while(targetObj.parent && targetObj.parent.type !== 'Scene') {
                if (targetObj.userData && targetObj.userData.id) break;
                targetObj = targetObj.parent;
            }

            if (targetObj.userData && targetObj.userData.id) {
                const defectIndex = defects.findIndex(d => d.id === targetObj.userData.id);
                if (defectIndex !== -1) {
                    focusDefect(defectIndex);
                    return; // Stop further processing (don't add new defect)
                }
            }
        }

        // SMART CLICK DETECTION
        // If click duration > 200ms OR moved > 5px, treat as Grid/Rotate interaction, NOT a click
        const clickDuration = Date.now() - mouseDownTime;
        const moveDist = mouseDownPos.distanceTo(new THREE.Vector2(e.clientX, e.clientY));
        
        if (clickDuration > 200 || moveDist > 5) {
            console.log('Interaction ignored (Drag/Rotate detected)');
            return;
        }
        
        raycaster.setFromCamera(mouse, camera);

        // 1. Handle Defect Pinning
        if (currentMode === 'defect') {
            const intersects = raycaster.intersectObject(carModel, true);
            if (intersects.length > 0) {
                const point = intersects[0].point;
                const object = intersects[0].object;
                const partName = object.name || "Unknown Part";
                
                const defectId = Date.now();
                addDefectToScene(point, defectId, partName, 'medium', defects.length);
                
                // Add to data
                defects.push({ 
                    id: defectId, 
                    part: partName, 
                    severity: 'medium', // Default
                    status: 'damage', 
                    description: '',
                    pos: { x: point.x, y: point.y, z: point.z } 
                });
                renderDefects();
                showToast(`Đã ghim lỗi: ${partName}`);
            }
        }
        
        // 2. Handle Color Selection Mode
        else if (currentMode === 'color') {
            const intersects = raycaster.intersectObject(carModel, true);
            if (intersects.length > 0) {
                const object = intersects[0].object;
                // Auto-select part in dropdown
                if (partSelect) partSelect.value = object.name;
                updateColorFromMesh(object);
                showToast(`Đã chọn: ${object.name}`);
            }
        }
    });

    // --- HIGHLIGHTING HELPERS ---
    function highlightPart(mesh) {
        if (mesh.material) {
            if (!mesh.userData.originalEmissive) {
                mesh.userData.originalEmissive = mesh.material.emissive ? mesh.material.emissive.getHex() : 0x000000;
            }
            // Clone to avoid sharing
            if (!mesh.userData.isCloned) {
                mesh.material = mesh.material.clone();
                mesh.userData.isCloned = true;
            }
            mesh.material.emissive = new THREE.Color(0x404040); // Soft grey glow
            mesh.material.emissiveIntensity = 0.5;
        }
    }

    function unhighlightPart(mesh) {
        if (mesh.material) {
            mesh.material.emissive = new THREE.Color(mesh.userData.originalEmissive || 0x000000);
            mesh.material.emissiveIntensity = 0; // Reset
        }
    }

    // --- COLOR CUSTOMIZATION ---
    function updateColorFromMesh(mesh) {
        if (mesh.material && mesh.material.color) {
            const hex = "#" + mesh.material.color.getHexString();
            colorPicker.value = hex;
            colorHex.textContent = hex.toUpperCase();
        }
    }

    if (colorPicker) {
        colorPicker.addEventListener('input', (e) => {
            const hex = e.target.value;
            colorHex.textContent = hex.toUpperCase();
            const partName = partSelect.value;
            
            if (partName && carModel) {
                const mesh = carModel.getObjectByName(partName);
                if (mesh) {
                    if (!mesh.userData.isCloned) {
                        mesh.material = mesh.material.clone();
                        mesh.userData.isCloned = true;
                    }
                    mesh.material.color.set(hex);
                }
            } else if (activeHighlight) {
                // If no part selected in dropdown but one is highlighted/clicked
                 if (!activeHighlight.userData.isCloned) {
                    activeHighlight.material = activeHighlight.material.clone();
                    activeHighlight.userData.isCloned = true;
                }
                activeHighlight.material.color.set(hex);
            }
        });
    }

    if(partSelect) {
        partSelect.addEventListener('change', (e) => {
            const partName = e.target.value;
            if (!carModel) return;
            const mesh = carModel.getObjectByName(partName);
            if (mesh) {
                // Flash highlight
                highlightPart(mesh);
                setTimeout(() => unhighlightPart(mesh), 500);
                updateColorFromMesh(mesh);
                
                // Fly to part logic could go here
            }
        });
    }

    // --- CAMERA ANIMATION ---
    function animateCamera(targetPos, targetLookAt) {
        const startPos = camera.position.clone();
        const startTarget = controls.target.clone();
        const duration = 1000;
        const startTime = Date.now();

        function update() {
            const now = Date.now();
            const progress = Math.min((now - startTime) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3); // Cubic ease out

            camera.position.lerpVectors(startPos, targetPos, ease);
            controls.target.lerpVectors(startTarget, targetLookAt, ease);
            controls.update();

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        update();
    }

    window.exportScreenshot = () => {
        renderer.render(scene, camera);
        const dataURL = renderer.domElement.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `baocao-kiemtra-${Date.now()}.png`;
        link.href = dataURL;
        link.click();
        showToast('Đã tải xuống hình ảnh!');
    };

    
    let autoSaveTimeout;


    function autoSave() {
        clearTimeout(autoSaveTimeout);
        const indicator = document.getElementById('saveIndicator');
        if(indicator) {
            indicator.classList.remove('opacity-0');
            indicator.innerHTML = '<i class="fas fa-sync fa-spin mr-1"></i> Đang lưu nháp...';
        }

        autoSaveTimeout = setTimeout(() => {
            saveInspection('draft', true);
        }, 2000); // Debounce 2s
    }

    // Modified saveInspection to accept status and silent mode
    window.saveInspection = (status = 'published', silent = false) => {
        fetch(API_SAVE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ defects: defects, status: status })
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                if(status === 'published') {
                    showToast('✓ Đã xuất bản kết quả cho khách hàng!');
                    defects.forEach(d => d.isPublished = true);
                    renderDefects();
                } else {
                    // Draft saved
                    showToast('✓ Đã lưu nháp thành công!');
                    
                    const indicator = document.getElementById('saveIndicator');
                    if(indicator) {
                        indicator.classList.remove('opacity-0');
                        indicator.innerHTML = '<i class="fas fa-check text-green-500 mr-1"></i> Đã lưu nháp';
                        setTimeout(() => indicator.classList.add('opacity-0'), 2000);
                    }
                }
            } else {
                if(!silent) showToast('✗ Lưu thất bại', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if(!silent) showToast('✗ Lỗi hệ thống', 'error');
        });
    };

    window.exportReport = () => {
        showToast('PDF export feature coming soon!');
    };
    
    function renderDefects() {
        defectCountEl.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${defects.length} LỖI`;
        
        // Update stats
        const stats = { critical: 0, medium: 0, minor: 0 };
        defects.forEach(d => stats[d.severity || 'medium']++);
        document.getElementById('stat-critical').textContent = stats.critical;
        document.getElementById('stat-medium').textContent = stats.medium;
        document.getElementById('stat-minor').textContent = stats.minor;
        
        if (defects.length === 0) {
            defectListEl.innerHTML = `
                <div class="flex flex-col items-center justify-center py-20 text-slate-300">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                        <i class="fas fa-clipboard-check text-3xl text-slate-300"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Chưa ghi nhận lỗi nào</p>
                    <p class="text-[11px] text-slate-400">Chọn "Ghim Lỗi" để bắt đầu</p>
                </div>`;
            return;
        }
        
        defectListEl.innerHTML = defects.map((d, i) => {
            const severityColors = {
                critical: { border: 'border-l-4 border-l-red-500', text: 'text-red-600', bg: 'hover:bg-red-50/30' },
                medium: { border: 'border-l-4 border-l-orange-500', text: 'text-orange-600', bg: 'hover:bg-orange-50/30' },
                minor: { border: 'border-l-4 border-l-blue-500', text: 'text-blue-600', bg: 'hover:bg-blue-50/30' }
            };
            const theme = severityColors[d.severity || 'medium'];
            
            // Format Part Name: Remove hyphens, Capitalize
            const partDisplayName = (d.part || 'Không xác định')
                .replace(/-/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());

            return `
            <div onclick="focusDefect(${i})" class="group relative bg-white border border-slate-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer ${theme.border} ${theme.bg}">
                ${d.isPublished ? '<div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm z-10 cursor-help" title="Đã lưu công khai"><i class="fas fa-check text-[8px]"></i></div>' : ''}
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Lỗi #${i+1}</span>
                            <div class="font-bold text-slate-800 text-sm leading-tight">${partDisplayName}</div>
                        </div>
                    </div>
                    <button onclick="event.stopPropagation(); removeDefect(${i})" class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-red-500 transition-all p-1 rounded hover:bg-red-50" title="Xóa">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
                
                <div class="space-y-3">
                    <!-- Custom Select -->
                    <div class="relative" onclick="event.stopPropagation()">
                        <select class="w-full bg-slate-50/50 border border-slate-200 rounded-lg pl-9 pr-8 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition appearance-none cursor-pointer hover:border-slate-300"
                            onchange="updateDefect(${i}, 'severity', this.value)">
                            <option value="critical" ${d.severity === 'critical' ? 'selected' : ''}>Nghiêm trọng</option>
                            <option value="medium" ${d.severity === 'medium' ? 'selected' : ''}>Trung bình</option>
                            <option value="minor" ${d.severity === 'minor' ? 'selected' : ''}>Nhẹ</option>
                        </select>
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-circle text-[8px] ${theme.text}"></i>
                        </div>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    
                    <textarea 
                        onclick="event.stopPropagation()"
                        class="w-full bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition resize-none placeholder-slate-400 min-h-[60px]"
                        placeholder="Thêm mô tả chi tiết..."
                        onchange="updateDefect(${i}, 'description', this.value)">${d.description || ''}</textarea>
                </div>
            </div>`;
        }).join('');
    }

    window.updateDefect = (index, field, value) => {
        defects[index][field] = value;
        if(field === 'severity') {
            // Update marker color
            const id = defects[index].id;
            const marker = pinMarkers.find(m => m.userData.id === id);
            if(marker) {
                const colors = { critical: 0xff0000, medium: 0xf59e0b, minor: 0x3b82f6 };
                marker.material.color.setHex(colors[value] || colors.medium);
            }
            renderDefects();
        }
    };

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();
    
    window.addEventListener('resize', () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });

    function createLabelSprite(text) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const size = 64; // Power of 2
        canvas.width = size;
        canvas.height = size;
        
        // Background Circle
        ctx.fillStyle = '#1e293b'; // Slate-800
        ctx.beginPath();
        ctx.arc(size/2, size/2, size/2 - 2, 0, Math.PI * 2);
        ctx.fill();
        
        // Border
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 4;
        ctx.stroke();

        // Text
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = 'bold 32px Arial';
        ctx.fillText(text, size/2, size/2 + 2);

        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({ map: texture, depthTest: false }); // Always visible on top
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(0.4, 0.4, 0.4);
        sprite.center.set(0.5, -1.5); // Float higher above marker
        sprite.renderOrder = 999;
        return sprite;
    }

    function addDefectToScene(pos, id, partName, severity = 'medium', index) {
        const colors = {
            critical: 0xff0000,
            medium: 0xf59e0b,
            minor: 0x3b82f6
        };
        const markerGeo = new THREE.SphereGeometry(0.1, 16, 16);
        const markerMat = new THREE.MeshBasicMaterial({ color: colors[severity] || colors.medium, depthTest: false, transparent: true, opacity: 0.9 });
        const marker = new THREE.Mesh(markerGeo, markerMat);
        marker.position.copy(pos);
        marker.renderOrder = 1;
        scene.add(marker);
        marker.userData = { id };
        pinMarkers.push(marker);

        // Add Hitbox (Invisible sphere for easier clicking)
        const hitboxGeo = new THREE.SphereGeometry(0.3, 8, 8);
        const hitboxMat = new THREE.MeshBasicMaterial({ visible: false }); // Invisible
        const hitbox = new THREE.Mesh(hitboxGeo, hitboxMat);
        marker.add(hitbox);

        // Add Number Sprite
        const num = index !== undefined ? index + 1 : defects.length + 1;
        const labelSprite = createLabelSprite(num);
        marker.add(labelSprite);
        marker.userData.label = labelSprite;
    }

    // Update markers when defects change
    function refreshMarkers() {
        pinMarkers.forEach((m, i) => {
            if(m.userData.label) {
                // Update sprite texture
                m.remove(m.userData.label);
                m.userData.label.geometry.dispose();
                m.userData.label.material.map.dispose();
                m.userData.label.material.dispose();

                const newSprite = createLabelSprite(i + 1);
                m.add(newSprite);
                m.userData.label = newSprite;
            }
        });
    }

    window.removeDefect = (index) => {
        const id = defects[index].id;
        const marker = pinMarkers.find(m => m.userData.id === id);
        if(marker) {
            scene.remove(marker);
            pinMarkers = pinMarkers.filter(m => m !== marker);
        }
        defects.splice(index, 1);
        renderDefects();
        refreshMarkers(); // Re-number
        showToast(`Đã xóa lỗi khỏi danh sách`);
    };

    window.focusDefect = (index) => {
        const defect = defects[index];
        if (!defect) return;
        
        // 1. Highlight Log Card
        const listEl = document.getElementById('defectList');
        // Check if listEl has children and index is valid
        if (listEl && listEl.children.length > index) {
            const cardEl = listEl.children[index]; 
            
            // Scroll into view
            cardEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Visual Flash
            const originalClasses = cardEl.className;
            cardEl.className = `${originalClasses} ring-2 ring-blue-500 bg-blue-50 transform scale-[1.02] transition-all duration-300`;
            
            setTimeout(() => {
                cardEl.className = originalClasses;
            }, 2000);
        }

        // 2. Animate Camera
        const marker = pinMarkers.find(m => m.userData.id === defect.id);
        if (marker) {
            // Move camera CLOSER to the defect
            // Calculate direction from origin (center of car) to marker
            const direction = marker.position.clone().normalize();
            // Position camera 2.5 meters out in that direction
            const targetCamPos = direction.multiplyScalar(2.5).add(marker.position.clone().multiplyScalar(0.2)); 
            
            animateCamera(targetCamPos, marker.position);
            showToast(`Đã chọn lỗi #${index + 1}`);
        }
    }



</script>
@endpush
