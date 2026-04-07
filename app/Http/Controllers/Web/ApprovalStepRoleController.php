<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalStepRoleRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalStepRole;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ApprovalStepRoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $approvalStepRole = ApprovalStepRole::with('role')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('ApprovalStepRole/IndexApprovalStepRole', [
            'pageHeader' => 'Pengaturan Persetujuan',
            'approvalStepRole' => $approvalStepRole,
            'roles' => Role::getSelectOptions(),
            'approvalStep' => ApprovalStep::getSelectOptions(),
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),
        ]);
    }

    public function show($id) {}

    public function store(ApprovalStepRoleRequest $request)
    {
        try {
            $validated = $request->validated();

            $ApprovalStepRole = ApprovalStepRole::create($validated);

            return redirect()
                ->back()
                ->with('success', "Persetujuan {$ApprovalStepRole->role->name} berhasil ditambahkan");
        } catch (\Exception $e) {
            Log::error('Error creating persetujuan', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan persetujuan. Silakan coba lagi.');
        }
    }

    public function update(ApprovalStepRoleRequest $request, ApprovalStepRole $approval_step_role)
    {
        try {
            $validated = $request->validated();

            $approval_step_role->update($validated);

            return redirect()
                ->back()
                ->with('success', "Departemen {$approval_step_role->role->name} berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error('Update departemen failed', [
                'approval_step_role_id' => $approval_step_role->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui persetujuan. Silakan coba lagi.');
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
