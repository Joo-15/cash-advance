<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ========== CEK HAK AKSES ==========
        $user = Auth::user();

        // Cek apakah user login
        if (!$user) {
            return redirect()->route('login');
        }

        // Definisikan role yang diizinkan
        $allowedRoles = ['Super Admin', 'admin', 'Employee',  'Supervisor', 'Manager', 'Chief', 'General Manager', 'Manager Accounting', 'Finance'];
        // dd($user);
        // Cek apakah user memiliki role yang diizinkan
        if (!in_array($user->role->name, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman dashboard.');
        }

        // ========== FILTER BERDASARKAN ROLE ==========
        // Filter bulan (default: bulan ini)
        $month = $request->get('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        // Base query dengan filter role
        $cashAdvanceQuery = CashAdvance::query();

        // Jika bukan admin/finance, hanya lihat data department sendiri
        if (!in_array($user->role->name, ['Super Admin', 'Admin', 'Finance'])) {
            $cashAdvanceQuery->whereHas('user', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            });
        }

        // Data untuk card statistik (gunakan query yang sudah difilter)
        $totalPengajuan = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->sum('amount');

        $totalDicairkan = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->where('status', 'disbursed')
            ->sum('amount');

        $pendingAmount = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->where('status', 'pending')
            ->sum('amount');

        $pendingCount = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->where('status', 'pending')
            ->count();

        $unaccountedAmount = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->where('status', 'disbursed')
            ->whereNull('attachment')
            ->sum('amount');

        // Data pengajuan per departemen (sesuai role)
        $perDepartemen = $this->getDepartmentData($year, $monthNum, $user);

        // Data status pengajuan
        $statusCounts = (clone $cashAdvanceQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->total];
            });

        // Trend 6 bulan terakhir (sesuai role)
        $trendData = $this->getTrendData($user);

        // Hitung persentase perubahan
        $lastMonthTotal = (clone $cashAdvanceQuery)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        $percentageChange = $lastMonthTotal > 0
            ? (($totalPengajuan - $lastMonthTotal) / $lastMonthTotal) * 100
            : 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_pengajuan' => $totalPengajuan,
                'total_dicairkan' => $totalDicairkan,
                'pending_amount' => $pendingAmount,
                'pending_count' => $pendingCount,
                'unaccounted_amount' => $unaccountedAmount,
                'percentage_change' => round($percentageChange, 1),
                'transaction_count' => (clone $cashAdvanceQuery)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $monthNum)
                    ->where('status', 'disbursed')
                    ->count(),
                'user_role' => $user->role->name,
                'user_name' => $user->name,
                'user_department' => $user->department?->name
            ],
            'per_departemen' => $perDepartemen,
            'status_counts' => [
                'disbursed' => $statusCounts['disbursed'] ?? 0,
                'approved' => $statusCounts['approved'] ?? 0,
                'pending' => $statusCounts['pending'] ?? 0,
                'rejected' => $statusCounts['rejected'] ?? 0,
            ],
            'trend' => [
                'months' => $trendData['months'],
                'total' => $trendData['total'],
                'disbursed' => $trendData['disbursed']
            ],
            'current_month' => $month,
            'user_permissions' => [
                'can_view_all' => in_array($user->role->name, ['admin', 'finance']),
                'can_approve' => in_array($user->role->name, ['admin', 'finance', 'supervisor']),
                'can_export' => in_array($user->role->name, ['admin', 'finance']),
                'can_edit' => in_array($user->role->name, ['admin', 'finance']),
            ]
        ]);
    }

    private function getDepartmentData($year, $monthNum, $user)
    {
        // Admin dan finance bisa melihat semua departemen
        if (in_array($user->role->name, ['Super Admin', 'Admin', 'General Manager', 'Manager Accounting', 'Finance'])) {
            $departments = Department::with(['users.cashAdvances' => function ($query) use ($year, $monthNum) {
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $monthNum);
            }])->get();
        }
        // Supervisor/Manager hanya melihat department sendiri
        elseif (in_array($user->role->name, ['Employee', 'Supervisor', 'Manager', 'Chef'])) {
            $departments = Department::where('id', $user->department_id)
                ->with(['users.cashAdvances' => function ($query) use ($year, $monthNum) {
                    $query->whereYear('created_at', $year)
                        ->whereMonth('created_at', $monthNum);
                }])
                ->get();
        }
        // Role lain tidak bisa melihat data departemen
        else {
            return collect();
        }

        return $departments
            ->map(function ($dept) {
                $allCashAdvances = $dept->users->flatMap(function ($user) {
                    return $user->cashAdvances;
                });

                return [
                    'name' => $dept->name,
                    'amount' => $allCashAdvances->sum('amount'),
                    'total_requests' => $allCashAdvances->count(),
                    'color' => $dept->color ?? '#18a058',
                    'icon' => $dept->icon ?? 'BusinessOutline'
                ];
            })
            ->filter(function ($dept) {
                return $dept['amount'] > 0;
            })
            ->values();
    }

    private function getTrendData($user)
    {
        $trendData = [
            'months' => [],
            'total' => [],
            'disbursed' => []
        ];

        $startOfMonth = now()->startOfMonth();

        // Base query dengan filter role
        $cashAdvanceQuery = CashAdvance::query();

        // Jika bukan admin/finance, hanya lihat data department sendiri
        if (!in_array($user->role->name, ['Super Admin', 'Admin', 'General Manager', 'Manager Accounting', 'Finance'])) {
            $cashAdvanceQuery->whereHas('user', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            });
        }

        for ($i = 5; $i >= 0; $i--) {
            $trendMonth = $startOfMonth->copy()->subMonths($i);
            $trendYear = $trendMonth->year;
            $trendMonthNum = $trendMonth->month;

            $trendData['months'][] = $trendMonth->format('M Y');
            $trendData['total'][] = (clone $cashAdvanceQuery)
                ->whereYear('created_at', $trendYear)
                ->whereMonth('created_at', $trendMonthNum)
                ->sum('amount');
            $trendData['disbursed'][] = (clone $cashAdvanceQuery)
                ->whereYear('created_at', $trendYear)
                ->whereMonth('created_at', $trendMonthNum)
                ->where('status', 'disbursed')
                ->sum('amount');
        }

        return $trendData;
    }
}
