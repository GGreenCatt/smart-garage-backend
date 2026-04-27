<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RepairOrder;
use App\Models\RepairTask;
use App\Models\Role;
use App\Models\SosRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        Gate::authorize('view_staff');

        $staff = User::with('assignedRole')
            ->where(function ($query) {
                $query->whereHas('assignedRole', function ($roleQuery) {
                    $roleQuery->whereNotIn('slug', ['admin', 'customer']);
                })->orWhereIn('role', ['staff', 'manager', 'technician']);
            })
            ->latest()
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        Gate::authorize('manage_staff');

        return view('admin.staff.create', [
            'roles' => $this->staffRoles(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_staff');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'password' => 'required|min:6',
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->whereNotIn('slug', ['admin', 'customer']))],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'role' => in_array($role->slug, ['staff', 'manager', 'technician'], true) ? $role->slug : 'staff',
            'permissions' => [],
            'status' => 'active',
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_STAFF',
            'details' => "Tạo tài khoản nhân viên {$user->name} (#{$user->id})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Tạo nhân viên thành công');
    }

    public function show(Request $request, User $staff)
    {
        Gate::authorize('view_staff');
        $this->abortIfNotStaffUser($staff);

        $query = ActivityLog::where('user_id', $staff->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $actions = ActivityLog::where('user_id', $staff->id)->distinct()->pluck('action');

        return view('admin.staff.show', compact('staff', 'logs', 'actions'));
    }

    public function edit(User $staff)
    {
        Gate::authorize('manage_staff');
        $this->abortIfNotStaffUser($staff);

        return view('admin.staff.edit', [
            'staff' => $staff,
            'roles' => $this->staffRoles(),
        ]);
    }

    public function update(Request $request, User $staff)
    {
        Gate::authorize('manage_staff');
        $this->abortIfNotStaffUser($staff);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($staff->id)],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->whereNotIn('slug', ['admin', 'customer']))],
            'password' => 'nullable|min:6',
            'tags' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,banned',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role_id' => $role->id,
            'role' => in_array($role->slug, ['staff', 'manager', 'technician'], true) ? $role->slug : 'staff',
            'status' => $validated['status'] ?? $staff->status ?? 'active',
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (array_key_exists('tags', $validated)) {
            $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags'] ?? ''))));
        }

        $staff->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_STAFF',
            'details' => "Cập nhật tài khoản nhân viên {$staff->name} (#{$staff->id})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Cập nhật nhân viên thành công');
    }

    public function destroy(User $staff)
    {
        Gate::authorize('manage_staff');
        $this->abortIfNotStaffUser($staff);

        if ($staff->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa chính tài khoản của bạn');
        }

        $hasOperationalHistory = RepairOrder::where('advisor_id', $staff->id)->exists()
            || RepairTask::where('mechanic_id', $staff->id)->exists()
            || SosRequest::where('assigned_staff_id', $staff->id)->exists();

        if ($hasOperationalHistory) {
            $staff->update(['status' => 'inactive']);

            return back()->with('warning', 'Nhân viên đã có lịch sử vận hành nên không xóa dữ liệu. Tài khoản đã được chuyển sang ngừng hoạt động.');
        }

        $name = $staff->name;
        $id = $staff->id;
        $staff->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_STAFF',
            'details' => "Xóa tài khoản nhân viên {$name} (#{$id})",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Đã xóa nhân viên thành công');
    }

    public function logs(Request $request)
    {
        Gate::authorize('view_reports');

        $logs = ActivityLog::with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->q);
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('action', 'like', "%{$keyword}%")
                        ->orWhere('details', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"));
                });
            })
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->action))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin.staff.logs', compact('logs', 'actions'));
    }

    private function staffRoles()
    {
        return Role::whereNotIn('slug', ['admin', 'customer'])
            ->orderBy('name')
            ->get();
    }

    private function abortIfNotStaffUser(User $user): void
    {
        $roleSlug = $user->assignedRole?->slug;
        abort_if(in_array($user->role, ['admin', 'customer'], true) || in_array($roleSlug, ['admin', 'customer'], true), 404);
    }
}
