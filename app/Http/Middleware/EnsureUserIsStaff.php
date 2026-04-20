<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simple role check (assuming 'role' column or method exists)
        // For prototyping, we can assume authenticated users are valid if they hit this, 
        // OR check a specific column if migration has it.
        // Let's assume a 'role' column on User table from standard practice.

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        // Check 'role' column OR 'assignedRole' relation
        $role = $user->role;
        $roleSlug = $user->assignedRole ? $user->assignedRole->slug : '';
        
        $allowed = ['staff', 'admin', 'manager', 'technician'];
        
        if (!in_array($role, $allowed) && !in_array($roleSlug, $allowed)) {
            abort(403, 'Unauthorized action. Staff access required.');
        }

        return $next($request);
    }
}
