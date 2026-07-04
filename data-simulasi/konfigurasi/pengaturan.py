#!/usr/bin/env python3
"""
konfigurasi/pengaturan.py
=========================
Tujuan: Menyimpan semua parameter simulator sekolah.

Tanggung jawab:
- Menyimpan probabilitas generate data
- Menyimpan interval loop service
- Menyimpan master perilaku dan parameter bab/materi
"""

BOBOT_ABSENSI = {
    "hadir": 82,
    "terlambat": 6,
    "izin": 5,
    "sakit": 5,
    "alpha": 2,
}

PELUANG_TUGAS = 40
PELUANG_PENGERJAAN_TUGAS = 90
PELUANG_UJIAN = 25
PELUANG_PERILAKU = 30
PROPORSI_PERILAKU_NEGATIF = 65

JEDA_LOOP_DETIK = 1
HARI_BOOTSTRAP_DEFAULT = 30
JUMLAH_SOAL_UJIAN = 10
JUMLAH_BAB_PER_MAPEL = 4
JUMLAH_MATERI_PER_BAB = 3
NAMA_TABEL_TRACKING_SIMULASI = "simulation_schedule_runs"

PROFIL_ABSENSI = {
    "premium": {"hadir": 90, "terlambat": 5, "izin": 3, "sakit": 2, "alpha": 0},
    "normal": {"hadir": 82, "terlambat": 6, "izin": 5, "sakit": 5, "alpha": 2},
    "preman": {"hadir": 70, "terlambat": 10, "izin": 8, "sakit": 7, "alpha": 5},
    "aktif": {"hadir": 98, "terlambat": 2, "izin": 0, "sakit": 0, "alpha": 0},
}

PROFIL_AKADEMIK_VARIAN = {
    "bagus": (80, 100),
    "sedang": (65, 85),
    "lemah": (55, 70),
}

MASTER_PERILAKU = [
    ("Terlambat masuk kelas", "negatif", -5),
    ("Tidak mengerjakan PR", "negatif", -5),
    ("Tidak memakai atribut lengkap", "negatif", -5),
    ("Membuat gaduh di kelas", "negatif", -10),
    ("Bolos jam pelajaran", "negatif", -15),
    ("Tidak sopan kepada guru", "negatif", -20),
    ("Berkelahi dengan teman", "negatif", -25),
    ("Mencontek saat ujian", "negatif", -20),
    ("Aktif bertanya di kelas", "positif", 5),
    ("Membantu teman kesulitan belajar", "positif", 10),
    ("Mengumpulkan tugas tepat waktu", "positif", 5),
    ("Menjadi juara lomba akademik", "positif", 20),
    ("Menjadi juara lomba non-akademik", "positif", 15),
    ("Aktif dalam kegiatan OSIS/ekskul", "positif", 10),
    ("Membantu piket kelas tanpa diminta", "positif", 5),
]
