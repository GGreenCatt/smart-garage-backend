<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\InventoryTransaction;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view_inventory');
        $query = Part::with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        $parts = $query->latest()->paginate(15);
        $suppliers = Supplier::all(); // For quick add modal

        $totalValue = Part::sum(\DB::raw('stock_quantity * purchase_price'));
        $lowStockCount = Part::whereColumn('stock_quantity', '<=', 'min_stock')->count();
        $safetyStockCount = Part::whereColumn('stock_quantity', '<=', 'safety_stock')
                                ->whereColumn('stock_quantity', '>', 'min_stock')
                                ->count();

        return view('admin.inventory.index', compact('parts', 'suppliers', 'totalValue', 'lowStockCount', 'safetyStockCount'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_inventory');
        $validated = $request->validate([
            'name' => 'required|string',
            'sku' => 'required|unique:parts,sku',
            'category' => 'required',
            'purchase_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
            'safety_stock' => 'integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        $part = Part::create($validated);
        
        // Log initial stock as a transaction if > 0
        if ($part->stock_quantity > 0) {
            InventoryTransaction::create([
                'part_id' => $part->id,
                'type' => 'in',
                'quantity' => $part->stock_quantity,
                'user_id' => auth()->id(),
                'reference' => 'INITIAL_STOCK',
                'note' => 'Initial inventory setup'
            ]);
        }

        return back()->with('success', 'Part added successfully');
    }

    public function updateStock(Request $request, $id)
    {
        $part = Part::findOrFail($id);
        
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        if ($validated['type'] == 'out' && $part->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock');
        }

        // Create transaction
        InventoryTransaction::create([
            'part_id' => $part->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'user_id' => auth()->id(),
            'reference' => 'MANUAL_ADJ',
            'note' => $validated['note']
        ]);

        // Update part stock
        if ($validated['type'] == 'in') {
            $part->increment('stock_quantity', $validated['quantity']);
        } else {
            $part->decrement('stock_quantity', $validated['quantity']);
        }

        return back()->with('success', 'Stock updated successfully');
    }

    public function transactions()
    {
        $transactions = InventoryTransaction::with(['part', 'user'])->latest()->paginate(20);
        return view('admin.inventory.transactions', compact('transactions'));
    }
}
