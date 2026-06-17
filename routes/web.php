<?php

use App\Http\Controllers\Kurikulum\GuruController;
use App\Http\Controllers\Kurikulum\GuruAssignmentController;
use App\Http\Controllers\Kurikulum\JadwalController;
use App\Http\Controllers\Kurikulum\MapelController;
use App\Http\Controllers\Kurikulum\SemesterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\GuruBk\PengajaranSayaController as GuruBkPengajaranSayaController;
use App\Http\Controllers\GuruMapel\AbsensiController as GuruMapelAbsensiController;
use App\Http\Controllers\GuruMapel\PengajaranSayaController;
use App\Http\Controllers\GuruPiket\QRController as GuruPiketQRController;
use App\Http\Controllers\Kurikulum\KelasController as KurikulumKelasController;
use App\Http\Controllers\Kurikulum\SiswaController as KurikulumSiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\QRController as SiswaQRController;
use App\Http\Controllers\WaliKelas\KelasSayaController;
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
    });

    Route::middleware('role:guru_bk')->prefix('guru_bk')->name('guru_bk.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_bk.dashboard');
        })->name('dashboard');

        Route::get('/pengajaran-saya', [GuruBkPengajaranSayaController::class, 'index'])->name('pengajaran-saya.index');
    });

    Route::middleware('role:guru_mapel')->prefix('guru_mapel')->name('guru_mapel.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_mapel.dashboard');
        })->name('dashboard');

        Route::get('/pengajaran-saya', [PengajaranSayaController::class, 'index'])->name('pengajaran-saya.index');

        Route::get('/absensi', [GuruMapelAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/{jadwal}', [GuruMapelAbsensiController::class, 'show'])->name('absensi.show');
        Route::post('/absensi/{jadwal}', [GuruMapelAbsensiController::class, 'store'])->name('absensi.store');
    });

    Route::middleware('role:kurikulum')->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('/dashboard', function () {
            return view('kurikulum.dashboard');
        })->name('dashboard');

        Route::resource('mapel', MapelController::class);

        Route::resource('kelas', KurikulumKelasController::class)->parameters([
            'kelas' => 'kelas',
        ]);
        Route::get('siswa/template', [KurikulumSiswaController::class, 'downloadTemplate'])->name('siswa.template');
        Route::post('siswa/import', [KurikulumSiswaController::class, 'importExcel'])->name('siswa.import');
        Route::get('siswa/download-last-credentials', [KurikulumSiswaController::class, 'downloadLastImportCredentials'])
            ->name('siswa.download-last-credentials');
        Route::post('siswa/export-credentials', [KurikulumSiswaController::class, 'generateCredentialsExcel'])
            ->name('siswa.export-credentials');
        Route::resource('siswa', KurikulumSiswaController::class);
        Route::resource('semesters', SemesterController::class);

        Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
        Route::get('guru/download-template', [GuruController::class, 'downloadTemplate'])->name('guru.download-template');
        Route::resource('guru', GuruController::class);
        Route::prefix('penugasan-guru')->name('penugasan-guru.')->group(function () {
            Route::get('mapel', [GuruAssignmentController::class, 'mapelIndex'])->name('mapel.index');
            Route::post('mapel', [GuruAssignmentController::class, 'storeMapelAssignment'])->name('mapel.store');
            Route::get('piket', [GuruAssignmentController::class, 'piketIndex'])->name('piket.index');
            Route::post('piket', [GuruAssignmentController::class, 'storePiketAssignment'])->name('piket.store');
            Route::get('wali', [GuruAssignmentController::class, 'waliIndex'])->name('wali.index');
            Route::post('wali', [GuruAssignmentController::class, 'storeWaliAssignment'])->name('wali.store');
            Route::get('bk', [GuruAssignmentController::class, 'bkIndex'])->name('bk.index');
            Route::post('bk', [GuruAssignmentController::class, 'storeBkAssignment'])->name('bk.store');

            Route::delete('mapel/{guru}', [GuruAssignmentController::class, 'mapelDestroy'])->name('mapel.destroy');
            Route::delete('piket/{guru}', [GuruAssignmentController::class, 'piketDestroy'])->name('piket.destroy');
            Route::delete('wali/{kelas}', [GuruAssignmentController::class, 'waliDestroy'])->name('wali.destroy');
            Route::delete('bk/{guru}', [GuruAssignmentController::class, 'bkDestroy'])->name('bk.destroy');
        });

        Route::resource('jadwal', JadwalController::class);
    });

    Route::middleware('role:guru_piket')->prefix('guru_piket')->name('guru_piket.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_piket.dashboard');
        })->name('dashboard');

        Route::get('/absensi/qr', [GuruPiketQRController::class, 'index'])->name('qr');
        Route::post('/absensi/qr', [GuruPiketQRController::class, 'generate'])->name('qr.generate');
        Route::get('/absensi/qr/refresh', [GuruPiketQRController::class, 'refreshAttendance'])->name('qr.refresh');
        Route::post('/absensi/qr/reset', [GuruPiketQRController::class, 'resetSession'])->name('qr.reset');
        Route::get('/absensi/history', [GuruPiketQRController::class, 'history'])->name('attendance.history');
        Route::get('/absensi/export', [GuruPiketQRController::class, 'exportReport'])->name('attendance.export');
    });

    Route::middleware('role:wali_kelas')->prefix('wali_kelas')->name('wali_kelas.')->group(function () {
        Route::get('/dashboard', function () {
            return view('wali_kelas.dashboard');
        })->name('dashboard');

        Route::get('/kelas-saya', [KelasSayaController::class, 'index'])->name('kelas-saya.index');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', function () {
            return view('siswa.dashboard');
        })->name('dashboard');

        Route::get('/absensi/qr', [SiswaQRController::class, 'scan'])->name('qr.scan');
        Route::post('/absensi/qr', [SiswaQRController::class, 'process'])->name('qr.process');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
