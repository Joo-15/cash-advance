<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisbursementRequest;
use App\Models\CashAdvance;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DisbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $departmentName = Auth::user()->department?->name;

        $perPage = (int) $request->input('per_page', 10);

        $disbursement = CashAdvance::with([
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
            ->whereIn('status', ['approved', 'disbursed'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Disbursement/IndexDisbursement', [
            'disbursement' => $disbursement,
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),

        ]);
    }

    public function show($id)
    {
        // dd($id);
        $user = Auth::user();

        $data = CashAdvance::with([
            'approvals',
            'user.department',

        ])
            ->where('id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(DisbursementRequest $request)
    {

        $validated = $request->validated();
        $validated['finance_id'] = Auth::id();

        DB::beginTransaction();

        try {
            if (Auth::user()->role->name === "Finance") {

                // Menggunakan updateOrCreate berdasarkan cash_advance_id
                $disbursement = Disbursement::updateOrCreate(
                    ['cash_advance_id' => $validated['cash_advance_id']], // Kondisi pencarian
                    $validated // Data yang akan diupdate atau dibuat
                );

                $cashAdvance = CashAdvance::find($disbursement->cash_advance_id);

                if ($cashAdvance) {
                    $cashAdvance->update(['status' => 'disbursed']);
                }
            } else {
                DB::rollBack();

                return redirect()->back()->with('error', 'Gagal memproses pencairan');
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pencairan dana berhasil diproses');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process disbursement', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);

            return redirect()->back()->with('error', 'Gagal memproses pencairan: ' . $e->getMessage());
        }
    }
}
