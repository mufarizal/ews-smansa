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

## Jalankan di Linux / cPanel

Semua kode sudah kompatibel Linux (path relatif, `python3 -m venv`, penulisan log pakai `os.path.join`).

### Opsi 1 — Manual dengan `start.sh` (paling mudah)

```bash
cd data-simulasi
chmod +x start.sh
./start.sh bootstrap 30     # isi histori 30 hari (sekali jalan)
./start.sh                 # jalankan simulasi (loop terus)
```

Script otomatis membuat virtualenv, install `requirements.txt`, dan memuat `.env`.

### Opsi 2 — systemd service (VPS root)

Salin `simulasi.service` ke `/etc/systemd/system/`, ganti `USERNAME` dan path, lalu:

```bash
systemctl daemon-reload
systemctl enable --now simulasi.service
journalctl -u simulasi.service -f
```

### Opsi 3 — cPanel (tanpa akses root)

cPanel umumnya **tidak bisa** menjalankan service 24 jam. Gunakan **Cron Job**
di cPanel → Cron Jobs. Karena simulator butuh jalan terus, pakai pendekatan
"background + restart otomatis tiap menit":

```cron
* * * * * pgrep -f "data-simulasi/simulasi.py" >/dev/null || /bin/bash /home/USERNAME/ews-smansa/data-simulasi/start.sh >> /home/USERNAME/ews-smansa/data-simulasi/logs/cron.log 2>&1
```

Atau, kalau hanya butuh isi data periodik (bukan real-time), jalankan bootstrap
via cron setiap hari:

```cron
0 1 * * * /bin/bash /home/USERNAME/ews-smansa/data-simulasi/start.sh bootstrap 1 >> /home/USERNAME/ews-smansa/data-simulasi/logs/cron.log 2>&1
```

> Catatan: cPanel shared hosting membatasi `virtualenv`/Python di beberapa paket.
> Kalau `python3 -m venv` tidak diizinkan, install `mysql-connector-python`
> global lewat `pip install --user` dan hapus bagian venv di `start.sh`.

## Catatan

Folder dan file lama sudah dipisahkan dari jalur aktif. Jalur pakai sekarang adalah [inisialisasi.py](inisialisasi.py), [simulasi.py](simulasi.py), dan [utama.py](utama.py).
