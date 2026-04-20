<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Gate;

class PromotionController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_promotions'); // Or manage_customers
        $promotions = Promotion::latest()->paginate(10);
        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        Gate::authorize('manage_promotions');
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_promotions');
        $validated = $request->validate([
            'code' => 'required|string|unique:promotions,code|uppercase',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'customer_phone' => 'nullable|string|exists:users,phone', // Helper to find ID
        ]);

        $data = $validated;
        
        // Resolve Customer ID from Phone if provided
        if (!empty($request->customer_phone)) {
            $user = User::where('phone', $request->customer_phone)->first();
            if ($user) $data['customer_id'] = $user->id;
        }
        unset($data['customer_phone']);

        $data['is_active'] = $request->has('is_active');

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created successfully');
    }

    public function edit(Promotion $promotion)
    {
        Gate::authorize('manage_promotions');
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        Gate::authorize('manage_promotions');
        $validated = $request->validate([
            'code' => ['required', 'string', 'uppercase', Rule::unique('promotions')->ignore($promotion->id)],
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'customer_phone' => 'nullable|string|exists:users,phone',
        ]);

        $data = $validated;

         if (!empty($request->customer_phone)) {
            $user = User::where('phone', $request->customer_phone)->first();
            if ($user) $data['customer_id'] = $user->id;
        } else {
            $data['customer_id'] = null; // Reset if empty
        }
        unset($data['customer_phone']);

        $data['is_active'] = $request->has('is_active');

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated successfully');
    }

    public function destroy(Promotion $promotion)
    {
        Gate::authorize('manage_promotions');
        $promotion->delete();
        return back()->with('success', 'Promotion deleted successfully');
    }
}
