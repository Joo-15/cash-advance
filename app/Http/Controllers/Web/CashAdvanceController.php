<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            ->when($request->sort && $request->order, function ($query) use ($request) {
                // Whitelist field yang boleh di-sort
                $allowedSorts = ['tanggal', 'keperluan', 'jumlah', 'status'];

                if (in_array($request->sort, $allowedSorts)) {
                    $query->orderBy($request->sort, $request->order);
                }
            }, function ($query) {
                // Default sorting jika tidak ada parameter sort
                $query->latest('tanggal'); // latest() = orderBy('created_at', 'desc')
            })
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
        // dd($request->all());
        // Validasi
        $validated = $request->validate([
            'tanggal' => 'required',
            'keperluan' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
        ]);

        // Simpan - semua logic otomatis di-handle oleh observer
        CashAdvance::create($validated);
        return redirect()->back()->with('success', 'Data berhasil disimpan');
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

        $cashAdvance = CashAdvance::findOrFail($id);

        $cashAdvance->tanggal = $request->tanggal;
        $cashAdvance->keperluan = $request->keperluan;
        $cashAdvance->jumlah = $request->jumlah;
        $cashAdvance->status = $request->status;

        $cashAdvance->save();

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
