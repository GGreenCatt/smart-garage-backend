<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Customers (Guests) might generally see the landing page, 
            // but if this middleware protects specific customer dashboard routes:
            return redirect()->route('login');
        }

        $user = auth()->user();
        if ($user->role !== 'customer') {
             // For now, allow admin/staff to view customer view or strict?
             // Let's keep it strict or allow 'admin' to debug.
             if($user->role !== 'admin') {
                  abort(403, 'Unauthorized action. Customer access only.');
             }
        }

        return $next($request);
    }
}
