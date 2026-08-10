<?php

namespace App\Http\Controllers\Web;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function cetakPdf(Request $request, $date = null)
    {
        try {
            // Parse date
            if (!$date) {
                return response()->json(['message' => 'Parameter date diperlukan'], 400);
            }

            $dates = explode('_', $date);
            if (count($dates) !== 2) {
                return response()->json(['message' => 'Format tanggal tidak valid'], 400);
            }

            $startDate = $dates[0];
            $endDate = $dates[1];

            $data  = CashAdvance::with([
                'disbursement',
                'user.department'
            ])
                ->whereHas('disbursement', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('disbursed_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ])
                        ->whereIn('report_status', ['approved', 'not_submitted', 'submitted']);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Format summary
            $summary = [
                'total_data' => $data->count(),
                'total_amount' => $data->sum(function ($item) {
                    return $item->disbursement->amount ?? 0;
                }),
                'total_disbursed' => $data->sum(function ($item) {
                    return $item->disbursement->total_spent ?? 0;
                }),
                'total_remaining' => $data->sum(function ($item) {
                    return $item->disbursement->amount - $item->disbursement->total_spent ?? 0;
                }),
                'total_approved' => $data->filter(function ($item) {
                    return ($item->disbursement->report_status ?? '') === 'approved';
                })->count(),
                'total_warning' => $data->filter(function ($item) {
                    return ($item->disbursement->report_status ?? '') === 'not_submitted';
                })->count(),
                'total_error' => $data->filter(function ($item) {
                    return ($item->disbursement->report_status ?? '') === 'submitted';
                })->count()
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.report', [
                'data' => $data,
                'start_date' => DateHelper::formatIndonesian($startDate),
                'end_date' => DateHelper::formatIndonesian($endDate),
                'generated_at' => now()->format('d-m-Y H:i:s'),
                'summary' => $summary
            ]);

            // Set paper A4 landscape
            $pdf->setPaper('A4', 'landscape');

            // Set options 
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultPaperSize' => 'a4',
                'dpi' => 96,
                'enable_php' => true,
                'enable_javascript' => true,
            ]);

            return $pdf->download("transaksi_{$startDate}_{$endDate}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
