<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkshopController extends Controller
{
    private array $columns = [
        RepairOrder::STATUS_PENDING => [
            'label' => 'Chờ tiếp nhận',
            'hint' => 'Xe mới tạo phiếu, cần kiểm tra ban đầu.',
            'color' => 'amber',
        ],
        RepairOrder::STATUS_IN_PROGRESS => [
            'label' => 'Đang kiểm tra / sửa',
            'hint' => 'Xe đang được nhân viên xử lý trong xưởng.',
            'color' => 'blue',
        ],
        RepairOrder::STATUS_PENDING_APPROVAL => [
            'label' => 'Chờ khách duyệt',
            'hint' => 'Đã gửi báo giá, đang chờ phản hồi.',
            'color' => 'violet',
        ],
        RepairOrder::STATUS_APPROVED => [
            'label' => 'Khách đã duyệt',
            'hint' => 'Có thể tiếp tục thi công các hạng mục đã duyệt.',
            'color' => 'emerald',
        ],
        RepairOrder::STATUS_COMPLETED => [
            'label' => 'Hoàn thành',
            'hint' => 'Xe đã hoàn tất, kiểm tra thanh toán và bàn giao.',
            'color' => 'cyan',
        ],
        RepairOrder::STATUS_CANCELLED => [
            'label' => 'Đã hủy',
            'hint' => 'Phiếu ngưng xử lý hoặc bị hủy.',
            'color' => 'rose',
        ],
    ];

    public function index(Request $request)
    {
        Gate::authorize('view_repair_orders');

        $query = RepairOrder::with(['customer', 'vehicle', 'advisor'])
            ->withCount([
                'tasks',
                'items',
                'tasks as completed_tasks_count' => fn ($taskQuery) => $taskQuery->where('status', 'completed'),
                'tasks as unfinished_tasks_count' => fn ($taskQuery) => $taskQuery->where('status', '!=', 'completed'),
                'tasks as rejected_tasks_count' => fn ($taskQuery) => $taskQuery->where('customer_approval_status', 'rejected'),
            ])
            ->when($request->filled('q'), function ($orderQuery) use ($request) {
                $keyword = trim($request->q);

                $orderQuery->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('track_id', 'like', "%{$keyword}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                            $customerQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($keyword) {
                            $vehicleQuery->where('license_plate', 'like', "%{$keyword}%")
                                ->orWhere('model', 'like', "%{$keyword}%")
                                ->orWhere('make', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('advisor_id') && $request->advisor_id !== 'all', fn ($orderQuery) => $orderQuery->where('advisor_id', $request->advisor_id))
            ->when($request->filled('payment_status') && $request->payment_status !== 'all', fn ($orderQuery) => $orderQuery->where('payment_status', $request->payment_status));

        if ($request->input('scope', 'active') === 'active') {
            $query->whereNotIn('status', [RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_CANCELLED]);
        }

        $orders = $query->orderByRaw($this->statusOrderSql())
            ->orderBy('expected_completion_date')
            ->latest()
            ->get();

        $groupedOrders = collect(array_keys($this->columns))
            ->mapWithKeys(fn ($status) => [$status => $orders->where('status', $status)->values()]);

        $stats = [
            'active_orders' => RepairOrder::whereNotIn('status', [RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_CANCELLED])->count(),
            'waiting_customer' => RepairOrder::where('status', RepairOrder::STATUS_PENDING_APPROVAL)->count(),
            'due_today' => RepairOrder::whereDate('expected_completion_date', today())
                ->whereNotIn('status', [RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_CANCELLED])
                ->count(),
            'unpaid_completed' => RepairOrder::where('status', RepairOrder::STATUS_COMPLETED)
                ->where(function ($paymentQuery) {
                    $paymentQuery->whereNull('payment_status')
                        ->orWhere('payment_status', '!=', 'paid');
                })
                ->count(),
        ];

        $todayAppointments = Appointment::with(['customer', 'vehicle', 'service'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $advisors = User::where(function ($userQuery) {
            $userQuery->whereIn('role', ['admin', 'manager', 'staff', 'technician'])
                ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->whereIn('slug', ['admin', 'manager', 'staff', 'technician']));
        })->orderBy('name')->get(['id', 'name']);

        return view('admin.workshop.index', [
            'advisors' => $advisors,
            'columns' => $this->columns,
            'groupedOrders' => $groupedOrders,
            'orders' => $orders,
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
            'statusLabels' => collect($this->columns)->map(fn ($column) => $column['label'])->all(),
        ]);
    }

    private function statusOrderSql(): string
    {
        $statuses = array_keys($this->columns);
        $cases = collect($statuses)
            ->map(fn ($status, $index) => "WHEN '{$status}' THEN {$index}")
            ->implode(' ');

        return "CASE status {$cases} ELSE 99 END";
    }
}
