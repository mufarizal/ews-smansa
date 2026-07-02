<?php

namespace App\Http\Controllers\Kurikulum;

use App\Exports\SiswaCredentialsExport;
use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaBulkImport;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    private const SISWA_ROLE = 'siswa';

    private const DEFAULT_EMAIL_DOMAIN = 'siswa.com';

    private const DEFAULT_PASSWORD = 'default$123';

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $selectedKelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;

        $siswaQuery = Siswa::with(['kelas', 'user'])
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->orderBy('kelas.nama_kelas')
            ->orderBy('siswas.nis')
            ->select('siswas.*');

        if ($search !== '') {
            $siswaQuery->where(function ($query) use ($search) {
                $query->where('nis', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhereHas('kelas', function ($kelasQuery) use ($search) {
                        $kelasQuery->where('nama_kelas', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedKelasId !== null) {
            $siswaQuery->where('siswas.kelas_id', $selectedKelasId);
        }
        $siswa = $siswaQuery->paginate(25)->withQueryString();
        $kelas = Kelas::orderBy('nama_kelas')->get();

        $totalSiswa = Siswa::count();
        $kelasCount = Kelas::whereHas('siswas')->count();
        $akunSiap = Siswa::whereNotNull('user_id')->count();

        return view('kurikulum.siswa.index', compact(
            'siswa',
            'kelas',
            'search',
            'selectedKelasId',
            'totalSiswa',
            'kelasCount',
            'akunSiap'
        ));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('kurikulum.siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|max:255|unique:siswas,nis',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        $email = $this->buildEmailFromNis($request->nis);
        $plainPassword = $this->generateReadablePassword();
        $roleSiswa = Role::where('slug', self::SISWA_ROLE)->firstOrFail();

        if (User::where('email', $email)->exists()) {
            return back()
                ->withErrors(['nis' => 'NIS menghasilkan email yang sudah digunakan.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $email, $plainPassword, $roleSiswa) {
            $user = User::create([
                'name' => $request->nama,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'default_role' => self::SISWA_ROLE,
            ]);

            $user->roles()->syncWithoutDetaching([$roleSiswa->id]);

            Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $request->kelas_id,
                'nis' => $request->nis,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
            ]);
        });

        return redirect()->route('kurikulum.siswa.index')
            ->with('success', "Siswa berhasil dibuat. Email: $email | Password: $plainPassword");
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $angkatanOptions = $kelas->pluck('angkatan')->unique()->sort()->values();
        $page = request()->get('page', 1);

        return view('kurikulum.siswa.edit', compact('siswa', 'kelas', 'angkatanOptions', 'page'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|max:255|unique:siswas,nis,'.$siswa->id,
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        $email = $this->buildEmailFromNis($request->nis);

        if ($siswa->user && User::where('email', $email)->where('id', '!=', $siswa->user->id)->exists()) {
            return back()
                ->withErrors(['nis' => 'NIS menghasilkan email yang sudah digunakan.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $siswa, $email) {
            if ($siswa->user) {
                $siswa->user->update([
                    'name' => $request->nama,
                    'email' => $email,
                    'default_role' => self::SISWA_ROLE,
                ]);
            }

            $siswa->update([
                'kelas_id' => $request->kelas_id,
                'nis' => $request->nis,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
            ]);
        });

        $currentPage = $request->get('page', 1);

        return redirect()->route('kurikulum.siswa.index', ['page' => $currentPage])
            ->with('success', 'Siswa Berhasil Diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {
            if ($siswa->user) {
                $siswa->user->delete();

                return;
            }

            $siswa->delete();
        });

        $currentPage = $request->get('page', 1);

        return redirect()->route('kurikulum.siswa.index', ['page' => $currentPage])
            ->with('success', 'Siswa Berhasil Dihapus.');
    }

    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $kelasQuery = Kelas::orderBy('id');
        if ($request->filled('kelas_id')) {
            $kelasQuery->where('id', (int) $request->kelas_id);
        }

        $kelas = $kelasQuery->get();

        if ($kelas->isEmpty()) {
            return back()->withErrors([
                'template' => 'Belum ada data kelas. Tambahkan kelas terlebih dahulu sebelum download template import siswa.',
            ]);
        }

        $suffix = '';
        if ($request->filled('kelas_id')) {
            $suffix = '-kelas-'.$kelas->first()->id;
        }

        return Excel::download(
            new SiswaTemplateExport($kelas),
            'template-import-siswa'.$suffix.'-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $roleSiswa = Role::where('slug', self::SISWA_ROLE)->firstOrFail();
        $importer = new SiswaBulkImport;

        Excel::import($importer, $request->file('excel_file'));

        $rows = $importer->rows ?? collect();
        if ($rows->isEmpty()) {
            return back()->withErrors([
                'excel_file' => 'File kosong atau header tidak sesuai. Gunakan template dari sistem.',
            ]);
        }

        $created = 0;
        $skipped = [];
        $generatedCredentials = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nama = trim((string) ($row['nama'] ?? ''));
            $alamat = trim((string) ($row['alamat'] ?? ''));
            $kelasId = (int) ($row['kelas_id'] ?? 0);

            // Resolve NIS: generate otomatis jika kosong atau berisi rumus Excel
            $nisRaw = trim((string) ($row['nis'] ?? ''));
            $nis = $this->resolveNis($nisRaw, $kelasId);

            // Skip baris benar-benar kosong
            if ($nis === '' && $nama === '' && $alamat === '' && $kelasId <= 0) {
                continue;
            }

            if ($nama === '' || $kelasId <= 0) {
                $skipped[] = "Baris {$line}: kolom wajib (kelas_id, nama) belum lengkap.";

                continue;
            }

            $kelas = Kelas::find($kelasId);
            if (! $kelas) {
                $skipped[] = "Baris {$line}: kelas_id {$kelasId} tidak ditemukan.";

                continue;
            }

            // Jika NIS masih kosong setelah resolve (kelas tidak valid), skip
            if ($nis === '') {
                $skipped[] = "Baris {$line}: gagal generate NIS, kelas_id {$kelasId} tidak valid.";

                continue;
            }

            if (Siswa::where('nis', $nis)->exists()) {
                $skipped[] = "Baris {$line}: NIS {$nis} sudah terdaftar.";

                continue;
            }

            $email = $this->buildEmailFromNis($nis);
            if (User::where('email', $email)->exists()) {
                $skipped[] = "Baris {$line}: email {$email} sudah dipakai user lain.";

                continue;
            }

            $plainPassword = $this->generateReadablePassword();

            DB::transaction(function () use ($nama, $email, $plainPassword, $roleSiswa, $kelas, $nis, $alamat, &$generatedCredentials, &$created) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($plainPassword),
                    'default_role' => self::SISWA_ROLE,
                ]);

                $user->roles()->syncWithoutDetaching([$roleSiswa->id]);

                Siswa::create([
                    'user_id' => $user->id,
                    'kelas_id' => $kelas->id,
                    'nis' => $nis,
                    'nama' => $nama,
                    'alamat' => $alamat !== '' ? $alamat : null,
                ]);

                $generatedCredentials[] = [
                    'kelas_id' => $kelas->id,
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas' => $kelas->nama_kelas,
                    'email' => $email,
                    'password' => $plainPassword,
                ];
                $created++;
            });
        }

        if ($created === 0) {
            return back()->withErrors([
                'excel_file' => 'Tidak ada data yang berhasil diimpor. Cek format file/template.',
            ])->with('warning_list', $skipped);
        }

        session([
            'siswa_generated_credentials' => $generatedCredentials,
            'siswa_last_import_skipped' => $skipped,
        ]);

        $message = "Import selesai: {$created} siswa berhasil ditambahkan.";
        if (! empty($skipped)) {
            $message .= ' Ada beberapa baris yang dilewati.';
        }

        return redirect()->route('kurikulum.siswa.index')->with('success', $message);
    }

    public function downloadLastImportCredentials(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $allCredentials = collect(session('siswa_generated_credentials', []));
        $credentials = $allCredentials;

        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        if ($kelasId !== null) {
            $credentials = $credentials
                ->filter(fn (array $row) => (int) ($row['kelas_id'] ?? 0) === $kelasId)
                ->values();
        }

        if ($credentials->isEmpty()) {
            return back()->withErrors([
                'download_credentials' => 'Belum ada data akun pada hasil import terbaru untuk kelas yang dipilih.',
            ]);
        }

        if ($kelasId === null) {
            session()->forget('siswa_generated_credentials');
        } else {
            $remaining = $allCredentials
                ->reject(fn (array $row) => (int) ($row['kelas_id'] ?? 0) === $kelasId)
                ->values();

            if ($remaining->isEmpty()) {
                session()->forget('siswa_generated_credentials');
            } else {
                session(['siswa_generated_credentials' => $remaining->all()]);
            }
        }

        $fileSuffix = $kelasId !== null ? '-kelas-'.$kelasId : '';

        return Excel::download(
            new SiswaCredentialsExport($credentials),
            'akun-login-siswa-import'.$fileSuffix.'-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function generateCredentialsExcel(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        $kelasLabel = null;

        $siswaQuery = Siswa::with(['user', 'kelas']);
        if ($kelasId !== null) {
            $siswaQuery->where('kelas_id', $kelasId);
            $kelasLabel = Kelas::find($kelasId)?->nama_kelas;
        }

        $allSiswa = $siswaQuery->get();

        if ($allSiswa->isEmpty()) {
            $targetText = $kelasLabel ? " pada kelas {$kelasLabel}" : '';

            return back()->withErrors([
                'export_credentials' => 'Belum ada data siswa'.$targetText.' untuk dibuatkan akun login.',
            ]);
        }

        $roleSiswa = Role::where('slug', self::SISWA_ROLE)->firstOrFail();
        $credentials = [];

        foreach ($allSiswa as $siswa) {
            if (! $siswa->user) {
                continue;
            }

            $plainPassword = $this->generateReadablePassword();
            $email = $this->buildEmailFromNis($siswa->nis);

            DB::transaction(function () use ($siswa, $plainPassword, $email, $roleSiswa) {
                $siswa->user->update([
                    'name' => $siswa->nama,
                    'email' => $email,
                    'password' => Hash::make($plainPassword),
                    'default_role' => self::SISWA_ROLE,
                ]);
                $siswa->user->roles()->syncWithoutDetaching([$roleSiswa->id]);
            });

            $credentials[] = [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => optional($siswa->kelas)->nama_kelas,
                'email' => $email,
                'password' => $plainPassword,
            ];
        }

        if (empty($credentials)) {
            $targetText = $kelasLabel ? " pada kelas {$kelasLabel}" : '';

            return back()->withErrors([
                'export_credentials' => 'Tidak ada akun user siswa'.$targetText.' yang bisa diproses.',
            ]);
        }

        $fileSuffix = $kelasId !== null ? '-kelas-'.$kelasId : '-semua';

        return Excel::download(
            new SiswaCredentialsExport(collect($credentials)),
            'akun-login-siswa'.$fileSuffix.'-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    // ─── Private Helpers ────────────────────────────────────────────────────────

    private function resolveNis(string $nisRaw, int $kelasId): string
    {
        // Generate otomatis jika kosong atau mengandung karakter rumus Excel
        $isFormula = str_contains($nisRaw, '=')
            || str_contains($nisRaw, '&')
            || str_contains($nisRaw, 'ROW')
            || str_contains($nisRaw, 'TEXT');

        if ($nisRaw === '' || $isFormula) {
            return $this->generateNisFromKelas($kelasId);
        }

        return $nisRaw;
    }

    private function generateNisFromKelas(int $kelasId): string
    {
        $kelas = Kelas::find($kelasId);
        if (! $kelas) {
            return '';
        }

        // "10 A" → "101", "10 B" → "102", "11 A" → "111", dst
        preg_match('/(\d+)\s*([A-Za-z])/', $kelas->nama_kelas, $m);
        $tingkat = $m[1] ?? '10';
        $nomorHuruf = isset($m[2]) ? (ord(strtoupper($m[2])) - ord('A') + 1) : 1;
        $kodeKelas = $tingkat.$nomorHuruf;

        // Nomor absen berikutnya untuk kelas ini
        $absen = Siswa::where('kelas_id', $kelasId)->count() + 1;

        return $kodeKelas.str_pad($absen, 2, '0', STR_PAD_LEFT);
    }

    private function buildEmailFromNis(string $nis): string
    {
        return trim($nis).'@'.self::DEFAULT_EMAIL_DOMAIN;
    }

    private function generateReadablePassword(): string
    {
        return self::DEFAULT_PASSWORD;
    }
}
