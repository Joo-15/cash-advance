<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundUsageRequest;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            // Hanya handle file upload
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

                    // Update hanya field attachment
                    $penggunaan_dana->update([
                        'attachment' => $filePath,
                        'original_name' => $originalName
                    ]);
                }

                $message = "File berhasil diperbarui";
            } else {
                $message = "Tidak ada file yang diupload";
            }

            return redirect()
                ->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Update file failed', [
                'cash_advance_id' => $penggunaan_dana->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengupdate file. Silakan coba lagi.');
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
