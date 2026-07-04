# Simulator Aktivitas Sekolah

Project ini hanya bertugas mensimulasikan aktivitas sekolah agar database Laravel selalu terisi data baru.

Python tidak menghitung SAW.
Python tidak membuat AI Recommendation.
Python tidak mengubah tabel hasil SAW maupun tabel AI.

## Struktur Sederhana

```text
koneksi/
   database.py

konfigurasi/
   pengaturan.py

data/
   tabel.py

bantuan/
   acak.py
   log.py

pembuat/
   layanan.py

logs/
legacy/
inisialisasi.py
simulasi.py
utama.py
```

## Mode Jalan

### Inisialisasi

Dipakai sekali untuk isi histori.

```bash
python inisialisasi.py --hari 30
python inisialisasi.py --hari 60
python inisialisasi.py --hari 90
```

### Simulasi

Jalan terus sebagai service.

```bash
python simulasi.py
```

Atau pakai pintu masuk cepat:

```bash
python utama.py
```

## Data yang Dibaca dari Laravel

- semesters
- jadwals
- guru_mapel_kelas
- gurus
- mapels
- kelas
- siswas

## Data yang Dibuat Python

- babs
- materis
- perilakus
- absensis
- tugas
- nilai_tugas
- ujian_harians
- hasil_ujians
- perilaku_siswas
- simulation_schedule_runs

## Alur Simulasi

1. Cek semester aktif.
2. Cek hari dan jam sekarang.
3. Saat service start, kejar dulu jadwal yang terlewat sejak semester aktif.
4. Ambil semua jadwal yang sedang aktif.
5. Claim jadwal supaya tidak dobel.
6. Generate absensi.
7. Jika probabilitas cocok, generate tugas, ujian, dan perilaku.
8. Simpan log ke folder logs/.
9. Sleep lalu ulang lagi.

## Konfigurasi Penting

Edit [konfigurasi/pengaturan.py](konfigurasi/pengaturan.py) untuk mengubah:

- peluang absensi
- peluang tugas
- peluang ujian
- peluang perilaku
- jeda loop service
- jumlah hari bootstrap default

## Catatan

Folder dan file lama sudah dipisahkan dari jalur aktif. Jalur pakai sekarang adalah [inisialisasi.py](inisialisasi.py), [simulasi.py](simulasi.py), dan [utama.py](utama.py).
