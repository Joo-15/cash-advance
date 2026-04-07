<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DepartmentController extends Controller
{

    public function index(Request $request)

    {
        $perPage = $request->get('per_page', 10);

        $departments = Department::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })

            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Department/IndexDepartment', [
            'pageHeader' => 'Data Departemen',
            'departments' => $departments,
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),
        ]);
    }

    public function store(DepartmentRequest $request)
    {
        try {
            $validated = $request->validated();

            $department = Department::create($validated);

            return redirect()
                ->back()
                ->with('success', "Departemen {$department->name} berhasil ditambahkan");
        } catch (\Exception $e) {
            Log::error('Error creating department', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan departemen. Silakan coba lagi.');
        }
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        try {
            $validated = $request->validated();

            // Update user
            $department->update($validated);

            return redirect()
                ->back()
                ->with('success', "Departemen {$department->name} berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error('Update departemen failed', [
                'department_id' => $department->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput($request->except('password'))
                ->with('error', 'Gagal memperbarui departemen. Silakan coba lagi.');
        }
    }

    public function destroy(Department $department)  // Langsung dapat model
    {
        try {
            $dataDepartment = [
                'id' => $department->id,
                'name' => $department->name,
            ];

            Log::info('Department deleted', [
                'deleted_department' => $dataDepartment,
                'deleted_by' => Auth::id()
            ]);

            $department->delete();

            return redirect()
                ->back()
                ->with('success', "Department {$department->name} berhasil dihapus");
        } catch (\Exception $e) {
            Log::error('Delete failed', [
                'department_id' => $department->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus departemen');
        }
    }

    public function getOptions()
    {

        // dd('tes');
        $departments = Department::all()->map(function ($dept) {
            return [
                'label' => $dept->name,
                'value' => $dept->id
            ];
        });

        return response()->json($departments);
    }
}
