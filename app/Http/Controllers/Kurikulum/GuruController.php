<?php

namespace App\Http\Controllers\Kurikulum;

use App\Exports\GuruTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    private const DEFAULT_EMAIL_DOMAIN = 'sma.com';

    private const DEFAULT_PASSWORD = 'default$123';

    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $filter = $request->get('filter', 'all'); // all|mapel|piket|bk|wali|none

        $activeSemester = Semester::where('is_active', true)->first();

        $gurusQuery = Guru::query()->with([
            'user',
            'kelasDiampu',
            'guruMapelKelas.mapel',
            'guruPikets',
            'guruBkKelas',
        ]);

        if ($search) {
            $gurusQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama) like ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(nip) like ?', ['%'.strtolower($search).'%']);
            });
        }

        // Filter berdasarkan tipe penugasan
        match ($filter) {
            'mapel' => $gurusQuery->whereHas('guruMapelKelas'),
            'piket' => $gurusQuery->whereHas('guruPikets'),
            'bk' => $gurusQuery->whereHas('guruBkKelas'),
            'wali' => $gurusQuery->whereIn('id', Kelas::whereNotNull('wali_kelas_id')->pluck('wali_kelas_id')),
            'none' => $gurusQuery->whereDoesntHave('guruMapelKelas')
                ->whereDoesntHave('guruPikets')
                ->whereDoesntHave('guruBkKelas')
                ->whereNotIn('id', Kelas::whereNotNull('wali_kelas_id')->pluck('wali_kelas_id')),
            default => null,
        };

        $gurus = $gurusQuery->orderBy('nama')->paginate(20)->withQueryString();

        $stats = [
            'total' => Guru::count(),
            'mapel' => Guru::whereHas('guruMapelKelas')->count(),
            'wali' => Kelas::whereNotNull('wali_kelas_id')->distinct('wali_kelas_id')->count('wali_kelas_id'),
            'none' => Guru::whereDoesntHave('guruMapelKelas')
                ->whereDoesntHave('guruPikets')
                ->whereDoesntHave('guruBkKelas')
                ->whereNotIn('id', Kelas::whereNotNull('wali_kelas_id')->pluck('wali_kelas_id'))
                ->count(),
        ];

        return view('kurikulum.guru.index', [
            'gurus' => $gurus,
            'search' => $search,
            'filter' => $filter,
            'stats' => $stats,
            'activeSemester' => $activeSemester,
        ]);
    }

    public function create()
    {
        return view('kurikulum.guru.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'nip' => $this->normalizeNip($request->input('nip', '')),
        ]);

        $request->validate([
            'nip' => 'required|string|max:255|regex:/^[0-9]+$/|unique:gurus,nip',
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
        ]);

        $email = $this->buildEmailFromNip($request->nip);
        $plainPassword = self::DEFAULT_PASSWORD;

        if (User::where('email', $email)->exists()) {
            return back()
                ->withErrors([
                    'nip' => 'NIP menghasilkan email yang sudah digunakan.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $email, $plainPassword) {

            $user = User::create([
                'name' => $request->nama,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'default_role' => null,
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
            ]);
        });

        return redirect()
            ->route('kurikulum.guru.index')
            ->with(
                'success',
                "Guru berhasil dibuat. Email: {$email} | Password: {$plainPassword}"
            );
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Guru $guru)
    {
        $page = request()->get('page', 1);

        return view('kurikulum.guru.edit', compact('guru', 'page'));
    }

    public function update(Request $request, Guru $guru)
    {
        $request->merge([
            'nip' => $this->normalizeNip($request->input('nip', '')),
        ]);

        $request->validate([
            'nip' => 'required|string|max:255|regex:/^[0-9]+$/|unique:gurus,nip,'.$guru->id,
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
        ]);

        $email = $this->buildEmailFromNip($request->nip);

        if (
            $guru->user &&
            User::where('email', $email)
                ->where('id', '!=', $guru->user->id)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'nip' => 'NIP menghasilkan email yang sudah digunakan.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $guru, $email) {

            if ($guru->user) {
                $guru->user->update([
                    'name' => $request->nama,
                    'email' => $email,
                ]);
            }

            $guru->update([
                'nip' => $request->nip,
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
            ]);
        });

        $currentPage = $request->get('page', 1);

        return redirect()
            ->route('kurikulum.guru.index', ['page' => $currentPage])
            ->with('success', 'Guru Berhasil Diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        DB::transaction(function () use ($guru) {
            Kelas::where('wali_kelas_id', $guru->id)->update(['wali_kelas_id' => null]);

            $guru->guruMapelKelas()->delete();
            $guru->guruPikets()->delete();
            $guru->guruBkKelas()->delete(); // tambah ini

            if ($guru->user) {
                $guru->user->delete();
            } else {
                $guru->delete();
            }
        });

        $currentPage = $request->get('page', 1);

        return redirect()->route('kurikulum.guru.index', ['page' => $currentPage])
            ->with('success', 'Guru Berhasil Dihapus.');
    }

    private function buildEmailFromNip(string $nip): string
    {
        return $this->normalizeNip($nip).'@'.self::DEFAULT_EMAIL_DOMAIN;
    }

    private function normalizeNip(string $nip): string
    {
        $normalized = strtolower(trim($nip));

        if (str_contains($normalized, '@')) {
            $normalized = explode('@', $normalized)[0] ?? $normalized;
        }

        // Hapus semua spasi (format NIP ASN: "19721021 202121 1 002")
        $normalized = str_replace(' ', '', $normalized);

        return $normalized;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $import = new GuruImport;
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();

            $message = "Import selesai: {$successCount} guru berhasil ditambahkan";
            if ($failureCount > 0) {
                $message .= " ({$failureCount} baris gagal)";
            }

            return redirect()->route('kurikulum.guru.index')
                ->with('success', $message)
                ->with('import_failures', $import->getFailures());

        } catch (\Throwable $e) {
            return back()
                ->withErrors(['file' => 'Error: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new GuruTemplateExport,
            'template-import-guru-'.now()->format('Y-m-d-His').'.xlsx'
        );
    }
}
