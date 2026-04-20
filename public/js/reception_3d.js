import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { RGBELoader } from 'three/addons/loaders/RGBELoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';

// --- CONFIGURATION ---
const MODELS_PATH = './assets/models/';
const CAR_LIST = [
    { name: "Police Cruiser", file: "car_police.glb" },
    { name: "Range Rover", file: "range_rover.glb" },
    { name: "Family MPV", file: "MPV.glb" },
    { name: "Sedan Premium", file: "sedan.glb" },
    { name: "SUV Adventure", file: "suv.glb" },
    { name: "Compact Hatchback", file: "hatchback.glb" },
    { name: "Utility Pick-up", file: "Pick-up.glb" },
    { name: "Cutkit Model", file: "cutkit.glb" },
    { name: "Bumper Car", file: "bumper_car.glb" }
];

// --- STATE ---
let currentMode = 'view'; // view, defect, color
let carModel = null;
let defects = [];
let raycaster = new THREE.Raycaster();
let mouse = new THREE.Vector2();
let pinMarkers = [];
let hoveredPart = null;
let isAutoRotate = false;
let targetCameraPos = null;
let activeCarFile = null; // Track currently loading/active car to prevent race conditions

// --- DOM ELEMENTS ---
const container = document.getElementById('webgl-container');
// const modelSelect = document.getElementById('model-select'); // Removed
const partSelect = document.getElementById('part-select');
const colorPicker = document.getElementById('color-picker');
const colorHex = document.getElementById('color-hex');
const customPanel = document.getElementById('custom-panel');
const defectListEl = document.getElementById('defectList');
const defectCountEl = document.getElementById('defectCount');
const modeLabel = document.getElementById('currentModeLabel');
const loadingOverlay = document.getElementById('loading-overlay');
const loadingBar = document.getElementById('loading-bar');
const loadingError = document.getElementById('loading-error');
const tooltip = document.getElementById('tooltip');
const btnRotate = document.getElementById('btnRotate');

// --- THREE.JS INIT ---
const scene = new THREE.Scene();
scene.background = new THREE.Color(0xf1f5f9);

const vehicleGroup = new THREE.Group();
vehicleGroup.name = 'vehicleGroup';
scene.add(vehicleGroup);

const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
camera.position.set(6, 4, 8);

const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
renderer.setSize(container.clientWidth, container.clientHeight);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5)); // Optimize: Cap pixel ratio
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.0;
container.appendChild(renderer.domElement);

const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping = true;
controls.maxPolarAngle = Math.PI / 2;
controls.autoRotateSpeed = 2.0;

// Lights & Environment
const ambientLight = new THREE.AmbientLight(0xffffff, 0.6); // Increased brightness
scene.add(ambientLight);

// Load HDRI
new RGBELoader()
    .setPath('') // Using absolute URL
    .load('https://dl.polyhaven.org/file/ph-assets/HDRIs/hdr/1k/royal_esplanade_1k.hdr', function (texture) {
        texture.mapping = THREE.EquirectangularReflectionMapping;
        scene.environment = texture;
    }, undefined, (err) => {
        console.error("Failed to load HDRI:", err);
    });

// Showroom Lighting Setup (4-Corner Spots)
function createSpotlight(x, z, intensity) {
    const spot = new THREE.SpotLight(0xffffff, intensity);
    spot.position.set(x, 12, z);
    spot.angle = Math.PI / 4;
    spot.penumbra = 0.5;
    spot.castShadow = true;
    spot.shadow.bias = -0.0001;
    spot.shadow.mapSize.width = 512; // Optimize: Reduced shadow map size
    spot.shadow.mapSize.height = 512;
    scene.add(spot);
    return spot;
}

// Main Key Light
createSpotlight(8, 8, 15);
// Fill Lights
createSpotlight(-8, 8, 8);
createSpotlight(8, -8, 8);
createSpotlight(-8, -8, 8);

const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
directionalLight.position.set(0, 5, 5);
scene.add(directionalLight);

