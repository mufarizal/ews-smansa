<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // 🔒 PREVENT: Cannot delete if only admin left
        $roleIds = $user->roles->pluck('id')->toArray();
        if (! empty($roleIds)) {
            $adminRoleId = Role::where('slug', 'admin')->value('id');

            if (in_array($adminRoleId, $roleIds)) {
                $adminCount = User::whereHas('roles', function ($query) use ($adminRoleId) {
                    $query->where('roles.id', $adminRoleId);
                })->count();

                if ($adminCount <= 1) {
                    return Redirect::route('profile.edit')
                        ->withBag('userDeletion', [
                            'password' => 'Tidak bisa menghapus akun admin terakhir. Pastikan ada admin lain terlebih dahulu.',
                        ]);
                }
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
