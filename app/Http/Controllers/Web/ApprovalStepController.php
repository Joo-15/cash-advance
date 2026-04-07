<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalStepRequest;
use App\Models\ApprovalStep;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ApprovalStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $approvalStep = ApprovalStep::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('ApprovalStep/IndexApprovalStep', [
            'pageHeader' => 'Pengaturan Persetujuan',
            'approvalStep' => $approvalStep,
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

    public function store(ApprovalStepRequest $request)
    {
        try {
            $validated = $request->validated();

            ApprovalStep::create($validated);

            return redirect()
                ->back()
                ->with('success', "Urutan peran berhasil ditambahkan");
        } catch (\Exception $e) {
            Log::error('Error creating ApprovalStep', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan. Silakan coba lagi.');
        }
    }

    public function update(ApprovalStepRequest $request, ApprovalStep $approvalStep)
    {
        try {
            $validated = $request->validated();

            $approvalStep->update($validated);

            return redirect()
                ->back()
                ->with('success', "Urutan persetujuan berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error('Update failed', [
                'approval_step_id' => $approvalStep->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput($request->except('password'))
                ->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    public function destroy(ApprovalStep $approvalStep)
    {
        try {
            $userData = [
                'id' => $approvalStep->id,
                'step_order' => $approvalStep->step_order,
                'name' => $approvalStep->name,
            ];

            Log::info('Approval Step deleted', [
                'deleted_user' => $userData,
                'deleted_by' => Auth::id()
            ]);

            $approvalStep->delete();

            return redirect()
                ->back()
                ->with('success', "{$approvalStep->name} berhasil dihapus");
        } catch (\Exception $e) {
            Log::error('Delete failed', [
                'user_id' => $approvalStep->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus');
        }
    }
}
