<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruBkKelas;
use App\Models\GuruMapelKelas;
use App\Models\GuruPiket;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruAssignmentController extends Controller
{
    private const PIKET_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    // ============ Index Pages ============

    public function mapelIndex()
    {
        return view('kurikulum.guru.assignment.index', $this->sharedAssignmentData() + ['activeTab' => 'mapel']);
    }

    public function piketIndex()
    {
        return view('kurikulum.guru.assignment.index', $this->sharedAssignmentData() + ['activeTab' => 'piket']);
    }

    public function waliIndex()
    {
        return view('kurikulum.guru.assignment.index', $this->sharedAssignmentData() + ['activeTab' => 'wali']);
    }

    public function bkIndex()
    {
        return view('kurikulum.guru.assignment.index', $this->sharedAssignmentData() + ['activeTab' => 'bk']);
    }

    // ============ Shared Data ============

    private function sharedAssignmentData(): array
    {
        $activeSemester = Semester::where('is_active', true)->first();

        $gurus = Guru::with([
            'guruMapelKelas' => fn ($q) => $q->with(['mapel', 'kelas'])
                ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id)),
            'guruBkKelas' => fn ($q) => $q->with('kelas')
                ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id)),
        ])->orderBy('nama')->get();

        return [
            'gurus' => $gurus,
            'guruBkList' => $gurus,
            'kelas' => Kelas::with('waliKelas')->orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama')->get(),
            'piketDays' => self::PIKET_DAYS,
            'activeSemester' => $activeSemester,
        ];
    }

    /**
     * Ambil semester aktif atau abort dengan pesan jelas.
     */
    private function getActiveSemesterOrFail(): Semester
    {
        return Semester::where('is_active', true)->firstOrFail();
    }

    // ============ Store ============

    /**
     * Format request:
     *   mapel_kelas[0][mapel_id]      = <id>
     *   mapel_kelas[0][kelas_ids][]   = <id>, <id>, ...
     *   mapel_kelas[1][mapel_id]      = <id>
     *   mapel_kelas[1][kelas_ids][]   = <id>, ...
     */
    public function storeMapelAssignment(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'mapel_kelas' => 'required|array|min:1',
            'mapel_kelas.*.mapel_id' => 'required|exists:mapels,id',
            'mapel_kelas.*.kelas_ids' => 'required|array|min:1',
            'mapel_kelas.*.kelas_ids.*' => 'exists:kelas,id',
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $semester = $this->getActiveSemesterOrFail();

        DB::transaction(function () use ($guru, $semester, $request) {
            // Hapus semua penugasan mapel guru ini di semester aktif
            $guru->guruMapelKelas()->where('semester_id', $semester->id)->delete();

            foreach ($request->mapel_kelas as $row) {
                $mapelId = $row['mapel_id'];
                $kelasIds = array_values(array_unique($row['kelas_ids']));

                foreach ($kelasIds as $kelasId) {
                    GuruMapelKelas::create([
                        'guru_id' => $guru->id,
                        'semester_id' => $semester->id,
                        'mapel_id' => $mapelId,
                        'kelas_id' => $kelasId,
                    ]);
                }
            }
        });

        return redirect()->route('kurikulum.penugasan-guru.mapel.index')
            ->with('success', "Penugasan mapel {$guru->nama} untuk semester {$semester->nama} berhasil disimpan.");
    }

    public function storePiketAssignment(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'piket_days' => 'required|array|min:1',
            'piket_days.*' => 'in:'.implode(',', self::PIKET_DAYS),
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $semester = $this->getActiveSemesterOrFail();
        $piketDays = array_values(array_unique($request->piket_days));

        DB::transaction(function () use ($guru, $semester, $piketDays) {
            $guru->guruPikets()->where('semester_id', $semester->id)->delete();

            foreach ($piketDays as $day) {
                GuruPiket::create([
                    'guru_id' => $guru->id,
                    'semester_id' => $semester->id,
                    'hari' => $day,
                ]);
            }
        });

        return redirect()->route('kurikulum.penugasan-guru.piket.index')
            ->with('success', "Penugasan piket {$guru->nama} untuk semester {$semester->nama} berhasil disimpan.");
    }

    public function storeWaliAssignment(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $kelas = Kelas::findOrFail($request->kelas_id);
        $semester = $this->getActiveSemesterOrFail();

        DB::transaction(function () use ($kelas, $guru, $semester) {
            // Lepas wali lama milik guru ini di kelas lain (dalam semester yang sama)
            Kelas::where('wali_kelas_id', $guru->id)
                ->where('id', '!=', $kelas->id)
                ->update([
                    'wali_kelas_id' => null,
                    'semester_id' => null,
                ]);

            $kelas->update([
                'wali_kelas_id' => $guru->id,
                'semester_id' => $semester->id,
            ]);
        });

        return redirect()->route('kurikulum.penugasan-guru.wali.index')
            ->with('success', "Wali kelas {$kelas->nama_kelas} berhasil diatur ke {$guru->nama}.");
    }

    public function storeBkAssignment(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $semester = $this->getActiveSemesterOrFail();
        $kelasIds = array_values(array_unique($request->kelas_ids));

        DB::transaction(function () use ($guru, $semester, $kelasIds) {
            $guru->guruBkKelas()->where('semester_id', $semester->id)->delete();

            foreach ($kelasIds as $kelasId) {
                GuruBkKelas::create([
                    'guru_id' => $guru->id,
                    'semester_id' => $semester->id,
                    'kelas_id' => $kelasId,
                ]);
            }
        });

        return redirect()->route('kurikulum.penugasan-guru.bk.index')
            ->with('success', "Penugasan BK {$guru->nama} untuk semester {$semester->nama} berhasil disimpan.");
    }

    // ============ Destroy ============

    public function mapelDestroy(Guru $guru)
    {
        $semester = $this->getActiveSemesterOrFail();
        $guru->guruMapelKelas()->where('semester_id', $semester->id)->delete();

        return back()->with('success', 'Penugasan mapel berhasil dihapus.');
    }

    public function piketDestroy(Guru $guru)
    {
        $semester = $this->getActiveSemesterOrFail();
        $guru->guruPikets()->where('semester_id', $semester->id)->delete();

        return back()->with('success', 'Penugasan piket berhasil dihapus.');
    }

    public function waliDestroy(Kelas $kelas)
    {
        $kelas->update([
            'wali_kelas_id' => null,
            'semester_id' => null,
        ]);

        return back()->with('success', 'Wali kelas berhasil dilepas.');
    }

    public function bkDestroy(Guru $guru)
    {
        $semester = $this->getActiveSemesterOrFail();
        $guru->guruBkKelas()->where('semester_id', $semester->id)->delete();

        return back()->with('success', 'Penugasan BK berhasil dihapus.');
    }
}
