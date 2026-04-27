<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorizeManageRoles();

        $roles = Role::where('slug', '!=', 'admin')
            ->withCount('users')
            ->latest()
            ->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorizeManageRoles();

        return view('admin.roles.create', [
            'permissionGroups' => Role::permissionGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManageRoles();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(Role::permissions())],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['permissions'] = array_values(array_unique($validated['permissions'] ?? []));

        if (Role::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $validated['slug'] . '-' . Str::random(4);
        }

        Role::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Tạo chức vụ thành công');
    }

    public function edit(Role $role)
    {
        $this->authorizeManageRoles();

        if ($role->slug === 'admin') {
            return back()->with('error', 'Không thể chỉnh sửa quyền của Chủ Garage (Admin)');
        }

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => Role::permissionGroups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeManageRoles();

        if ($role->slug === 'admin') {
            return back()->with('error', 'Không thể chỉnh sửa quyền của Chủ Garage (Admin)');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(Role::permissions())],
        ]);

        $validated['permissions'] = array_values(array_unique($validated['permissions'] ?? []));

        if ($role->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $role->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật chức vụ thành công');
    }

    public function destroy(Role $role)
    {
        $this->authorizeManageRoles();

        if (in_array($role->slug, ['admin', 'staff', 'customer'], true)) {
            return back()->with('error', 'Không thể xóa chức vụ hệ thống');
        }

        $role->delete();

        return back()->with('success', 'Đã xóa chức vụ thành công');
    }

    private function authorizeManageRoles(): void
    {
        abort_unless(Gate::any(['manage_roles', 'manage_settings']), 403);
    }
}
