<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashAdvanceRequest;
use App\Models\CashAdvance;
use App\Traits\HasFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CashAdvanceController extends Controller
{
    use HasFilters;

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $cashadvance = CashAdvance::query()
            ->when($request->search, function ($query, $search) {
                $query->where('keperluan', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->sort && $request->order, function ($query) use ($request) {
                // Whitelist field yang boleh di-sort
                $allowedSorts = ['tanggal', 'keperluan', 'jumlah', 'status'];

                if (in_array($request->sort, $allowedSorts)) {
                    $query->orderBy($request->sort, $request->order);
                }
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('CashAdvance/IndexCashAdvance', [
            'pageHeader' => 'Pengajuan Pinjaman',
            'cashadvance' => $cashadvance,
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),
            'statData' => [
                'total_pengajuan' => CashAdvance::count(),
                'pengajuan_disetujui' => CashAdvance::where('status', 'approved')->count(),
                'pengajuan_pending' => CashAdvance::where('status', 'pending')->count(),
                'pengajuan_ditolak' => CashAdvance::where('status', 'rejected')->count(),
            ]
        ]);
    }

    public function store(CashAdvanceRequest $request)
    {
        try {
            // Ambil data yang sudah divalidasi
            $validated = $request->validated();

            // Tambahkan metadata
            $validated['created_by'] = Auth::id();
            $validated['created_at'] = now();
            $validated['updated_at'] = now();

            // Simpan cash advance
            $cashAdvance = CashAdvance::create($validated);

            // Log success (opsional - jika perlu tracking)
            Log::info('Cash advance created', [
                'id' => $cashAdvance->id,
                'jumlah' => $cashAdvance->jumlah,
                'created_by' => Auth::id()
            ]);

            return redirect()
                ->back()
                ->with('success', 'Pengajuan pinjaman berhasil disimpan');
        } catch (\Exception $e) {
            // Log error
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
