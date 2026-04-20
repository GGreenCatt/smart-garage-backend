<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings'); // Temporarily use manage_vehicles if manage_settings not defined
        
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        Gate::authorize('manage_settings');

        // Handle File Upload
        if ($request->hasFile('garage_logo')) {
            $path = $request->file('garage_logo')->store('settings', 'public');
            Setting::set('garage_logo', '/storage/' . $path, 'general');
        }

        foreach ($request->except(['_token', '_method', 'garage_logo']) as $key => $value) {
            // Determine group based on key prefix or list (Optional, or just default)
            // For now, preservation of group requires looking up existing, but updateOrCreate handles it if we don't pass group.
            // Wait, my previous analysis said updateOrCreate overwrites group if passed. 
            // Setting::set($key, $value) uses default 'general'.
            // To be safe, we can try to find existing group or just map them.
            
            $group = 'general';
            if (in_array($key, ['tax_rate', 'currency_symbol', 'invoice_prefix', 'bank_id', 'bank_account_no', 'bank_account_name'])) $group = 'finance';
            if (in_array($key, ['maintenance_mode', 'auto_assign_tech', 'enable_3d_check'])) $group = 'system';
            if (in_array($key, ['portal_color_primary', 'portal_color_accent'])) $group = 'theme';

            Setting::set($key, $value, $group);
        }

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống');
    }
}
