<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');
        // Filter out admin role
        $roles = Role::where('slug', '!=', 'admin')->withCount('users')->latest()->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::authorize('manage_settings');
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure slug is unique
        if (Role::where('slug', $validated['slug'])->exists()) {
             $validated['slug'] = $validated['slug'] . '-' . Str::random(4);
        }

        Role::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully');
    }

    public function edit(Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->with('error', 'Không thể chỉnh sửa quyền của Chủ Garage (Admin)');
        }
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->with('error', 'Không thể chỉnh sửa quyền của Chủ Garage (Admin)');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        if ($role->name !== $validated['name']) {
             $validated['slug'] = Str::slug($validated['name']);
        }

        $role->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['admin', 'staff', 'customer'])) {
            return back()->with('error', 'Cannot delete system roles');
        }
        
        $role->delete();
        return back()->with('success', 'Role deleted successfully');
    }
}
