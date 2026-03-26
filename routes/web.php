<?php

use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth'])->group(function () {

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('kelas', KelasController::class)->parameters([
            'kelas' => 'kelas',
        ]);
    });

    Route::middleware('role:guru_mapel')->prefix('guru_mapel')->name('guru_mapel.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_mapel.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:kurikulum')->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('/dashboard', function () {
            return view('kurikulum.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:guru_piket')->prefix('guru_piket')->name('guru_piket.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_piket.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:wali_kelas')->prefix('wali_kelas')->name('wali_kelas.')->group(function () {
        Route::get('/dashboard', function () {
            return view('wali_kelas.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', function () {
            return view('siswa.dashboard');
        })->name('dashboard');
    });

});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
