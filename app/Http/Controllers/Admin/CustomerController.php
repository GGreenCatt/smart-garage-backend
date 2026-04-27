<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RepairOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_customers');

        $userQuery = User::query()
            ->where(function ($query) {
                $query->where('role', 'customer')
                    ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
            })
            ->select('id', 'name', 'email', 'phone', 'status', 'created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $registeredUsers = $userQuery->latest()->get()->map(function (User $user) {
            $user->vehicles_count = Vehicle::where('user_id', $user->id)
                ->when($user->phone, fn ($query) => $query->orWhere('owner_phone', $user->phone))
                ->count();
            $user->profile_id = (string) $user->id;
            $user->is_guest = false;

            return $user;
        });

        $registeredPhones = $registeredUsers->pluck('phone')->filter()->unique()->values();
        $vehicleQuery = Vehicle::query()
            ->whereNull('user_id')
            ->whereNotNull('owner_phone')
            ->whereNotIn('owner_phone', $registeredPhones)
            ->selectRaw('MAX(id) as id, owner_name as name, owner_phone as phone, MAX(created_at) as created_at, COUNT(*) as vehicles_count')
            ->groupBy('owner_phone', 'owner_name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $vehicleQuery->where(function ($query) use ($search) {
                $query->where('owner_name', 'like', "%{$search}%")
                    ->orWhere('owner_phone', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        $walkInCustomers = $vehicleQuery->get()->map(function ($vehicle) {
            $vehicle->email = null;
            $vehicle->status = null;
            $vehicle->is_guest = true;
            $vehicle->profile_id = 'guest-'.$vehicle->id;

            return $vehicle;
        });

        $allCustomers = $registeredUsers
            ->concat($walkInCustomers)
            ->sortByDesc('created_at')
            ->values();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 10;
        $customers = new LengthAwarePaginator(
            $allCustomers->slice(($page - 1) * $perPage, $perPage)->values(),
            $allCustomers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total' => $allCustomers->count(),
            'registered' => $registeredUsers->count(),
            'walk_in' => $walkInCustomers->count(),
            'new_this_month' => $allCustomers->where('created_at', '>=', now()->startOfMonth())->count(),
            'loyalty' => $allCustomers->where('vehicles_count', '>=', 3)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        Gate::authorize('manage_customers');

        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'tags' => 'nullable|string',
        ]);

        $customerRole = Role::where('slug', 'customer')->first();
        $customer = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'role_id' => $customerRole?->id,
            'status' => 'active',
            'tags' => $this->parseTags($validated['tags'] ?? null),
        ]);

        Vehicle::whereNull('user_id')
            ->where('owner_phone', $customer->phone)
            ->update([
                'user_id' => $customer->id,
                'owner_name' => $customer->name,
            ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_CUSTOMER',
            'details' => "Tạo tài khoản khách hàng {$customer->name} (#{$customer->id})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Tạo khách hàng thành công');
    }

    public function show(string $customer)
    {
        Gate::authorize('manage_customers');

        [$customerProfile, $vehicles, $isGuest] = $this->resolveCustomerProfile($customer);
        $orders = $vehicles->flatMap->repairOrders->sortByDesc('created_at')->values();
        $totalSpent = $orders
            ->where('payment_status', 'paid')
            ->sum(fn ($order) => (float) ($order->total_amount ?? 0));

        return view('admin.customers.show', [
            'customer' => $customerProfile,
            'vehicles' => $vehicles,
            'orders' => $orders,
            'totalSpent' => $totalSpent,
            'isGuest' => $isGuest,
        ]);
    }

    public function edit(User $customer)
    {
        Gate::authorize('manage_customers');
        $this->abortIfNotCustomer($customer);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        Gate::authorize('manage_customers');
        $this->abortIfNotCustomer($customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($customer->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($customer->id)],
            'password' => 'nullable|min:6',
            'tags' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,banned',
        ]);

        $oldPhone = $customer->phone;
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'status' => $validated['status'] ?? $customer->status ?? 'active',
            'tags' => $this->parseTags($validated['tags'] ?? null),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $customer->update($data);

        Vehicle::where('user_id', $customer->id)
            ->orWhere(function ($query) use ($oldPhone) {
                $query->whereNull('user_id')->where('owner_phone', $oldPhone);
            })
            ->update([
                'owner_phone' => $customer->phone,
                'owner_name' => $customer->name,
                'user_id' => $customer->id,
            ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_CUSTOMER',
            'details' => "Cập nhật khách hàng {$customer->name} (#{$customer->id})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Cập nhật khách hàng thành công');
    }

    public function getVehiclesJson($id)
    {
        Gate::authorize('manage_customers');
        $customer = User::findOrFail($id);
        $this->abortIfNotCustomer($customer);

        $vehicles = Vehicle::where('user_id', $customer->id)
            ->when($customer->phone, fn ($query) => $query->orWhere('owner_phone', $customer->phone))
            ->get();

        return response()->json($vehicles);
    }

    private function resolveCustomerProfile(string $id): array
    {
        if (str_starts_with($id, 'guest-')) {
            $vehicle = Vehicle::findOrFail((int) str_replace('guest-', '', $id));
            $vehicles = Vehicle::with('repairOrders.vehicle')
                ->whereNull('user_id')
                ->where('owner_phone', $vehicle->owner_phone)
                ->latest()
                ->get();

            $guest = new User([
                'name' => $vehicle->owner_name ?: 'Khách vãng lai',
                'phone' => $vehicle->owner_phone,
                'email' => null,
                'status' => null,
            ]);
            $guest->id = $id;
            $guest->created_at = $vehicle->created_at;

            return [$guest, $vehicles, true];
        }

        $customer = User::findOrFail($id);
        $this->abortIfNotCustomer($customer);

        $vehicles = Vehicle::with('repairOrders.vehicle')
            ->where('user_id', $customer->id)
            ->when($customer->phone, fn ($query) => $query->orWhere('owner_phone', $customer->phone))
            ->latest()
            ->get();

        return [$customer, $vehicles, false];
    }

    private function abortIfNotCustomer(User $user): void
    {
        abort_if(
            $user->role !== 'customer' && $user->assignedRole?->slug !== 'customer',
            404
        );
    }

    private function parseTags(?string $tags): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $tags ?? ''))));
    }
}
