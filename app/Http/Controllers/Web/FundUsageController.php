<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundUsageRequest;
use App\Models\CashAdvance;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class FundUsageController extends Controller
{

    public function index(Request $request)
    {

        $perPage = (int) $request->input('per_page', 10);

        $fundUsage = CashAdvance::with([
            'disbursement',
            'user.department'
        ])
            ->when($request->search, function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('purpose', 'like', $searchTerm)
                        ->orWhere('request_date', 'like', $searchTerm)
                        ->orWhereRaw("CAST(amount AS CHAR) like ?", [$searchTerm]);
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->sort && $request->order, function ($query) use ($request) {
                // Whitelist field yang boleh di-sort
                $allowedSorts = ['request_date', 'purpose', 'amount', 'status'];

                if (in_array($request->sort, $allowedSorts)) {
                    $query->orderBy($request->sort, $request->order);
                }
            })
            ->whereIn('status', ['disbursed'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('FundUsage/IndexFundUsage', [
            'fundUsage' => $fundUsage,
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),

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
    public function show(string $id)
    {

        $data = CashAdvance::with([
            'approvals',
            'user.department',
            'disbursement'
        ])
            ->where('id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

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
    public function update(FundUsageRequest $request, CashAdvance $penggunaan_dana)
    {

        try {

            // Handle file upload (attachment di tabel CashAdvance)
            $fileUploaded = false;
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    // Hapus file lama jika ada
                    if ($penggunaan_dana->attachment) {
                        Storage::disk('public')->delete($penggunaan_dana->attachment);
                    }

                    // Generate unique filename
                    $originalName = $file->getClientOriginalName();
                    $fileName = time() . '_' . uniqid() . '_' . $originalName;
                    $filePath = $file->storeAs('uploads/documents', $fileName, 'public');

                    // Update field attachment di tabel CashAdvance
                    $penggunaan_dana->update([
                        'attachment' => $filePath
                    ]);

                    $fileUploaded = true;
                }
            }

            // Update existing disbursement
            $penggunaan_dana->disbursement->update([
                'total_spent' => $request->total_spent,
                'report_notes' => $request->report_notes,
                'report_status' => 'submitted',
                'submitted_at' => now()
            ]);




            return redirect()
                ->back()
                ->with('success', "Laporan berhasil dikirim");
        } catch (\Exception $e) {
            Log::error('Update failed', [
                'cash_advance_id' => $penggunaan_dana->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengupdate data. Silakan coba lagi.');
        }
    }

    public function review(Request $request, CashAdvance $disbursment)
    {
        // dd($request->all());
        $user = Auth::user();

        // Cek role
        if (!$user || $user->role->name !== "Finance") {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Hanya Finance yang dapat mereview laporan.'
            ], 403);
        }

        $request->validate([
            'report_status' => 'required|in:approved,rejected',
            'finance_notes' => 'nullable|string|max:500', // Bisa null untuk approved
        ]);

        // Validasi tambahan: finance_notes wajib jika rejected
        if ($request->report_status === 'rejected' && empty($request->finance_notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan penolakan wajib diisi saat menolak laporan',
                'errors' => [
                    'finance_notes' => ['Catatan penolakan harus diisi']
                ]
            ], 422);
        }


        $disbursement = $disbursment->disbursement;

        if (!$disbursement) {
            return response()->json([
                'success' => false,
                'message' => 'Data pencairan tidak ditemukan untuk cash advance ini'
            ], 404);
        }

        // Cek status laporan
        if ($disbursement->report_status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak dapat direview karena status saat ini: ' . $disbursement->report_status
            ], 400);
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'finance_notes' => $request->finance_notes,
                'report_status' => $request->report_status,
            ];

            // Jika approved, tambahkan approved_at (opsional)
            if ($request->report_status === 'approved') {
                $updateData['approved_at'] = now();
            }


            // Update data disbursement
            $disbursement->update($updateData);

            DB::commit();


            return redirect()->back()->with([
                'success' => $request->report_status === 'approved'
                    ? 'Laporan berhasil disetujui'
                    : 'Laporan berhasil ditolak',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Review finance failed', [
                'cash_advance_id' => $disbursement->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit laporan: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
