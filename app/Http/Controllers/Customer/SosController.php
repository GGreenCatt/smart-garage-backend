<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SosRequest;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SosController extends Controller
{
    /**
     * Show the active SOS or the create form.
     */
    public function index(Request $request)
    {
        $activeSos = null;

        if (Auth::check()) {
            $activeSos = SosRequest::where('customer_id', Auth::id())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with(['assignedStaff', 'vehicle'])
                ->first();
        } elseif (session()->has('active_sos_id')) {
            $activeSos = SosRequest::where('id', session('active_sos_id'))
                ->with(['assignedStaff', 'vehicle'])
                ->first();

            // Clear session if the SOS is no longer active
            if ($activeSos && in_array($activeSos->status, ['completed', 'cancelled'])) {
                session()->forget('active_sos_id');
                $activeSos = null;
            }
        }

        // Support Ajax Polling
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $activeSos ? ['id' => $activeSos->id, 'status' => $activeSos->status] : null
            ]);
        }

        if ($activeSos) {
            return view('customer.sos.status', compact('activeSos'));
        }

        $vehicles = Auth::check() ? Vehicle::where('user_id', Auth::id())->get() : collect();
        return view('customer.sos.create', compact('vehicles'));
    }

    /**
     * Store a new SOS request.
     */
    public function store(Request $request)
    {
        $activeSos = null;
        if (Auth::check()) {
            $activeSos = SosRequest::where('customer_id', Auth::id())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->first();
        } elseif (session()->has('active_sos_id')) {
            $activeSos = SosRequest::where('id', session('active_sos_id'))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->first();
        }

        if ($activeSos) {
            return response()->json(['success' => false, 'message' => 'Bạn đã có một yêu cầu cứu hộ đang xử lý.']);
        }

        $rules = [
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'required|string',
            'images.*' => 'nullable|image|max:5120' // 5MB max
        ];

        if (!Auth::check()) {
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_phone'] = 'required|string|max:20';
        }

        $validated = $request->validate($rules);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sos', 'public');
                $imagePaths[] = $path;
            }
        }

        $sos = SosRequest::create([
            'customer_id' => Auth::check() ? Auth::id() : null,
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'description' => $validated['description'],
            'images' => $imagePaths,
            'status' => 'pending'
        ]);

        if (!Auth::check()) {
            session(['active_sos_id' => $sos->id]);
        }

        // Send Notification to Staff
        $customerName = Auth::check() ? Auth::user()->name : ($sos->guest_name ?? 'Khách vãng lai');
        \App\Services\NotificationService::notifyAllStaff(
            'sos_created',
            'Yêu cầu SOS mới',
            "{$customerName} vừa gửi một yêu cầu cứu hộ.",
            route('staff.sos.show', $sos->id),
            'fas fa-ambulance'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu cứu hộ thành công!',
                'redirect' => route('customer.sos.index')
            ]);
        }

        return redirect()->route('customer.sos.index')->with('success', 'Đã gửi yêu cầu cứu hộ thành công!');
    }

    /**
     * Cancel the active SOS request.
     */
    public function cancel(Request $request, $id)
    {
        $sosInfo = SosRequest::findOrFail($id);

        if (Auth::check()) {
            if ($sosInfo->customer_id !== Auth::id()) {
                abort(403);
            }
        } else {
            if (session('active_sos_id') != $sosInfo->id) {
                abort(403);
            }
        }

        if ($sosInfo->status === 'completed' || $sosInfo->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Không thể đổi trạng thái yêu cầu này.']);
        }

        $sosInfo->status = 'cancelled';
        $sosInfo->save();

        if (!Auth::check()) {
            session()->forget('active_sos_id');
        }

        return response()->json(['success' => true, 'message' => 'Đã hủy yêu cầu cứu hộ.']);
    }
}
