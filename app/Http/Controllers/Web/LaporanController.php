<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class LaporanController extends Controller
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
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                // dd($request->all());

                $query->whereHas('disbursement', function ($q) use ($request) {
                    $q->whereBetween('disbursed_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                });
            })

            ->whereHas('disbursement', function ($query) {
                $query->whereIn('report_status', ['approved', 'not_submitted', 'submitted']);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
        // dd($fundUsage);
        return Inertia::render('Report/IndexReport', [
            'fundUsage' => $fundUsage,
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order',

            ]),

        ]);
    }

    public function cetakPdf(Request $request)
    {
        dd($request->all());
    }
}
