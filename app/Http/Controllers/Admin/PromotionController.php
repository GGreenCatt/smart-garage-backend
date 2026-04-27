<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_promotions');

        $promotions = Promotion::with('customer')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::where('is_active', true)->count(),
            'used' => Promotion::sum('used_count'),
        ];

        return view('admin.promotions.index', compact('promotions', 'stats'));
    }

    public function create()
    {
        Gate::authorize('manage_promotions');

        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_promotions');

        $data = $this->validatedData($request);
        $promotion = Promotion::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_PROMOTION',
            'details' => "Tạo mã khuyến mãi {$promotion->code}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'Đã tạo mã khuyến mãi');
    }

    public function edit(Promotion $promotion)
    {
        Gate::authorize('manage_promotions');

        return view('admin.promotions.edit', compact('promotion'));
    }

    public function show(Promotion $promotion)
    {
        Gate::authorize('manage_promotions');

        return redirect()->route('admin.promotions.edit', $promotion);
    }

    public function update(Request $request, Promotion $promotion)
    {
        Gate::authorize('manage_promotions');

        $data = $this->validatedData($request, $promotion);
        $promotion->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_PROMOTION',
            'details' => "Cập nhật mã khuyến mãi {$promotion->code}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'Đã cập nhật mã khuyến mãi');
    }

    public function destroy(Promotion $promotion)
    {
        Gate::authorize('manage_promotions');

        if ($promotion->used_count > 0) {
            $promotion->update(['is_active' => false]);

            return back()->with('warning', 'Mã đã có lịch sử sử dụng nên không xóa dữ liệu. Hệ thống đã chuyển mã sang ngừng kích hoạt.');
        }

        $code = $promotion->code;
        $promotion->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_PROMOTION',
            'details' => "Xóa mã khuyến mãi {$code}",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Đã xóa mã khuyến mãi');
    }

    private function validatedData(Request $request, ?Promotion $promotion = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotions', 'code')->ignore($promotion?->id),
            ],
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            throw ValidationException::withMessages([
                'value' => 'Mã giảm theo phần trăm không được vượt quá 100%.',
            ]);
        }

        $customerId = null;
        if (! empty($validated['customer_phone'])) {
            $customer = User::where('phone', $validated['customer_phone'])
                ->where(function ($query) {
                    $query->where('role', 'customer')
                        ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
                })
                ->first();

            if (! $customer) {
                throw ValidationException::withMessages([
                    'customer_phone' => 'Không tìm thấy khách hàng theo số điện thoại đã nhập.',
                ]);
            }

            $customerId = $customer->id;
        }

        return [
            'code' => strtoupper(trim($validated['code'])),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'customer_id' => $customerId,
            'vehicle_id' => null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
