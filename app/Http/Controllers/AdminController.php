<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $paidOrdersThisMonth = RepairOrder::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startOfMonth, $now]);

        $paidOrdersLastMonth = RepairOrder::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startOfLastMonth, $endOfLastMonth]);

        $monthlyRevenue = (float) $paidOrdersThisMonth->sum('total_amount');
        $lastMonthRevenue = (float) $paidOrdersLastMonth->sum('total_amount');

        $activeStatuses = [
            RepairOrder::STATUS_PENDING,
            RepairOrder::STATUS_IN_PROGRESS,
            RepairOrder::STATUS_PENDING_APPROVAL,
            RepairOrder::STATUS_APPROVED,
        ];

        $statusCounts = RepairOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $period = CarbonPeriod::create(now()->subDays(6)->startOfDay(), now()->startOfDay());
        $revenueByDate = RepairOrder::where('payment_status', 'paid')
            ->whereDate('updated_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(updated_at) as paid_date, SUM(total_amount) as total')
            ->groupBy('paid_date')
            ->pluck('total', 'paid_date');

        $revenueChart = collect($period)->map(function (Carbon $date) use ($revenueByDate) {
            $key = $date->toDateString();

            return [
                'label' => $date->format('d/m'),
                'value' => round(((float) ($revenueByDate[$key] ?? 0)) / 1000000, 2),
            ];
        });

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        $stats = [
            'total_vehicles' => Vehicle::count(),
            'active_orders' => RepairOrder::whereIn('status', $activeStatuses)->count(),
            'pending_approval_orders' => RepairOrder::where('status', RepairOrder::STATUS_PENDING_APPROVAL)->count(),
            'completed_unpaid_orders' => RepairOrder::where('status', RepairOrder::STATUS_COMPLETED)
                ->where('payment_status', '!=', 'paid')
                ->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'new_customers_this_month' => User::where('role', 'customer')
                ->whereBetween('created_at', [$startOfMonth, $now])
                ->count(),
            'monthly_revenue' => $monthlyRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'revenue_change_percent' => $lastMonthRevenue > 0
                ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : null,
            'appointments_today' => Appointment::whereDate('scheduled_at', today())->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'revenueChart' => $revenueChart,
            'recentActivities' => $recentActivities,
        ]);
    }

    public function toggleViewMode()
    {
        if (session('admin_view_mode') == 'manager') {
            session(['admin_view_mode' => 'system']);
        } else {
            session(['admin_view_mode' => 'manager']);
        }

        return back();
    }
}
