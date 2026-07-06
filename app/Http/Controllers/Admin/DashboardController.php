<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        $roleCounts = [];

        foreach ($roles as $role) {
            $roleCounts[$role->slug] = [
                'name' => $role->name,
                'count' => User::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))->count(),
            ];
        }

        return view('admin.dashboard', [
            'roleCounts' => $roleCounts,
            'totalUsers' => User::count(),
        ]);
    }
}
