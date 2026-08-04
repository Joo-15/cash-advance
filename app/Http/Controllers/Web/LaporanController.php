<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
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
            ->whereHas('disbursement', function ($query) {
                $query->whereIn('report_status', ['approved']);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Report/IndexReport', [
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
}
