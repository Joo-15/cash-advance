<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            ->when($request->sort_by, function ($query) use ($request) {
                $query->orderBy(
                    $request->sort_by,
                    $request->sort_type ?? 'desc'
                );
            })
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('CashAdvance/IndexCashAdvance', [
            'pageHeader' => 'Pengajuan Pinjaman',
            'cashadvance' => $cashadvance,
            'filters' => $request->only(['search', 'status', 'per_page']),
            'statData' => [
                'total_pengajuan' => CashAdvance::count(),
                'pengajuan_disetujui' => CashAdvance::where('status', 'approved')->count(),
                'pengajuan_pending' => CashAdvance::where('status', 'pending')->count(),
                'pengajuan_ditolak' => CashAdvance::where('status', 'rejected')->count(),
            ]
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
        //
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'keperluan' => 'required',
            'jumlah' => 'required|numeric',
        ]);

        CashAdvance::where('id', $id)->update([
            'tanggal' => $request->tanggal
                ? Carbon::createFromTimestampMs($request->tanggal)
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d')
                : null,
            'keperluan' => $request->keperluan,
            'jumlah' => $request->jumlah,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        CashAdvance::where('id', $id)->delete();

        $page = $request->input('page', 1);

        return redirect()
            ->route('pengajuan-pinjaman.index', [
                'page' => $page
            ])
            ->with('success', 'Data pengajuan berhasil dihapus');
    }
}
