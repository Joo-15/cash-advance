<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $user = User::with('department')

            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($dept) use ($search) {
                            $dept->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('User/IndexUser', [
            'pageHeader' => 'Data Pengguna',
            'users' => $user,
            'departments' => Department::getSelectOptions(),
            'filters' => $request->only([
                'search',
                'status',
                'per_page',
                'sort',
                'order'
            ]),
        ]);
    }

    public function store(UserRequest $request)
    {
        try {
            $validated = $request->validated();

            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            // Tambahkan metadata
            $validated['created_by'] = Auth::id();
            $validated['created_at'] = now();

            // Simpan user
            $user = User::create($validated);

            return redirect()
                ->back()
                ->with('success', "User {$user->name} berhasil ditambahkan");
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->back()
                ->withInput($request->except('password'))
                ->with('error', 'Gagal menambahkan user. Silakan coba lagi.');
        }
    }

    public function update(UserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Handle password
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Tambahkan updated_by
            $validated['updated_by'] = Auth::id();

            // Update user
            $user->update($validated);

            return redirect()
                ->back()
                ->with('success', "User {$user->name} berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error('Update user failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput($request->except('password'))
                ->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    public function destroy(User $user)  // Langsung dapat model
    {
        try {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            Log::info('User deleted', [
                'deleted_user' => $userData,
                'deleted_by' => Auth::id()
            ]);

            $user->delete();

            return redirect()
                ->back()
                ->with('success', "User {$user->name} berhasil dihapus");
        } catch (\Exception $e) {
            Log::error('Delete failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus user');
        }
    }
}