// Floor
const floorGeo = new THREE.PlaneGeometry(25, 25); // Reduced from 40
const floorMat = new THREE.MeshStandardMaterial({
    color: 0xe2e8f0,
    roughness: 0.1,
    metalness: 0
});
const floor = new THREE.Mesh(floorGeo, floorMat);
floor.rotation.x = -Math.PI / 2;
floor.receiveShadow = true;
scene.add(floor);

const grid = new THREE.GridHelper(25, 25, 0xcbd5e1, 0xcbd5e1); // Reduced to match floor
grid.position.y = 0.01;
scene.add(grid);

// --- LOADING MANAGER ---
const loadingManager = new THREE.LoadingManager();

loadingManager.onStart = function (url, itemsLoaded, itemsTotal) {
    if (loadingOverlay) {
        loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
        loadingBar.style.width = '0%';
        loadingError.classList.add('hidden'); // Hide previous errors
    }
};

loadingManager.onProgress = function (url, itemsLoaded, itemsTotal) {
    if (loadingBar) {
        const percent = (itemsLoaded / itemsTotal) * 100;
        loadingBar.style.width = percent + '%';
    }
};

loadingManager.onLoad = function () {
    if (loadingOverlay) {
        loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
        setTimeout(() => {
            loadingBar.style.width = '0%';
        }, 500);
    }
};

loadingManager.onError = function (url) {
    console.error('There was an error loading ' + url);
    if (loadingOverlay) {
        // Keep overlay but show error
        loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
        loadingError.classList.remove('hidden');
        loadingError.textContent = `Error loading resource: ${url.split('/').pop()}`;
    }
};

const loader = new GLTFLoader(loadingManager);

// Configuration DracoLoader (Optimization)
const dracoLoader = new DRACOLoader();
dracoLoader.setDecoderPath('https://www.gstatic.com/draco/versioned/decoders/1.5.6/'); // Use CDN for decoders
loader.setDRACOLoader(dracoLoader);

// --- MODEL FUNCTIONS ---
// --- MODEL FUNCTIONS ---
// initModelSelector removed

function loadCar(fileName) {
    activeCarFile = fileName; // Set active file

    // Reset defects immediately for UI response
    pinMarkers.forEach(p => scene.remove(p));
    pinMarkers = [];
    defects = [];
    renderDefectList();

    // We don't remove carModel here anymore, we'll clear the group on load finish 
    // or we can clear it now to show "loading" state empty.
    vehicleGroup.clear(); // Ensure previous car is GONE.
    carModel = null;

    loader.load(MODELS_PATH + fileName, (gltf) => {
        // Prevent race condition: if user switched cars while loading, ignore this result
        if (fileName !== activeCarFile) return;

        // Clear group again just to be safe (if multiple racing loads)
        vehicleGroup.clear();

        carModel = gltf.scene;
        vehicleGroup.add(carModel);

        const box = new THREE.Box3().setFromObject(carModel);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());

        carModel.position.x -= center.x;
        carModel.position.z -= center.z;
        carModel.position.y -= box.min.y;

        carModel.traverse(node => {
            if (node.isMesh) {
                node.castShadow = true;
                node.receiveShadow = true;
                // Enhance materials
                if (node.material) {
                    node.material.envMapIntensity = 1.0;
                    node.material.needsUpdate = true;
                }
            }
        });

        updatePartDropdown();

        // Smooth camera entry
        const maxDim = Math.max(size.x, size.y, size.z);
        animateCamera(
            new THREE.Vector3(maxDim * 1.5, maxDim * 1, maxDim * 1.5),
            new THREE.Vector3(0, size.y / 2, 0)
        );

        // LB: Reload defects for this car
        loadDefects();
        renderDefectList();

    },
        undefined, // onProgress handled by manager 
        (err) => {
            console.error("Error loading model", err);
            // Error already handled by manager, but we can add specific logic here if needed
            loadingError.classList.remove('hidden');
            loadingError.textContent = "Could not load 3D model. Please check network.";
        });
}

function updatePartDropdown() {
    partSelect.innerHTML = '<option value="">Select a part...</option>';
    if (!carModel) return;

    carModel.traverse(child => {
        if (child.isMesh) {
            const opt = document.createElement('option');
            opt.value = child.name;
            opt.textContent = child.name || "Unnamed Part";
            partSelect.appendChild(opt);
        }
    });
}

