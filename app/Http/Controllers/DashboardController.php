<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Handle the global dashboard redirection logic.
     * 
     * Priorities:
     * 1. Admin (Role 'admin' or ID 1) -> Admin Dashboard
     * 2. Staff/Technician (Role 'staff','manager','technician' or IDs 2,3,5) -> Staff Dashboard
     * 3. Customer -> Customer Dashboard
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // --- 1. DATA EXTRACTION ---
        // Normalize role string
        $role = strtolower(trim($user->role ?? ''));
        // Get role_id directly from DB column
        $roleId = $user->role_id;
        // Get slug from relation if available
        $roleSlug = $user->assignedRole ? strtolower(trim($user->assignedRole->slug)) : '';

        // Debug Log
        Log::info("DashboardController: UserID:{$user->id} | RoleCol:{$role} | RoleID:{$roleId} | Slug:{$roleSlug}");

        // --- 2. ADMIN CHECK ---
        // DB: Admin role_id is 1
        if ($role === 'admin' || $roleId === 1 || $roleSlug === 'admin') {
            Log::info("DashboardController: Redirecting to Admin Dashboard");
            return redirect()->route('admin.dashboard');
        }

        // --- 3. STAFF / TECHNICIAN CHECK ---
        // DB: Manager(2), Staff(3), Technician(5)
        $staffIds = [2, 3, 5]; 
        $staffRoles = ['staff', 'manager', 'technician'];

        if (in_array($role, $staffRoles) || in_array($roleId, $staffIds) || in_array($roleSlug, $staffRoles)) {
            Log::info("DashboardController: Redirecting to Staff Dashboard");
            return redirect()->route('staff.dashboard');
        }

        // --- 4. FALLBACK: CUSTOMER ---
        Log::info("DashboardController: Fallback to Customer Dashboard");
        return redirect()->route('customer.dashboard');
    }
}
