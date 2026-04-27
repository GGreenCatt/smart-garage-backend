<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view_inventory');

        $query = Part::with('supplier');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status') && $request->stock_status !== 'all') {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'min_stock');
            } elseif ($request->stock_status === 'warning') {
                $query->whereColumn('stock_quantity', '<=', 'safety_stock')
                    ->whereColumn('stock_quantity', '>', 'min_stock');
            } elseif ($request->stock_status === 'ok') {
                $query->whereColumn('stock_quantity', '>', 'safety_stock');
            }
        }

        $parts = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();
        $totalValue = Part::sum(DB::raw('stock_quantity * purchase_price'));
        $lowStockCount = Part::whereColumn('stock_quantity', '<=', 'min_stock')->count();
        $safetyStockCount = Part::whereColumn('stock_quantity', '<=', 'safety_stock')
            ->whereColumn('stock_quantity', '>', 'min_stock')
            ->count();
        $healthyStockCount = Part::whereColumn('stock_quantity', '>', 'safety_stock')->count();

        return view('admin.inventory.index', compact('parts', 'suppliers', 'totalValue', 'lowStockCount', 'safetyStockCount', 'healthyStockCount'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_inventory');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:100', Rule::unique('parts', 'sku')],
            'category' => 'required|string|max:150',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'safety_stock' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $part = DB::transaction(function () use ($validated, $request) {
            $part = Part::create($validated);

            if ($part->stock_quantity > 0) {
                InventoryTransaction::create([
                    'part_id' => $part->id,
                    'type' => 'in',
                    'quantity' => $part->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'INITIAL_STOCK',
                    'note' => 'Tồn đầu khi tạo vật tư',
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'CREATE_PART',
                'details' => "Tạo vật tư {$part->name} ({$part->sku})",
                'ip_address' => $request->ip(),
            ]);

            return $part;
        });

        return redirect()->route('admin.inventory.index')->with('success', "Đã thêm vật tư {$part->name}");
    }

    public function updateStock(Request $request, $id)
    {
        Gate::authorize('manage_inventory');

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($id, $validated, $request) {
            $part = Part::lockForUpdate()->findOrFail($id);

            if ($validated['type'] === 'out' && $part->stock_quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Tồn kho không đủ để xuất',
                ]);
            }

            InventoryTransaction::create([
                'part_id' => $part->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'user_id' => auth()->id(),
                'reference' => 'MANUAL_ADJ',
                'note' => $validated['note'] ?? null,
            ]);

            $validated['type'] === 'in'
                ? $part->increment('stock_quantity', $validated['quantity'])
                : $part->decrement('stock_quantity', $validated['quantity']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'UPDATE_STOCK',
                'details' => ($validated['type'] === 'in' ? 'Nhập kho ' : 'Xuất kho ')."{$validated['quantity']} {$part->name} ({$part->sku})",
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Đã cập nhật tồn kho');
    }

    public function transactions(Request $request)
    {
        Gate::authorize('view_inventory');

        $transactions = InventoryTransaction::with(['part', 'user'])
            ->when($request->filled('type') && $request->type !== 'all', fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('reference', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('part', fn ($partQuery) => $partQuery->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.transactions', compact('transactions'));
    }
}
