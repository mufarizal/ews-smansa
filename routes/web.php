<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\GuruBk\DashboardController as GuruBkDashboardController;
use App\Http\Controllers\GuruBk\MonitoringController;
use App\Http\Controllers\GuruBk\PointPerilakuController as GuruBkPointPerilakuController;
use App\Http\Controllers\GuruBk\PengajaranSayaController as GuruBkPengajaranSayaController;
use App\Http\Controllers\WaliKelas\DashboardController as WaliKelasDashboardController;
use App\Http\Controllers\WaliKelas\KelasSayaController;
use App\Http\Controllers\GuruMapel\AbsensiController as GuruMapelAbsensiController;
use App\Http\Controllers\GuruMapel\BabController;
use App\Http\Controllers\GuruMapel\MateriController;
use App\Http\Controllers\GuruMapel\PengajaranSayaController;
use App\Http\Controllers\GuruMapel\PerilakuSiswaController;
use App\Http\Controllers\GuruMapel\TugasController;
use App\Http\Controllers\GuruMapel\UjianController;
use App\Http\Controllers\GuruPiket\DashboardController as GuruPiketDashboardController;
use App\Http\Controllers\GuruPiket\QRController as GuruPiketQRController;
use App\Http\Controllers\Kurikulum\GuruAssignmentController;
use App\Http\Controllers\Kurikulum\GuruController;
use App\Http\Controllers\Kurikulum\JadwalController;
use App\Http\Controllers\Kurikulum\KelasController as KurikulumKelasController;
use App\Http\Controllers\Kurikulum\MapelController;
use App\Http\Controllers\Kurikulum\SemesterController;
use App\Http\Controllers\Kurikulum\SiswaController as KurikulumSiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\PembelajaranController;
use App\Http\Controllers\Siswa\QRController as SiswaQRController;
use App\Http\Controllers\WaliKelas\PerilakuSiswaController as WaliKelasPerilakuSiswaController;
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
        Route::get('/dashboard', [GuruBkDashboardController::class, 'index'])->name('dashboard');

        Route::get('/pengajaran-saya', [GuruBkPengajaranSayaController::class, 'index'])->name('pengajaran-saya.index');

        Route::resource('point-perilaku', GuruBkPointPerilakuController::class)
            ->parameters(['point-perilaku' => 'perilaku']);
        Route::prefix('monitoring-perilaku')->name('monitoring.')->group(function (){
            Route::get('/', [MonitoringController::class, 'index'])->name('index');
            Route::get('/{kelas}',[MonitoringController::class, 'show'])->name('show');
            Route::get('/{kelas}/siswa/{siswa}', [MonitoringController::class, 'showSiswa'])->name('siswa');
        });
    });

    Route::middleware('role:guru_mapel')->prefix('guru_mapel')->name('guru_mapel.')->group(function () {
        Route::get('/dashboard', function () {
            return view('guru_mapel.dashboard');
        })->name('dashboard');

        Route::get('/pengajaran-saya', [PengajaranSayaController::class, 'index'])->name('pengajaran-saya.index');

        Route::get('/absensi', [GuruMapelAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/{jadwal}', [GuruMapelAbsensiController::class, 'show'])->name('absensi.show');
        Route::post('/absensi/{jadwal}', [GuruMapelAbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/{jadwal}/materis', [GuruMapelAbsensiController::class, 'getMaterisByBab'])->name('absensi.materis');

        Route::resource('bab', BabController::class);
        Route::resource('bab.materi', MateriController::class)->except(['show']);

        Route::get('tugas', [TugasController::class, 'index'])->name('tugas.index');
        Route::get('tugas/create', [TugasController::class, 'create'])->name('tugas.create');
        Route::post('tugas', [TugasController::class, 'store'])->name('tugas.store');
        Route::get('tugas/{tugas}', [TugasController::class, 'show'])->name('tugas.show');
        Route::get('tugas/{tugas}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
        Route::put('tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
        Route::delete('tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');

        Route::get('tugas/{tugas}/nilai', [TugasController::class, 'nilaiIndex'])->name('tugas.nilai.index');
        Route::post('tugas/{tugas}/nilai', [TugasController::class, 'nilaiStore'])->name('tugas.nilai.store');

        Route::post('tugas/{tugas}/soal', [TugasController::class, 'soalStore'])->name('tugas.soal.store');
        Route::put('tugas/{tugas}/soal/{soal}', [TugasController::class, 'soalUpdate'])->name('tugas.soal.update');
        Route::get('tugas/{tugas}/soal/{soal}/edit', [TugasController::class, 'soalEdit'])->name('tugas.soal.edit');
        Route::delete('tugas/{tugas}/soal/{soal}', [TugasController::class, 'soalDestroy'])->name('tugas.soal.destroy');

        Route::get('ujian', [UjianController::class, 'index'])->name('ujian.index');
        Route::get('ujian/create', [UjianController::class, 'create'])->name('ujian.create');
        Route::post('ujian', [UjianController::class, 'store'])->name('ujian.store');
        Route::get('ujian/{ujianHarian}', [UjianController::class, 'show'])->name('ujian.show');
        Route::get('ujian/{ujianHarian}/edit', [UjianController::class, 'edit'])->name('ujian.edit');
        Route::put('ujian/{ujianHarian}', [UjianController::class, 'update'])->name('ujian.update');
        Route::delete('ujian/{ujianHarian}', [UjianController::class, 'destroy'])->name('ujian.destroy');

        Route::patch('ujian/{ujianHarian}/publish', [UjianController::class, 'publish'])->name('ujian.publish');

        Route::post('ujian/{ujianHarian}/soal', [UjianController::class, 'soalStore'])->name('ujian.soal.store');
        Route::put('ujian/{ujianHarian}/soal/{soal}', [UjianController::class, 'soalUpdate'])->name('ujian.soal.update');
        Route::get('ujian/{ujianHarian}/soal/{soal}/edit', [UjianController::class, 'soalEdit'])->name('ujian.soal.edit');
        Route::delete('ujian/{ujianHarian}/soal/{soal}', [UjianController::class, 'soalDestroy'])->name('ujian.soal.destroy');

        Route::get('ujian/{ujianHarian}/hasil', [UjianController::class, 'hasilIndex'])->name('ujian.hasil.index');

        Route::get('perilaku-siswa', [PerilakuSiswaController::class, 'index'])->name('perilaku-siswa.index');
        Route::get('perilaku-siswa/create', [PerilakuSiswaController::class, 'create'])->name('perilaku-siswa.create');
        Route::post('perilaku-siswa', [PerilakuSiswaController::class, 'store'])->name('perilaku-siswa.store');
        Route::get('perilaku-siswa/{perilakuSiswa}', [PerilakuSiswaController::class, 'show'])->name('perilaku-siswa.show');
        Route::get('perilaku-siswa/{perilakuSiswa}/edit', [PerilakuSiswaController::class, 'edit'])->name('perilaku-siswa.edit');
        Route::put('perilaku-siswa/{perilakuSiswa}', [PerilakuSiswaController::class, 'update'])->name('perilaku-siswa.update');
        Route::delete('perilaku-siswa/{perilakuSiswa}', [PerilakuSiswaController::class, 'destroy'])->name('perilaku-siswa.destroy');
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
        Route::get('/dashboard', [GuruPiketDashboardController::class, 'index'])->name('dashboard');

        Route::get('/absensi/qr', [GuruPiketQRController::class, 'index'])->name('qr');
        Route::post('/absensi/qr', [GuruPiketQRController::class, 'generate'])->name('qr.generate');
        Route::get('/absensi/qr/refresh', [GuruPiketQRController::class, 'refreshAttendance'])->name('qr.refresh');
        Route::post('/absensi/qr/reset', [GuruPiketQRController::class, 'resetSession'])->name('qr.reset');
        Route::get('/absensi/history', [GuruPiketQRController::class, 'history'])->name('attendance.history');
        Route::get('/absensi/export', [GuruPiketQRController::class, 'exportReport'])->name('attendance.export');
    });

    Route::middleware('role:wali_kelas')->prefix('wali_kelas')->name('wali_kelas.')->group(function () {
        Route::get('/dashboard', [WaliKelasDashboardController::class, 'index'])->name('dashboard');

        Route::get('/kelas-saya', [KelasSayaController::class, 'index'])->name('kelas-saya.index');

        Route::resource('perilaku-siswa', WaliKelasPerilakuSiswaController::class)->names('perilaku-siswa');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [PembelajaranController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [PembelajaranController::class, 'profil'])->name('profil.index');

        Route::get('/absensi/qr', [SiswaQRController::class, 'scan'])->name('qr.scan');
        Route::post('/absensi/qr', [SiswaQRController::class, 'process'])->name('qr.process');

        Route::get('tugas', [PembelajaranController::class, 'tugasIndex'])->name('tugas.index');
        Route::get('tugas/{tugas}', [PembelajaranController::class, 'tugasShow'])->name('tugas.show');
        Route::get('tugas/{tugas}/kerjakan', [PembelajaranController::class, 'tugasKerjakan'])->name('tugas.kerjakan');
        Route::post('tugas/{tugas}/submit', [PembelajaranController::class, 'tugasSubmit'])->name('tugas.submit');

        Route::get('ujian', [PembelajaranController::class, 'ujianIndex'])->name('ujian.index');
        Route::get('ujian/{ujianHarian}', [PembelajaranController::class, 'ujianShow'])->name('ujian.show');
        Route::post('ujian/{ujianHarian}/submit', [PembelajaranController::class, 'ujianSubmit'])->name('ujian.submit');
        Route::get('ujian/{ujianHarian}/hasil', [PembelajaranController::class, 'ujianHasil'])->name('ujian.hasil');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// use Illuminate\Support\Facades\Http;

// Route::get('/cek-key', function () {

//     return Http::post(
//         "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . env('AI_STUDIO_PRIMARY_KEY'),
//         [
//             "contents" => [
//                 [
//                     "parts" => [
//                         [
//                             "text" => "Halo"
//                         ]
//                     ]
//                 ]
//             ]
//         ]
//     )->json();

// });
require __DIR__ . '/auth.php';
