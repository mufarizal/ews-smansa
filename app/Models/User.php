<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
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
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        if ($this->roles->isEmpty()) {
            return false;
        }

        return $this->roles->contains('slug', $role);
    }

    public function dashboardRouteName(): ?string
    {
        $this->load('roles');

        if ($this->hasRole('admin')) {
            return 'admin.dashboard';
        }

        if ($this->hasRole('kurikulum')) {
            return 'kurikulum.dashboard';
        }

        if ($this->hasRole('siswa')) {
            return 'siswa.dashboard';
        }

        if ($this->hasRole('guru_mapel')) {
            return 'guru_mapel.dashboard';
        }

        if ($this->hasRole('wali_kelas')) {
            return 'wali_kelas.dashboard';
        }

        if ($this->hasRole('guru_piket')) {
            return 'guru_piket.dashboard';
        }

        if ($this->hasRole('guru_bk')) {
            return 'guru_bk.dashboard';
        }

        return null;
    }

    public function dashboardUrl(): string
    {
        $this->load('roles');

        $roleSlugs = $this->roles->pluck('slug');

        if ($roleSlugs->isEmpty()) {
            Log::warning("User {$this->id} ({$this->email}) has no roles assigned");
            return '/login';
        }

        $activeRole = session('active_role');

        if ($activeRole && $roleSlugs->contains($activeRole)) {
            $role = $activeRole;
        } elseif (!empty($this->default_role) && $roleSlugs->contains($this->default_role)) {
            $role = $this->default_role;
        } else {
            $role = $roleSlugs->first();

            if (empty($this->default_role) || $this->default_role !== $role) {
                try {
                    $this->update(['default_role' => $role]);
                } catch (\Exception $e) {
                    Log::error("Failed to update default_role for user {$this->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $map = [
            'admin' => route('admin.dashboard'),
            'kurikulum' => route('kurikulum.dashboard'),
            'siswa' => route('siswa.dashboard'),
            'guru_mapel' => route('guru_mapel.dashboard'),
            'wali_kelas' => route('wali_kelas.dashboard'),
            'guru_piket' => route('guru_piket.dashboard'),
            'guru_bk' => route('guru_bk.dashboard'),
        ];

        if (!$role || !isset($map[$role])) {
            Log::warning("User {$this->id} ({$this->email}) has invalid role: {$role}");
            return '/login';
        }

        session(['active_role' => $role]);

        return $map[$role];
    }

    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }
}
