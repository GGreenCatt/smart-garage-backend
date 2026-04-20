<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\RepairOrder;

use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_customers');

        // 1. Get Registered Customers
        $userQuery = User::where('role', 'customer')
            ->select('id', 'name', 'email', 'phone', 'created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $userQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $registeredUsers = $userQuery->latest()->get();
        
        // Calculate vehicle count by Phone or User ID for Registered Users
        $registeredUsers->transform(function($u) {
            $u->vehicles_count = \App\Models\Vehicle::where('user_id', $u->id)
                ->orWhere('owner_phone', $u->phone)
                ->count();
            return $u;
        });
        $registeredPhones = $registeredUsers->pluck('phone')->filter()->toArray();

        // 2. Get Walk-in Customers (Vehicles without User ID)
        // Group by Phone to avoid duplicates
        // Exclude phones that are already in registered users list
        $vehicleQuery = \App\Models\Vehicle::whereNull('user_id')
            ->whereNotIn('owner_phone', $registeredPhones)
            ->selectRaw('MAX(id) as id, owner_name as name, owner_phone as phone, MAX(created_at) as created_at, COUNT(*) as vehicles_count')
            ->groupBy('owner_phone', 'owner_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $vehicleQuery->where(function($q) use ($search) {
                $q->where('owner_name', 'like', "%{$search}%")
                  ->orWhere('owner_phone', 'like', "%{$search}%");
            });
        }
        $walkInCustomers = $vehicleQuery->get()->map(function($v) {
            // Transform to match User structure for view
            $v->email = null; // Walk-ins don't have email in this logic
            $v->is_guest = true;
            return $v;
        });

        // 3. Merge Collection
        $allCustomers = $registeredUsers->concat($walkInCustomers);

        // 4. Manual Pagination
        $page = $request->input('page', 1);
        $perPage = 10;
        $slicedDetails = $allCustomers->slice(($page - 1) * $perPage, $perPage)->values();
        
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedDetails, 
            $allCustomers->count(), 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 5. Calculate Stats for Dashboard
        $stats = [
            'total' => $allCustomers->count(),
            'active' => $registeredUsers->count(),
            'new_this_month' => $allCustomers->where('created_at', '>=', now()->startOfMonth())->count(),
            'loyalty' => $allCustomers->where('vehicles_count', '>=', 3)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show($id)
    {
        $customer = User::findOrFail($id);
        
        // Load vehicles matching User ID OR Phone
        // We use a custom query instead of the relationship for the view
        $vehicles = \App\Models\Vehicle::where('user_id', $customer->id)
            ->orWhere('owner_phone', $customer->phone)
            ->with('repairOrders')
            ->latest()
            ->get();
            
        // Manually attach to customer object for view compatibility
        $customer->setRelation('vehicles', $vehicles);

        $totalSpent = 0; // functional placeholder
        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }

    public function edit(User $customer)
    {
        Gate::authorize('manage_customers');
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        Gate::authorize('manage_customers');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($customer->id)],
            'phone' => ['required', 'string', Rule::unique('users', 'phone')->ignore($customer->id)],
            'password' => 'nullable|min:6',
            'tags' => 'nullable|string'
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        if (isset($validated['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $data['tags'] = array_values($tags);
        }

        $customer->update($data);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_CUSTOMER',
            'details' => "Updated customer {$customer->name} ({$customer->id})",
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully');
    }

    public function getVehiclesJson($id)
    {
        Gate::authorize('manage_customers');
        $customer = User::findOrFail($id);
        // Also update JSON API for consistency
         $vehicles = \App\Models\Vehicle::where('user_id', $customer->id)
            ->orWhere('owner_phone', $customer->phone)
            ->get();
        return response()->json($vehicles);
    }
}