// --- INTERACTION & HOVER ---
window.setMode = function (mode) {
    // CHECK ROLE PERMISSIONS
    if (mode === 'color') {
        const role = window.currentUserRole || 'guest';
        if (role !== 'admin' && role !== 'staff') {
            alert("Only Admin or Staff can customize vehicle colors.");
            return; // Block access
        }
    }

    currentMode = mode;

    // UI Updates
    const buttons = {
        view: document.getElementById('btnView'),
        defect: document.getElementById('btnDefect'),
        color: document.getElementById('btnColor')
    };

    Object.values(buttons).forEach(btn => {
        if (btn) {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-500');
        }
    });

    if (buttons[mode]) {
        buttons[mode].classList.add('bg-blue-600', 'text-white');
        buttons[mode].classList.remove('bg-white', 'text-gray-500');
    }

    modeLabel.textContent = mode.charAt(0).toUpperCase() + mode.slice(1) + " Mode";
    customPanel.classList.toggle('hidden', mode !== 'color');
    container.style.cursor = mode === 'defect' ? 'crosshair' : 'default';
};

// --- AUTO-ROTATE & IDLE LOGIC ---
let lastActivityTime = Date.now();
const IDLE_THRESHOLD = 15000; // 15 seconds

function resetIdleTimer() {
    lastActivityTime = Date.now();
    isAutoRotate = false; // Stop rotating on interaction
    controls.autoRotate = false;
}

// Listen for interactions
window.addEventListener('mousemove', resetIdleTimer);
window.addEventListener('mousedown', resetIdleTimer);
window.addEventListener('keydown', resetIdleTimer);
window.addEventListener('touchstart', resetIdleTimer);
window.addEventListener('click', resetIdleTimer);

// NOTE: toggleAutoRotate and toggleXray removed.

// Mouse Move for Hover Effect
container.addEventListener('mousemove', (e) => {
    // Tooltip position
    tooltip.style.left = (e.clientX + 10) + 'px';
    tooltip.style.top = (e.clientY + 10) + 'px';

    const rect = container.getBoundingClientRect();
    mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);

    if (carModel) {
        const intersects = raycaster.intersectObject(carModel, true);

        if (intersects.length > 0) {
            const object = intersects[0].object;
            const partName = object.name || "Unknown Part";

            // Show Tooltip
            tooltip.textContent = partName;
            tooltip.classList.remove('hidden');
            container.style.cursor = currentMode === 'defect' ? 'crosshair' : 'pointer';

            // Part Highlight (Emission)
            if (activeHighlight !== object) {
                if (activeHighlight) unhighlightPart(activeHighlight);
                highlightPart(object);
                activeHighlight = object;
            }
        } else {
            tooltip.classList.add('hidden');
            container.style.cursor = currentMode === 'defect' ? 'crosshair' : 'default';
            if (activeHighlight) {
                unhighlightPart(activeHighlight);
                activeHighlight = null;
            }
        }
    }
});

let activeHighlight = null;

function highlightPart(mesh) {
    if (mesh.material) {
        if (!mesh.userData.originalEmissive) {
            mesh.userData.originalEmissive = mesh.material.emissive ? mesh.material.emissive.getHex() : 0x000000;
        }
        // Clone material to avoid affecting others
        if (!mesh.userData.isCloned) {
            mesh.material = mesh.material.clone();
            mesh.userData.isCloned = true;
        }
        mesh.material.emissive = new THREE.Color(0x303030);
    }
}

function unhighlightPart(mesh) {
    if (mesh.material) {
        mesh.material.emissive = new THREE.Color(mesh.userData.originalEmissive || 0x000000);
    }
}

