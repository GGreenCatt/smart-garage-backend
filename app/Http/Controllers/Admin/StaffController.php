<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Gate;

class StaffController extends Controller
{
    public function index()
    {
        Gate::authorize('view_staff');
        
        $staff = User::whereHas('assignedRole', function($q){
            $q->whereNotIn('slug', ['admin', 'customer']);
        })->orWhereIn('role', ['staff', 'manager', 'technician'])->latest()->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        Gate::authorize('manage_staff');
        $roles = \App\Models\Role::where('slug', '!=', 'admin')->get();
        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_staff');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
            // 'permissions' is now handled by Role
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'role' => 'staff', // Legacy fallback
            'permissions' => [] // Legacy fallback
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_STAFF',
            'details' => "Created user {$user->name} ({$user->id})",
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff created successfully');
    }

    public function show(Request $request, User $staff)
    {
        Gate::authorize('view_staff');
        
        $query = ActivityLog::where('user_id', $staff->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        
        // Get unique actions for filter dropdown
        $actions = ActivityLog::where('user_id', $staff->id)->distinct()->pluck('action');

        return view('admin.staff.show', compact('staff', 'logs', 'actions'));
    }

    public function edit(User $staff)
    {
        Gate::authorize('manage_staff');
        $roles = \App\Models\Role::where('slug', '!=', 'admin')->get();
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, User $staff)
    {
        Gate::authorize('manage_staff');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'phone' => ['nullable', 'string', Rule::unique('users', 'phone')->ignore($staff->id)],
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|min:6',
            'tags' => 'nullable|string' // Input as comma separated string
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
            // 'role' => 'staff', // Keep existing legacy role or update logic if needed
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Process tags
        if (isset($validated['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $data['tags'] = array_values($tags);
        }

        $staff->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_STAFF',
            'details' => "Updated user {$staff->name} ({$staff->id})",
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff updated successfully');
    }

    public function destroy(User $staff)
    {
        Gate::authorize('manage_staff');
        
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself');
        }

        $staff->delete(); // Soft delete if trait used, or hard delete
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_STAFF',
            'details' => "Deleted user {$staff->name} ({$staff->id})",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Staff deleted successfully');
    }

    public function logs()
    {
        Gate::authorize('view_reports');
        $logs = ActivityLog::with('user')->latest()->paginate(20);
        return view('admin.staff.logs', compact('logs'));
    }
}
