<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');

        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        Gate::authorize('manage_settings');

        $validated = $request->validate([
            'garage_name' => 'nullable|string|max:255',
            'garage_phone' => 'nullable|string|max:50',
            'garage_address' => 'nullable|string|max:500',
            'garage_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'invoice_prefix' => 'nullable|string|max:30',
            'bank_id' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'vietqr_template' => 'nullable|string|in:compact,compact2,qr_only,print',
            'qr_payment_content' => 'nullable|string|max:120',
            'maintenance_mode' => 'nullable|boolean',
            'enable_notifications' => 'nullable|boolean',
            'enable_3d_check' => 'nullable|boolean',
            'portal_color_primary' => 'nullable|string|max:20',
            'portal_color_accent' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('garage_logo')) {
            $path = $request->file('garage_logo')->store('settings', 'public');
            Setting::set('garage_logo', '/storage/' . $path, 'general');
        }

        $groups = [
            'general' => ['garage_name', 'garage_phone', 'garage_address'],
            'finance' => ['tax_rate', 'currency_symbol', 'invoice_prefix'],
            'transfer' => ['bank_id', 'bank_account_no', 'bank_account_name', 'vietqr_template', 'qr_payment_content'],
            'system' => ['maintenance_mode', 'enable_notifications', 'enable_3d_check'],
            'theme' => ['portal_color_primary', 'portal_color_accent'],
        ];

        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $validated)) {
                    Setting::set($key, $validated[$key] ?? '', $group);
                }
            }
        }

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống');
    }
}
