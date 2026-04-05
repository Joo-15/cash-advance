<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\ApprovalStepRole;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        // Dapatkan user yang login
        $user = Auth::user();
        $userRole = $user->role->name;
        $departmentId = $user->department_id;

        $query = CashAdvance::with([
            'user.department',
            'approvals.approvalStep.approvalStepRoles.role.users'
        ]);

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->where('purpose', 'like', $searchTerm)
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('user.department', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm);
                    })
                    ->orWhereRaw("CAST(amount AS CHAR) LIKE ?", [$searchTerm])
                    ->orWhereHas('approvals', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('status', 'like', $searchTerm);
                    });
            });
        }

        // Filter berdasarkan departemen
        if ($request->filled('department')) {
            $query->whereHas('user', function ($subQuery) use ($request) {
                $subQuery->where('department_id', $request->department);
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->whereHas('approvals', function ($subQuery) use ($request, $user) {
                $subQuery->where('status', $request->status);

                if ($user) {
                    $userRoleId = $user->role_id;

                    $subQuery->whereHas('approvalStep.approvalStepRoles', function ($roleQuery) use ($userRoleId) {
                        $roleQuery->where('role_id', $userRoleId);
                    });
                }
            });
        }

        // Filter berdasarkan role
        switch ($userRole) {
            case 'Admin':
            case 'Super Admin':
                break;

            case 'Employee':
            case 'Supervisor':
            case 'Manager':
                $query->where(function ($q) use ($departmentId, $user) {
                    $q->whereHas('user', function ($subQuery) use ($departmentId) {
                        $subQuery->where('department_id', $departmentId);
                    });
                });
                break;

            case 'General Manager':
            case 'Manager Accounting':
            case 'Finance':

                $query->where(function ($q) use ($user) {
                    $q->whereHas('approvals', function ($subQuery) {
                        $subQuery->whereIn('status', ['pending', 'approved', 'rejected']);
                    });
                    $q->whereHas('approvals.approvalStep.approvalStepRoles', function ($subQuery) use ($user) {
                        $subQuery->where('role_id', $user->role_id);
                    });
                });
                break;

            default:
                $query->whereRaw('1 = 0');
                break;
        }

        // Sorting - PERBAIKAN UTAMA
        if ($request->filled('sort') && $request->filled('order')) {
            $sortField = $request->sort;
            $sortOrder = $request->order;

            switch ($sortField) {
                case 'cash_advance.amount':
                case 'amount':
                    $query->orderBy(
                        CashAdvance::select('amount')
                            ->whereColumn('cash_advances.id', 'approvals.cash_advance_id'),
                        $sortOrder
                    );
                    break;

                case 'request_date':
                    $query->orderBy(
                        CashAdvance::select('request_date')
                            ->whereColumn('cash_advances.id', 'approvals.cash_advance_id'),
                        $sortOrder
                    );
                    break;

                case 'purpose':
                    $query->orderBy(
                        CashAdvance::select('purpose')
                            ->whereColumn('cash_advances.id', 'approvals.cash_advance_id'),
                        $sortOrder
                    );
                    break;

                case 'status':
                    $query->orderBy(
                        Approval::select('status')
                            ->whereColumn('approvals.cash_advance_id', 'cash_advances.id')
                            ->latest()
                            ->limit(1),
                        $sortOrder
                    );
                    break;

                default:
                    // Default sorting menggunakan created_at dari cash_advances
                    $query->orderBy('cash_advances.created_at', $sortOrder);
                    break;
            }
        } else {
            // Default sorting - PERBAIKAN: Gunakan cash_advances.created_at
            $query->orderBy('cash_advances.created_at', 'desc');
        }

        // Eksekusi query
        $perPage = $request->get('per_page', 15);
        $approvals = $query->paginate($perPage)->withQueryString();


        return Inertia::render('Approval/IndexApproval', [
            'pageHeader' => 'Persetujuan Pengajuan',
            'approval' => $approvals,
            'filters' => $request->only([
                'search',
                'status',
                'department',
                'per_page',
                'sort',
                'order'
            ]),
            'userRole' => $userRole,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Approval $approval)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }

    public function getDetail($id)
    {
        // dd($id);
        $user = Auth::user();

        $approvals = Approval::with([
            'cashAdvance.user.department',
            'approvalStep.approvalStepRoles.role.users',
            'user.department',

        ])
            ->where('cash_advance_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $approvals,
            'approvalStep' => ApprovalStepRole::where('role_id', $user->role_id)->value('approval_step_id'),
        ]);
    }

    public function approve(Request $request, Approval $approval)
    {
        // dd(Auth::user()->role->name);
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {

            $approval->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update status cash advance jika finance sudah approve
            if (Auth::user()->role->name === "Finance") {
                $cashAdvance = CashAdvance::find($approval->cash_advance_id);
                if ($cashAdvance) {
                    $cashAdvance->update([
                        'status' => $validated['status'] === 'approved' ? 'approved' : 'rejected'
                    ]);
                }
            }


            DB::commit();

            return redirect()->back()->with('success', 'Persetujuan berhasil diproses');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update approvals', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);
            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Approval $approval)
    {

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update semua approval untuk cash advance tertentu
            $updatedCount = Approval::where('cash_advance_id', $approval->cash_advance_id)
                ->where('status', 'pending')
                ->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

            // Update status cash advance juga
            $cashAdvance = CashAdvance::find($approval->cash_advance_id);
            // dd($cashAdvance);

            if ($cashAdvance) {
                $cashAdvance->update([
                    'status' => $validated['status'] === 'approved' ? 'approved' : 'rejected'
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "{$updatedCount} persetujuan berhasil diproses");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update approvals by cash advance', [
                'error' => $e->getMessage(),
                'cash_advance_id' => $approval->cash_advance_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }
}
