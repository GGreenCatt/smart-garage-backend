
// Mock Parts Database for Automation
// Mock Parts Database matching User's 3D Model Names (Vietnamese Slugs)
import { getInventory } from './inventory_utils.js';

function calculateQuote() {
    const defects = window.getDefects ? window.getDefects() : [];
    const quoteBody = document.getElementById('quote-items-body');
    const quoteTotal = document.getElementById('quote-total-price');

    let html = '';
    let subtotal = 0;

    if (defects.length === 0) {
        quoteBody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-400 font-medium">Không tìm thấy lỗi. Xe trong tình trạng tốt.</td></tr>';
        quoteTotal.innerText = '$0.00';
        return;
    }

    // Get latest pricing from inventory
    const inventory = getInventory();

    defects.forEach((d, index) => {
        const partName = (d.part || '').toLowerCase();
        let matchedItem = null;

        // Find matching item in inventory based on slug
        // logic: check if the defect part name contains the inventory slug
        for (const item of inventory) {
            if (item.slug && partName.includes(item.slug.toLowerCase())) {
                matchedItem = item;
                break;
            }
        }

        // Fallback for unknown items
        if (!matchedItem) {
            matchedItem = {
                name: 'Unknown Component (Linh Kiện Khác)',
                price: 0,
                labor: 50,
                status: 'Unknown',
                id: 'N/A'
            };
        }

        const itemTotal = matchedItem.price + matchedItem.labor;
        subtotal += itemTotal;

        // Inventory Status Check
        const stockStatus = matchedItem.qty > 0 ? 'In Stock' : 'Out of Stock';
        const statusColor = matchedItem.qty > 0 ? 'text-green-500' : 'text-red-500';

        html += `
            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center font-bold text-xs border border-red-100">${index + 1}</div>
                        <div>
                            <div class="font-bold text-gray-800 text-sm">${matchedItem.name}</div>
                            <div class="text-[10px] text-gray-400 font-mono">
                                ${d.status.toUpperCase()} • ID: ${d.part || 'N/A'}
                                <span class="${statusColor} ml-2 font-bold">${stockStatus}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 font-mono text-sm text-gray-600">$${matchedItem.price.toFixed(2)}</td>
                <td class="px-6 py-4 font-mono text-sm text-gray-600">$${matchedItem.labor.toFixed(2)}</td>
                <td class="px-6 py-4 font-mono font-bold text-gray-800 text-right">$${itemTotal.toFixed(2)}</td>
            </tr>
        `;
    });

    const tax = subtotal * 0.1; // 10% VAT
    const finalTotal = subtotal + tax;

    quoteBody.innerHTML = html;

    // Update Summary
    document.getElementById('quote-subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('quote-tax').innerText = `$${tax.toFixed(2)}`;
    quoteTotal.innerText = `$${finalTotal.toFixed(2)}`;

    // Open Modal (UI Logic remains)
    const modal = document.getElementById('quoteModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }
}

// Expose to window for HTML buttons
window.calculateQuote = calculateQuote;

function closeQuoteModal() {
    const modal = document.getElementById('quoteModal');
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.remove('scale-100');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 300);
}
