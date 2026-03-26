<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
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

        $selectedRoleSlugs = Role::whereIn('id', $request->roles)->pluck('slug')->map(fn($slug) => (string) $slug);
        $defaultRole = (string) $request->default_role;

        if (!$selectedRoleSlugs->contains($defaultRole)) {
            throw ValidationException::withMessages([
                'default_role' => 'Role default harus termasuk ke dalam role yang dipilih.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'default_role' => $request->default_role,
        ]);

        $user->roles()->attach($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(string $id)
    {
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|integer|exists:roles,id',
            'default_role' => 'required|string|exists:roles,slug',
            'password' => 'nullable|min:8',
        ]);

        $selectedRoleSlugs = Role::whereIn('id', $request->roles)->pluck('slug')->map(fn($slug) => (string) $slug);
        $defaultRole = (string) $request->default_role;

        if (!$selectedRoleSlugs->contains($defaultRole)) {
            throw ValidationException::withMessages([
                'default_role' => 'Role default harus termasuk ke dalam role yang dipilih.',
            ]);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'default_role' => $request->default_role,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}