container.addEventListener('mousedown', (e) => {
    raycaster.setFromCamera(mouse, camera);

    // 1. Check for Markers (Link to Sidebar)
    const markerIntersects = raycaster.intersectObjects(pinMarkers);
    if (markerIntersects.length > 0) {
        const marker = markerIntersects[0].object;
        const defectId = marker.userData.id;

        const sidebarItem = document.getElementById(`defect-item-${defectId}`);
        if (sidebarItem) {
            sidebarItem.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Visual flash
            sidebarItem.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50');
            setTimeout(() => {
                sidebarItem.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50');
            }, 1000);
        }
        return;
    }

    if (currentMode === 'view') return;

    // 2. Check for Car (Add Defect / Color)
    const intersects = raycaster.intersectObject(carModel, true);

    if (intersects.length > 0) {
        const point = intersects[0].point;
        const object = intersects[0].object;

        if (currentMode === 'defect') {
            addDefect(point, object.name);
        } else if (currentMode === 'color') {
            partSelect.value = object.name;
            updateColorFromMesh(object);
        }
    }
});

// --- DEFECT LOGIC ---
const STATUS_COLORS = {
    damage: 0xef4444, // Red
    fixed: 0x22c55e,  // Green
    initial: 0x3b82f6 // Blue
};

function addDefect(pos, partName) {
    const id = Date.now();
    const defaultStatus = 'damage';

    // Create 3D Marker
    const markerGeo = new THREE.SphereGeometry(0.08, 16, 16);
    const markerMat = new THREE.MeshBasicMaterial({ color: STATUS_COLORS[defaultStatus] });
    const marker = new THREE.Mesh(markerGeo, markerMat);
    marker.position.copy(pos);
    scene.add(marker);

    // Pulse data
    marker.userData = { id, originalScale: 1, time: 0 };
    pinMarkers.push(marker);

    // Save data
    defects.push({
        id: id,
        part: partName,
        pos: pos.clone(),
        status: defaultStatus,
        description: "",
        proposal: "",
        images: []
    });

    renderDefectList();
}

// --- EXPORT FOR QUOTATION ENGINE ---
window.getDefects = function () {
    return defects;
};

