
// inventory_utils.js - Helper functions for Inventory Management

// Simulated Shared Database (In a real app, this would be an API call)
export const INVENTORY_DB = [
    { id: 101, uuid: 'PART-BMP-F-001', name: 'Front Bumper (Cản Trước)', sku: 'BMP-F-2022', category: 'Body', qty: 12, price: 350.00, labor: 50.00, slug: 'Can-Truoc' },
    { id: 102, uuid: 'PART-BMP-R-002', name: 'Rear Bumper (Cản Sau)', sku: 'BMP-R-2022', category: 'Body', qty: 8, price: 350.00, labor: 50.00, slug: 'Can-Sau' },
    { id: 103, uuid: 'PART-HL-L-003', name: 'Headlight Assembly (Đèn Pha)', sku: 'LGT-HL-LED', category: 'Electrical', qty: 4, price: 200.00, labor: 30.00, slug: 'Den-Pha' },
    { id: 104, uuid: 'PART-DR-FL-004', name: 'Front Door (Cửa Trước)', sku: 'BDY-DR-FL', category: 'Body', qty: 2, price: 500.00, labor: 100.00, slug: 'Cua-Truoc' },
    { id: 105, uuid: 'PART-DR-RL-005', name: 'Rear Door (Cửa Sau)', sku: 'BDY-DR-RL', category: 'Body', qty: 3, price: 500.00, labor: 100.00, slug: 'Cua-Sau' },
    { id: 106, uuid: 'PART-WH-ALL-006', name: 'Alloy Wheel (Bánh Xe)', sku: 'WHL-18-SLV', category: 'Chassis', qty: 24, price: 150.00, labor: 20.00, slug: 'Banh-Xe' },
    { id: 107, uuid: 'PART-WND-F-007', name: 'Windshield (Kính Chắn Gió)', sku: 'GLS-F-2022', category: 'Glass', qty: 1, price: 300.00, labor: 80.00, slug: 'Kinh-Chan-Gio' },
    { id: 108, uuid: 'PART-HD-008', name: 'Hood / Bonnet (Nắp Capo)', sku: 'BDY-HD-2022', category: 'Body', qty: 5, price: 400.00, labor: 60.00, slug: 'Nap-Capo' },
    { id: 109, uuid: 'PART-TRK-009', name: 'Trunk Lid (Cốp Sau)', sku: 'BDY-TRK-2022', category: 'Body', qty: 3, price: 380.00, labor: 60.00, slug: 'Cop-Sau' },
    { id: 110, uuid: 'PART-MIR-L-010', name: 'Side Mirror (Gương)', sku: 'MIR-EL-2022', category: 'Electrical', qty: 15, price: 80.00, labor: 15.00, slug: 'Guong' },
    { id: 111, uuid: 'PART-RF-011', name: 'Roof Panel (Nóc Xe)', sku: 'BDY-RF-2022', category: 'Body', qty: 0, price: 600.00, labor: 150.00, slug: 'Noc-Xe' },
    { id: 112, uuid: 'PART-OIL-012', name: 'Engine Oil 5W-30', sku: 'LUB-OIL-syn', category: 'Consumables', qty: 50, price: 45.00, labor: 0.00, slug: 'Dau-Nhot' },
    { id: 113, uuid: 'PART-FLT-OIL-013', name: 'Oil Filter', sku: 'FLT-OIL-GEN', category: 'Consumables', qty: 20, price: 12.00, labor: 10.00, slug: 'Loc-Dau' }
];

export function getInventory() {
    // In a real app, this might fetch from localStorage or an API
    const stored = localStorage.getItem('sg_inventory_data');
    if (stored) {
        return JSON.parse(stored);
    }
    return INVENTORY_DB;
}

export function saveInventory(data) {
    localStorage.setItem('sg_inventory_data', JSON.stringify(data));
}

export const LOW_STOCK_THRESHOLD = 5;

/**
 * Parses a CSV string into an array of objects.
 * Assumes first row is header.
 * @param {string} csvText 
 * @returns {Array} Array of item objects
 */
export function parseCSV(csvText) {
    const lines = csvText.split('\n');
    const headers = lines[0].split(',').map(h => h.trim());
    const residents = [];

    for (let i = 1; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;

        const values = line.split(',');
        const item = {};

        headers.forEach((header, index) => {
            let value = values[index] ? values[index].trim() : '';
            // Basic type conversion
            if (header === 'qty' || header === 'price') {
                value = parseFloat(value) || 0;
            }
            item[header] = value;
        });

        // Add auto-generated fields if missing
        if (!item.id) item.id = Date.now() + Math.random();
        if (!item.status) item.status = (item.qty > 0) ? 'In Stock' : 'Out of Stock';

        residents.push(item);
    }
    return residents;
}

/**
 * Generates HTML for a single inventory table row.
 * @param {Object} item 
 * @param {number} index 
 * @returns {string} HTML string
 */
export function renderInventoryRow(item, index) {
    const isLowStock = item.qty <= LOW_STOCK_THRESHOLD && item.qty > 0;
    const isOutOfStock = item.qty === 0;

    let statusBadge = '';
    if (isOutOfStock) {
        statusBadge = `<span class="badge badge-danger">Out of Stock</span>`;
    } else if (isLowStock) {
        statusBadge = `<span class="badge badge-warning">Low Stock</span>`;
    } else {
        statusBadge = `<span class="badge badge-success">In Stock</span>`;
    }

    return `
    <tr class="hover:bg-gray-50 transition border-b border-gray-100 last:border-0">
        <td class="px-6 py-4 w-10">
            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        </td>
        <td class="px-6 py-4">
            <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-lg">
                    <i class="fas fa-box"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">${item.name}</div>
                    <div class="text-xs text-gray-500">UUID: ${item.uuid || 'N/A'}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 font-mono">${item.sku}</td>
        <td class="px-6 py-4 text-sm text-gray-500">${item.category || 'General'}</td>
        <td class="px-6 py-4 text-sm text-gray-900 font-bold">${item.qty}</td>
        <td class="px-6 py-4 text-sm text-gray-900">$${parseFloat(item.price).toFixed(2)}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            ${statusBadge}
        </td>
        <td class="px-6 py-4 text-right text-sm font-medium">
            <button onclick="editItem(${item.id})" class="text-indigo-600 hover:text-indigo-900 mr-2"><i class="fas fa-edit"></i></button>
            <button onclick="deleteItem(${item.id})" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
    `;
}
