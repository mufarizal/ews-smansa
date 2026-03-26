<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'default_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        return $this->roles->contains('slug', $role);
    }

    public function dashboardRouteName(): ?string
    {
        $roleRouteMap = [
            'admin' => 'admin.dashboard',
            'kurikulum' => 'kurikulum.dashboard',
            'guru_mapel' => 'guru_mapel.dashboard',
            'wali_kelas' => 'wali_kelas.dashboard',
            'guru_piket' => 'guru_piket.dashboard',
            'siswa' => 'siswa.dashboard',
        ];

        foreach ($roleRouteMap as $role => $routeName) {
            if ($this->hasRole($role) && Route::has($routeName)) {
                return $routeName;
            }
        }

        return null;
    }

    public function dashboardUrl(): string
    {
        $roleSlugs = $this->roles->pluck('slug');
        $activeRole = session('active_role');

        $role = $roleSlugs->contains($activeRole)
            ? $activeRole
            : ($roleSlugs->contains($this->default_role)
                ? $this->default_role
                : $roleSlugs->first());

        session(['active_role' => $role]);

        $map = [
            'admin' => route('admin.dashboard'),
            'kurikulum' => route('kurikulum.dashboard'),
            'guru_mapel' => route('guru_mapel.dashboard'),
            'wali_kelas' => route('wali_kelas.dashboard'),
            'guru_piket' => route('guru_piket.dashboard'),
            'siswa' => route('siswa.dashboard'),
        ];

        return $map[$role] ?? '/login';
    }
}
