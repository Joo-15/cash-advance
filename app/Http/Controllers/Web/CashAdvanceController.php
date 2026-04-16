<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashAdvanceRequest;
use App\Models\Approval;
use App\Models\CashAdvance;
use App\Traits\HasFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CashAdvanceController extends Controller
{
    use HasFilters;

    public function index(Request $request)
    {
        $user = Auth::user();
        $departmentName = $user->department?->name;
        $isSuperAdmin = $user->role === 'super_admin'; // sesuaikan dengan sistemmu

        $perPage = (int) $request->input('per_page', 5);

        // Base query dengan eager loading
        $query = CashAdvance::with([
            'approvals.approvalStep.approvalStepRoles.role',
            'user.department'
        ]);


        $query->whereIn('status', ['pending', 'approved', 'rejected']);

        // Filter search
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('purpose', 'like', $searchTerm)
                    ->orWhere('request_date', 'like', $searchTerm)
                    ->orWhereRaw("CAST(amount AS CHAR) like ?", [$searchTerm]);
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan department (kecuali super admin)
        if (!$isSuperAdmin && $departmentName) {
            $query->whereHas('user.department', function ($q) use ($departmentName) {
                $q->where('name', $departmentName);
            });
        }

        // Sorting
        if ($request->filled('sort') && $request->filled('order')) {
            $allowedSorts = ['request_date', 'purpose', 'amount', 'status', 'created_at'];
            if (in_array($request->sort, $allowedSorts)) {
                $query->orderBy($request->sort, $request->order);
            }
        }

        // Pagination
        $cashadvance = $query->latest()->paginate($perPage)->withQueryString();

        // Optimasi statData - menggunakan 1 query (group by)
        $statusCounts = CashAdvance::query()
            ->when(!$isSuperAdmin && $departmentName, function ($q) use ($departmentName) {
                $q->whereHas('user.department', function ($sub) use ($departmentName) {
                    $sub->where('name', $departmentName);
                });
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $q->where(function ($sub) use ($searchTerm) {
                    $sub->where('purpose', 'like', $searchTerm)
                        ->orWhere('request_date', 'like', $searchTerm)
                        ->orWhereRaw("CAST(amount AS CHAR) like ?", [$searchTerm]);
                });
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('CashAdvance/IndexCashAdvance', [
            'cashadvance' => $cashadvance,
            'filters' => $request->only(['search', 'status', 'per_page', 'sort', 'order']),
            'statData' => [
                'total_pengajuan' => $statusCounts->sum(),
                'pengajuan_disetujui' => $statusCounts->get('approved', 0),
                'pengajuan_pending' => $statusCounts->get('pending', 0),
                'pengajuan_ditolak' => $statusCounts->get('rejected', 0),
                'pengajuan_dicairkan' => $statusCounts->get('disbursed', 0),
            ],
        ]);
    }

    public function store(CashAdvanceRequest $request)
    {

        try {
            DB::beginTransaction();
            // Ambil data yang sudah divalidasi
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();

            // Tambahkan metadata
            // $validated['created_by'] = Auth::id();
            // $validated['created_at'] = now();
            // $validated['updated_at'] = now();

            // Simpan cash advance
            $cashAdvance = CashAdvance::create($validated);
            // dd($cashAdvance->user_id);

            // 2. Buat 3 approval sekaligus
            $approvalStepRoles = [1, 2, 3, 4];
            foreach ($approvalStepRoles as $step) {
                Approval::create([
                    'cash_advance_id' => $cashAdvance->id,
                    'approval_step_id' => $step,
                ]);
            }

            DB::commit();
            return redirect()
                ->back()
                ->with('success', 'Pengajuan pinjaman berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create cash advance', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    public function update(CashAdvanceRequest $request, CashAdvance $pengajuan_pinjaman)
    {
        try {
            $validated = $request->validated();

            // Tambahkan updated_by
            $validated['updated_by'] = Auth::id();

            // Update user
            $pengajuan_pinjaman->update($validated);

            // $filters = $this->getFilters($request);

            return redirect()
                ->back()
                ->with('success', "Pengajuan pinjaman berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error('Update pengajuan pinjaman failed', [
                'cashadvance_id' => $pengajuan_pinjaman->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengajuan pinjaman. Silakan coba lagi.');
        }
    }

    public function generateReceipt($id)
    {

        dd($id);
        try {
            // Ambil data cash advance dengan relasi
            $cashAdvance = CashAdvance::with([
                'user',
                'user.department',
                'approvals',
                'disbursement'
            ])->findOrFail($id);

            // Generate PDF menggunakan DomPDF
            $pdf = Pdf::loadView('pdf.cash-advance-receipt', [
                'data' => $cashAdvance,
                'date' => now(),
                'receipt_number' => 'CA/' . date('Ymd') . '/' . $cashAdvance->id
            ]);

            // Setting PDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            // Return PDF untuk ditampilkan di browser
            return $pdf->stream("Tanda_Terima_{$cashAdvance->id}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    // Menggunakan route model binding
    public function destroy(CashAdvance $pengajuan_pinjaman)
    {

        try {

            $cashAdvanceData = [
                'id' => $pengajuan_pinjaman->id,
                'user_id' => $pengajuan_pinjaman->user_id,
                'tanggal' => $pengajuan_pinjaman->tanggal,
                'keperluan' => $pengajuan_pinjaman->keperluan,
                'jumlah' => $pengajuan_pinjaman->jumlah,
                'status' => $pengajuan_pinjaman->status
            ];

            Log::info('CashAdvance deleted', [
                'deleted_user' => $cashAdvanceData,
                'deleted_by' => Auth::id()
            ]);


            $pengajuan_pinjaman->delete();

            return redirect()
                ->back()
                ->with('success', "Pengajuan pinjaman berhasil dihapus");
        } catch (\Exception $e) {
            Log::error('Delete failed', [
                'pengajuan_pinjaman' => $pengajuan_pinjaman->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus');
        }
    }
}
