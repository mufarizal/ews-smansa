<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $selectedRoleId = $request->filled('role_id') ? (int) $request->role_id : null;

        $activeSemester = Semester::where('is_active', true)->first();

        $usersQuery = User::with(['roles']);

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($selectedRoleId !== null) {
            $usersQuery->whereHas('roles', function ($query) use ($selectedRoleId) {
                $query->where('roles.id', $selectedRoleId);
            });
        }

        $users = $usersQuery
            ->with([
                'guru' => fn ($q) => $q->with([
                    'guruMapelKelas' => fn ($q) => $q->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id)),
                    'guruPikets' => fn ($q) => $q->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id)),
                    'guruBkKelas' => fn ($q) => $q->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id)),
                    'kelasDiampu',
                ]),
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role->name] = User::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))->count();
        }

        return view('admin.users.index', compact('users', 'roles', 'search', 'selectedRoleId', 'activeSemester', 'roleCounts'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|integer|exists:roles,id',
            'default_role' => 'required|string|exists:roles,slug',
        ]);

        $selectedRoles = Role::whereIn('id', $request->roles)->get();
        $selectedRoleSlugs = $selectedRoles->pluck('slug')->map(fn ($slug) => (string) $slug);
        $defaultRole = (string) $request->default_role;

        if (! $selectedRoleSlugs->contains($defaultRole)) {
            throw ValidationException::withMessages([
                'default_role' => 'Role default harus termasuk ke dalam role yang dipilih.',
            ]);
        }

        $userData = [
            'name' => trim((string) $request->name),
            'email' => trim((string) $request->email),
            'default_role' => $defaultRole,
        ];

        $user = new User($userData);
        $user->password = $request->password;
        $user->save();

        $user->roles()->attach($request->roles);

        Log::info('User created successfully', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $request->roles,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user->id);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $page = request()->get('page', 1);

        return view('admin.users.edit', compact('user', 'roles', 'page'));
    }

    public function update(Request $request, User $user)
    {
        if (! $user || ! $user->exists) {
            Log::warning('Attempted to update non-existent user', [
                'user_id' => $user->id ?? 'unknown',
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|integer|exists:roles,id',
            'default_role' => 'required|string|exists:roles,slug',
            'password' => 'nullable|min:8',
        ]);

        $selectedRoles = Role::whereIn('id', $request->roles)->get();
        $selectedRoleSlugs = $selectedRoles->pluck('slug')->map(fn ($slug) => (string) $slug);
        $defaultRole = (string) $request->default_role;

        if (! $selectedRoleSlugs->contains($defaultRole)) {
            throw ValidationException::withMessages([
                'default_role' => 'Role default harus termasuk ke dalam role yang dipilih.',
            ]);
        }

        $isUpdatingOwnAccount = Auth::id() === $user->id;
        $currentUserRoles = Auth::user()->roles->pluck('id')->toArray();
        $newRoles = $request->roles ?? [];

        if ($isUpdatingOwnAccount && empty($newRoles)) {
            Log::warning('Attempted to remove all roles from self', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'admin_id' => Auth::id(),
            ]);
            throw ValidationException::withMessages([
                'roles' => 'Anda harus memiliki setidaknya satu role. Tidak bisa menghapus semua role sendiri!',
            ]);
        }

        if ($isUpdatingOwnAccount && ! empty($currentUserRoles)) {
            $commonRoles = array_intersect($currentUserRoles, $newRoles);
            if (empty($commonRoles)) {
                Log::warning('Attempted to change all own roles', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'old_roles' => $currentUserRoles,
                    'new_roles' => $newRoles,
                ]);
                throw ValidationException::withMessages([
                    'roles' => 'Anda harus mempertahankan setidaknya satu role yang sudah ada. Anda tidak bisa menghapus semua role milik Anda!',
                ]);
            }
        }

        $rolesChanged = count(array_diff($currentUserRoles, $newRoles)) > 0 ||
            count(array_diff($newRoles, $currentUserRoles)) > 0;

        $updateData = [
            'name' => trim((string) $request->name),
            'email' => trim((string) $request->email),
            'default_role' => $defaultRole,
        ];

        $user->fill($updateData);

        $password = trim((string) $request->input('password', ''));
        if (! empty($password)) {
            $user->password = $password;
        }

        $user->save();

        if (! empty($newRoles)) {
            $user->roles()->sync($newRoles);
        }

        Log::info('User updated successfully', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'updated_by' => Auth::id(),
            'roles_changed' => $rolesChanged,
        ]);

        if ($isUpdatingOwnAccount && $rolesChanged) {
            $user->refresh();

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->with('info', 'Role Anda telah diubah. Silakan login kembali dengan role yang baru.');
        }

        $currentPage = $request->get('page', 1);

        return redirect()->route('admin.users.index', ['page' => $currentPage])
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (! $user || ! $user->exists) {
            Log::warning('Attempted to delete non-existent user', [
                'user_id' => $user->id ?? 'unknown',
                'deleted_by' => Auth::id(),
            ]);

            return back()->with('error', 'User tidak ditemukan.');
        }

        if (Auth::id() === $user->id) {
            Log::warning('User attempted to delete their own account', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri!');
        }

        $roleIds = $user->roles->pluck('id')->toArray();
        $adminRoleId = Role::where('slug', 'admin')->value('id');

        if (! empty($roleIds) && in_array($adminRoleId, $roleIds)) {
            $adminCount = User::whereHas('roles', function ($query) use ($adminRoleId) {
                $query->where('roles.id', $adminRoleId);
            })->count();

            if ($adminCount <= 1) {
                Log::warning('Attempted to delete last admin', [
                    'admin_being_deleted' => $user->id,
                    'admin_being_deleted_email' => $user->email,
                    'deleted_by' => Auth::id(),
                ]);

                return back()->with('error', 'Tidak bisa menghapus admin terakhir. Pastikan ada admin lain sebelum menghapus.');
            }
        }

        try {
            $user->delete();

            Log::info('User deleted successfully', [
                'deleted_user_id' => $user->id,
                'deleted_user_email' => $user->email,
                'deleted_by' => Auth::id(),
            ]);

            $currentPage = $request->get('page', 1);

            return redirect()->route('admin.users.index', ['page' => $currentPage])
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'deleted_by' => Auth::id(),
            ]);

            return back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }
}
