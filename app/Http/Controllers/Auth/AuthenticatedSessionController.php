<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->to($request->user()->dashboardUrl());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function switch(Request $request)
    {
        $request->validate(['role' => 'required|string']);

        $roleInput = trim((string) $request->role);
        $user = $request->user();

        $userRoles = $user->roles()->get(['name', 'slug']);

        $role = $userRoles->firstWhere('slug', $roleInput)?->slug;

        if (! $role) {
            $role = $userRoles
                ->first(fn ($item) => strtolower((string) $item->name) === strtolower($roleInput))
                ?->slug;
        }

        if (! $role) {
            abort(403, 'Role tidak valid.');
        }

        $request->session()->put('active_role', $role);

        $dashboards = [
            'admin' => route('admin.dashboard'),
            'kurikulum' => route('kurikulum.dashboard'),
            'guru_mapel' => route('guru_mapel.dashboard'),
            'wali_kelas' => route('wali_kelas.dashboard'),
            'guru_piket' => route('guru_piket.dashboard'),
            'siswa' => route('siswa.dashboard'),
            'guru_bk' => route('guru_bk.dashboard'),
        ];

        return redirect($dashboards[$role] ?? '/login');
    }
}