function renderDefectList() {
    defectCountEl.textContent = `${defects.length} ISSUES`;

    if (defects.length === 0) {
        defectListEl.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-gray-300">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-crosshairs text-2xl opacity-20"></i>
                </div>
                <p class="text-sm font-medium text-gray-400">No defects pinned</p>
                <p class="text-[10px] text-gray-400">Switch to Pin Mode and click on the car</p>
            </div>`;
        return;
    }

    defectListEl.innerHTML = defects.map((d, i) => {
        // Helper to generate status button classes
        const getBtnClass = (status, activeColor) => {
            return d.status === status
                ? `bg-${activeColor}-500 text-white border-${activeColor}-600`
                : `bg-white text-gray-400 border-gray-200 hover:bg-gray-50`;
        };

        return `
            <div id="defect-item-${d.id}" class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm hover:shadow-md transition group text-xs">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md flex items-center justify-center font-bold text-white shadow-sm" style="background-color: #${new THREE.Color(STATUS_COLORS[d.status]).getHexString()}">${i + 1}</span>
                    <div>
                        <div class="font-bold text-gray-700">${d.part || 'Unknown Part'}</div>
                    </div>
                </div>
                <div class="flex gap-1">
                     <button onclick="inspectDefect(${d.id})" class="text-blue-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition" title="Fly to issue">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="removeDefect(${d.id})" class="text-gray-300 hover:text-red-500 p-1 rounded hover:bg-red-50 transition" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>

            <!-- Status Toggles -->
            <div class="flex gap-1 mb-2">
                <button onclick="updateStatus(${d.id}, 'damage')" class="flex-1 py-1 rounded border text-[10px] font-medium transition ${getBtnClass('damage', 'red')}">
                    Hỏng
                </button>
                <button onclick="updateStatus(${d.id}, 'fixed')" class="flex-1 py-1 rounded border text-[10px] font-medium transition ${getBtnClass('fixed', 'green')}">
                    Đã sửa
                </button>
                <button onclick="updateStatus(${d.id}, 'initial')" class="flex-1 py-1 rounded border text-[10px] font-medium transition ${getBtnClass('initial', 'blue')}">
                    Nguyên bản
                </button>
            </div>

            <!-- Inputs -->
            <div class="space-y-2">
                <div>
                    <input type="text" onchange="updateData(${d.id}, 'description', this.value)" class="w-full border border-gray-200 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500 outline-none placeholder-gray-400" placeholder="Mô tả lỗi..." value="${d.description}">
                </div>
                <div>
                    <input type="text" onchange="updateData(${d.id}, 'proposal', this.value)" class="w-full border border-gray-200 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500 outline-none placeholder-gray-400" placeholder="Đề xuất sửa chữa..." value="${d.proposal}">
                </div>
            </div>

            <!--Actions -->
            <div class="mt-2 flex items-center justify-between border-t border-gray-50 pt-2">
                <button class="text-gray-500 hover:text-blue-600 flex items-center gap-1 transition">
                    <i class="fas fa-camera"></i> <span class="text-[10px]">Thêm ảnh</span>
                </button>
                <span class="text-[9px] text-gray-300">ID: ${d.id}</span>
            </div>
        </div >
            `}).join('');
}

window.updateStatus = function (id, status) {
    const defect = defects.find(d => d.id === id);
    if (!defect) return;

    defect.status = status;

    // Update marker color
    const marker = pinMarkers.find(m => m.userData.id === id);
    if (marker) {
        marker.material.color.setHex(STATUS_COLORS[status]);
    }

    renderDefectList();
    saveDefects();
};

window.updateData = function (id, field, value) {
    const defect = defects.find(d => d.id === id);
    if (defect) {
        defect[field] = value;
        saveDefects();
    }
};

window.inspectDefect = function (id) {
    const defect = defects.find(d => d.id === id);
    if (!defect) return;

    // Smart Focus Camera Animation
    const offset = defect.pos.clone().normalize().multiplyScalar(3);
    // Position camera slightly away from defect along normal
    const camPos = defect.pos.clone().add(new THREE.Vector3(1, 1, 1).normalize().multiplyScalar(2));

    animateCamera(camPos, defect.pos);
};

window.removeDefect = function (id) {
    if (!confirm("Bạn có chắc chắn muốn xóa điểm lỗi này không?")) return;

    defects = defects.filter(d => d.id !== id);

    // Robust removal: Remove ALL markers matching this ID (in case of duplicates)
    let markerRemoved = false;
    // Loop backwards to splice safely
    for (let i = pinMarkers.length - 1; i >= 0; i--) {
        if (pinMarkers[i].userData.id === id) {
            const marker = pinMarkers[i];
            scene.remove(marker);
            if (marker.geometry) marker.geometry.dispose();
            if (marker.material) marker.material.dispose();
            pinMarkers.splice(i, 1);
            markerRemoved = true;
        }
    }

    renderDefectList();
    saveDefects();
}

// --- COLOR & HELPERS ---
function updateColorFromMesh(mesh) {
    if (mesh.material && mesh.material.color) {
        const hex = "#" + mesh.material.color.getHexString();
        colorPicker.value = hex;
        colorHex.textContent = hex.toUpperCase();
    }
}

colorPicker.addEventListener('input', (e) => {
    const hex = e.target.value;
    colorHex.textContent = hex.toUpperCase();
    const partName = partSelect.value;
    if (!partName || !carModel) return;

    const mesh = carModel.getObjectByName(partName);
    if (mesh && mesh.isMesh) {
        if (!mesh.userData.isCloned) {
            mesh.material = mesh.material.clone();
            mesh.userData.isCloned = true;
        }
        mesh.material.color.set(hex);
    }
});

partSelect.addEventListener('change', (e) => {
    const partName = e.target.value;
    if (!partName || !carModel) return;
    const mesh = carModel.getObjectByName(partName);
    if (mesh) {
        updateColorFromMesh(mesh);
        highlightPart(mesh);
        activeHighlight = mesh;
    }
});

// --- EXTERNAL EVENTS (New Vehicle Search System) ---
window.addEventListener('vehicleLoaded', (e) => {
    // Determine 3D model based on description or default
    const modelName = e.detail.model.toLowerCase();
    let file = 'sedan.glb'; // Default

    // Mapping logic matching ACTUAL files in assets/models/
    if (modelName.includes('sedan')) file = 'sedan.glb';
    else if (modelName.includes('suv')) file = 'suv.glb';
    else if (modelName.includes('truck') || modelName.includes('ford ranger')) file = 'Pick-up.glb';
    else if (modelName.includes('hatchback')) file = 'hatchback.glb';
    else if (modelName.includes('mpv')) file = 'MPV.glb';

    loadCar(file);
});

// REMOVED: modelSelect logic (replaced by vehicleLoaded event)
// modelSelect.addEventListener('change', ...);

// --- CAMERA ANIMATION ---
function animateCamera(targetPosition, targetLookAt) {
    const startPos = camera.position.clone();
    const startTarget = controls.target.clone();
    const duration = 1000;
    const startTime = Date.now();

    function update() {
        const now = Date.now();
        const progress = Math.min((now - startTime) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3); // Cubic ease out

        camera.position.lerpVectors(startPos, targetPosition, ease);
        controls.target.lerpVectors(startTarget, targetLookAt, ease);
        controls.update();

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    update();
}

// --- RENDER LOOP ---
function animate() {
    requestAnimationFrame(animate);

    // Auto-rotate Check
    if (!controls.autoRotate && Date.now() - lastActivityTime > IDLE_THRESHOLD) {
        controls.autoRotate = true;
    }

    controls.update();

    // Pulse markers
    const time = Date.now() * 0.005;
    pinMarkers.forEach(m => {
        const s = 1 + Math.sin(time) * 0.2;
        m.scale.set(s, s, s);
    });

    renderer.render(scene, camera);
}

// --- UTILS & LISTENERS ---

// 1. Responsive Canvas
window.addEventListener('resize', onWindowResize, false);

function onWindowResize() {
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

const btnExport = Array.from(document.querySelectorAll('button')).find(
    btn => btn.textContent.trim().includes("Export Inspection Report")
);
// Fallback selector or robust one. The HTML has "Export Inspection Report".

if (btnExport) {
    btnExport.onclick = exportScreenshot;
}

function exportScreenshot() {
    // Render one frame specifically for capturing (preserving high quality)
    renderer.render(scene, camera);
    const dataURL = renderer.domElement.toDataURL('image/png');

    // Create download link
    const link = document.createElement('a');
    link.download = `inspection-report-${Date.now()}.png`;
    link.href = dataURL;
    link.click();
}

// 3. Data Persistence (LocalStorage)
const STORAGE_KEY_PREFIX = 'smart_garage_defects_';

function getStorageKey() {
    return STORAGE_KEY_PREFIX + activeCarFile;
}

function saveDefects() {
    const key = getStorageKey();
    const simpleDefects = defects.map(d => ({
        id: d.id,
        part: d.part,
        pos: { x: d.pos.x, y: d.pos.y, z: d.pos.z },
        status: d.status,
        description: d.description,
        proposal: d.proposal
    }));
    localStorage.setItem(key, JSON.stringify(simpleDefects));
}

function loadDefects() {
    const key = getStorageKey();
    const stored = localStorage.getItem(key);
    if (!stored) return;

    try {
        const storedDefects = JSON.parse(stored);
        storedDefects.forEach(d => {
            const pos = new THREE.Vector3(d.pos.x, d.pos.y, d.pos.z);
            restoreDefect(d.id, pos, d.part, d.status, d.description, d.proposal);
        });
    } catch (e) {
        console.error("Failed to load defects", e);
    }
}

function restoreDefect(id, pos, partName, status, description, proposal) {
    // Recreate Marker
    const markerGeo = new THREE.SphereGeometry(0.08, 16, 16);
    const markerMat = new THREE.MeshBasicMaterial({ color: STATUS_COLORS[status] });
    const marker = new THREE.Mesh(markerGeo, markerMat);
    marker.position.copy(pos);
    scene.add(marker);

    marker.userData = { id, originalScale: 1, time: 0 };
    pinMarkers.push(marker);

    // Add to array
    defects.push({
        id,
        part: partName,
        pos,
        status,
        description: description || "",
        proposal: proposal || "",
        images: []
    });
}

// --- INIT ---
// --- INIT ---
// Default load for the prototype
loadCar('sedan.glb');

animate();

// --- INITIALIZE ---
window.addEventListener('resize', () => {
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
});

// Clean startup
animate();
