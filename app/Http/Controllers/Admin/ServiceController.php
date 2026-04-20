<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_settings');
        
        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $services = $query->latest()->paginate(15);
        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_settings');
        
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|unique:services,code',
            'category' => 'required|in:repair,maintenance,diagnosis',
            'base_price' => 'numeric|min:0',
            'estimated_duration' => 'integer|min:1',
            'description' => 'nullable|string'
        ]);

        Service::create($validated);
        
        return back()->with('success', 'Service added successfully');
    }

    public function update(Request $request, Service $service)
    {
        Gate::authorize('manage_settings');
        
        $validated = $request->validate([
            'name' => 'required|string',
            'base_price' => 'numeric|min:0',
            'estimated_duration' => 'integer|min:1',
        ]);

        $service->update($validated);
        return back()->with('success', 'Service updated successfully');
    }

    public function destroy(Service $service)
    {
        Gate::authorize('manage_settings');
        $service->delete();
        return back()->with('success', 'Service deleted successfully');
    }
}
