#!/usr/bin/env python3
"""
generate_dummy_data.py
=======================
Generator data simulasi (dummy) untuk sistem EWS SMANSA.

Script ini HANYA meng-insert "data mentah" ke database MySQL/MariaDB:
    - absensis        (absensi per mapel)
    - tugas + nilai_tugas
    - ujian_harians + hasil_ujians
    - perilaku_siswas

Proses perhitungan SAW / AI TIDAK dilakukan di sini — itu tanggung jawab
aplikasi web (Laravel). Script ini murni penyedia data mentah yang berubah
setiap kali dijalankan, untuk mensimulasikan aktivitas sekolah sehari-hari.

Asumsi data master yang SUDAH ADA di database (tidak disentuh script ini):
    - users, roles, role_user
    - semesters (ada 1 semester dengan is_active = 1)
    - kelas
    - siswas
    - gurus
    - mapels
    - guru_mapel_kelas (penugasan guru ke mapel & kelas)

Data master yang akan DIBUAT OTOMATIS oleh script ini jika belum ada:
    - babs        (per guru_mapel_kelas)
    - materis     (per bab)
    - perilakus   (master jenis perilaku positif/negatif)

Cara pakai:
    python generate_dummy_data.py                # generate untuk HARI INI saja
    python generate_dummy_data.py --days 30       # generate 30 hari terakhir (backfill awal)
    python generate_dummy_data.py --date 2026-06-15  # generate untuk tanggal tertentu

Jalankan script ini setiap hari (manual, atau via Task Scheduler / cron) untuk
mensimulasikan bahwa data terus bertambah seperti aktivitas sekolah nyata.
"""

import os
import sys
import random
import argparse
from datetime import date, timedelta

try:
    import mysql.connector
    from mysql.connector import Error
except ImportError:
    sys.exit(
        "Library 'mysql-connector-python' belum terpasang.\n"
        "Install dulu dengan: pip install mysql-connector-python"
    )

# =========================================================================
# KONFIGURASI KONEKSI DATABASE
# Ganti sesuai .env Laravel kamu (config/database.php / file .env)
# =========================================================================
DB_CONFIG = {
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", 3307)),
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", "root"),
    "database": os.environ.get("DB_DATABASE", "ews_smansa"),
}

# =========================================================================
# PARAMETER SIMULASI — bebas kamu ubah sesuai kebutuhan skripsi
# =========================================================================

# Distribusi status absensi (harus total 100)
ABSENSI_WEIGHTS = {
    "hadir": 82,
    "terlambat": 6,
    "izin": 5,
    "sakit": 5,
    "alpha": 2,
}

# Peluang (%) sebuah guru_mapel_kelas membuat TUGAS baru pada suatu hari
PELUANG_TUGAS_BARU = 12

# Peluang (%) sebuah guru_mapel_kelas membuat UJIAN HARIAN baru pada suatu hari
PELUANG_UJIAN_BARU = 6

# Peluang (%) seorang siswa dicatat perilakunya pada suatu hari
PELUANG_PERILAKU = 8

# Proporsi perilaku negatif vs positif saat dicatat
PROPORSI_PERILAKU_NEGATIF = 65  # sisanya positif

# Master perilaku yang akan dibuat otomatis jika tabel perilakus masih kosong
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

# Nama bab & materi generik (dipakai untuk semua mapel agar sederhana)
JUMLAH_BAB_PER_MAPEL = 4
JUMLAH_MATERI_PER_BAB = 3


# =========================================================================
# HELPER UMUM
# =========================================================================

def get_connection():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except Error as e:
        sys.exit(f"Gagal konek ke database: {e}")


def weighted_choice(weights: dict):
    """Pilih 1 key dari dict {key: bobot} secara random sesuai bobotnya."""
    keys = list(weights.keys())
    bobot = list(weights.values())
    return random.choices(keys, weights=bobot, k=1)[0]


def chance(percent: int) -> bool:
    """True dengan peluang `percent` persen."""
    return random.randint(1, 100) <= percent


# =========================================================================
# TAHAP 1 — PASTIKAN DATA MASTER PENDUKUNG SUDAH ADA
# =========================================================================

def ensure_perilakus(cursor):
    cursor.execute("SELECT COUNT(*) AS n FROM perilakus")
    if cursor.fetchone()["n"] > 0:
        return
    print("→ Membuat master data 'perilakus' ...")
    for nama, jenis, poin in MASTER_PERILAKU:
        cursor.execute(
            """INSERT INTO perilakus (nama_perilaku, jenis, poin, status_aktif, created_at, updated_at)
               VALUES (%s, %s, %s, 1, NOW(), NOW())""",
            (nama, jenis, poin),
        )
    print(f"  {len(MASTER_PERILAKU)} jenis perilaku ditambahkan.")


def ensure_babs_dan_materis(cursor):
    """Buat bab & materi untuk setiap guru_mapel_kelas yang belum punya bab."""
    cursor.execute("""
        SELECT gmk.id AS guru_mapel_kelas_id, m.nama AS mapel_nama
        FROM guru_mapel_kelas gmk
        JOIN mapels m ON m.id = gmk.mapel_id
        WHERE gmk.id NOT IN (SELECT DISTINCT guru_mapel_kelas_id FROM babs)
    """)
    target = cursor.fetchall()
    if not target:
        return

    print(f"→ Membuat bab & materi untuk {len(target)} penugasan guru_mapel_kelas ...")
    total_bab, total_materi = 0, 0
    for row in target:
        gmk_id = row["guru_mapel_kelas_id"]
        mapel = row["mapel_nama"]
        for i in range(1, JUMLAH_BAB_PER_MAPEL + 1):
            cursor.execute(
                """INSERT INTO babs (guru_mapel_kelas_id, nama_bab, urutan, deskripsi, created_at, updated_at)
                   VALUES (%s, %s, %s, %s, NOW(), NOW())""",
                (gmk_id, f"Bab {i} - {mapel}", i, f"Materi bab {i} untuk mata pelajaran {mapel}"),
            )
            bab_id = cursor.lastrowid
            total_bab += 1
            for j in range(1, JUMLAH_MATERI_PER_BAB + 1):
                cursor.execute(
                    """INSERT INTO materis (bab_id, judul, isi_materi, urutan, created_at, updated_at)
                       VALUES (%s, %s, %s, %s, NOW(), NOW())""",
                    (bab_id, f"Materi {j} Bab {i}", f"Ringkasan materi {j} pada bab {i}.", j),
                )
                total_materi += 1
    print(f"  {total_bab} bab & {total_materi} materi ditambahkan.")


# =========================================================================
# TAHAP 2 — DATA REFERENSI YANG DIPAKAI BERULANG
# =========================================================================

def get_semester_aktif(cursor):
    cursor.execute("SELECT id FROM semesters WHERE is_active = 1 LIMIT 1")
    row = cursor.fetchone()
    if not row:
        sys.exit("Tidak ada semester dengan is_active = 1. Set salah satu semester aktif dulu.")
    return row["id"]


def get_guru_mapel_kelas(cursor, semester_id):
    cursor.execute(
        """SELECT id, guru_id, mapel_id, kelas_id
           FROM guru_mapel_kelas WHERE semester_id = %s""",
        (semester_id,),
    )
    return cursor.fetchall()


def get_siswa_by_kelas(cursor):
    cursor.execute("SELECT id, kelas_id FROM siswas")
    siswa_per_kelas = {}
    for row in cursor.fetchall():
        siswa_per_kelas.setdefault(row["kelas_id"], []).append(row["id"])
    return siswa_per_kelas


def get_bab_by_gmk(cursor):
    cursor.execute("SELECT id, guru_mapel_kelas_id FROM babs")
    bab_per_gmk = {}
    for row in cursor.fetchall():
        bab_per_gmk.setdefault(row["guru_mapel_kelas_id"], []).append(row["id"])
    return bab_per_gmk


def get_materi_by_bab(cursor):
    cursor.execute("SELECT id, bab_id FROM materis")
    materi_per_bab = {}
    for row in cursor.fetchall():
        materi_per_bab.setdefault(row["bab_id"], []).append(row["id"])
    return materi_per_bab


def get_perilaku_ids(cursor):
    cursor.execute("SELECT id, jenis FROM perilakus WHERE status_aktif = 1")
    positif, negatif = [], []
    for row in cursor.fetchall():
        (positif if row["jenis"] == "positif" else negatif).append(row["id"])
    return positif, negatif


# =========================================================================
# TAHAP 3 — GENERATE DATA MENTAH HARIAN
# =========================================================================

def generate_absensi(cursor, tanggal, gmk_list, siswa_per_kelas):
    dibuat = 0
    for gmk in gmk_list:
        siswa_ids = siswa_per_kelas.get(gmk["kelas_id"], [])
        for siswa_id in siswa_ids:
            # Idempotensi: skip kalau absensi mapel ini di tanggal ini sudah ada
            cursor.execute(
                """SELECT id FROM absensis
                   WHERE siswa_id=%s AND tanggal=%s AND tipe='mapel' AND mapel_id=%s
                   LIMIT 1""",
                (siswa_id, tanggal, gmk["mapel_id"]),
            )
            if cursor.fetchone():
                continue

            status = weighted_choice(ABSENSI_WEIGHTS)
            terlambat_menit = random.randint(1, 20) if status == "terlambat" else 0
            jam_masuk = f"{tanggal} 07:{random.randint(0, 59):02d}:00" if status in ("hadir", "terlambat") else None

            cursor.execute(
                """INSERT INTO absensis
                   (siswa_id, tanggal, tipe, guru_id, mapel_id, status,
                    jam_masuk, terlambat_menit, sudah_disetujui, created_at, updated_at)
                   VALUES (%s, %s, 'mapel', %s, %s, %s, %s, %s, 1, NOW(), NOW())""",
                (siswa_id, tanggal, gmk["guru_id"], gmk["mapel_id"], status,
                 jam_masuk, terlambat_menit),
            )
            dibuat += 1
    return dibuat


def generate_tugas_dan_nilai(cursor, tanggal, gmk_list, siswa_per_kelas, materi_per_bab, bab_per_gmk):
    tugas_dibuat, nilai_dibuat = 0, 0
    for gmk in gmk_list:
        if not chance(PELUANG_TUGAS_BARU):
            continue
        bab_ids = bab_per_gmk.get(gmk["id"], [])
        if not bab_ids:
            continue
        bab_id = random.choice(bab_ids)
        materi_ids = materi_per_bab.get(bab_id, [])
        if not materi_ids:
            continue
        materi_id = random.choice(materi_ids)

        deadline = tanggal + timedelta(days=random.randint(3, 10))
        cursor.execute(
            """INSERT INTO tugas
               (guru_mapel_kelas_id, materi_id, judul, deskripsi, tanggal_tugas, tanggal_deadline,
                created_at, updated_at)
               VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())""",
            (gmk["id"], materi_id, f"Tugas {tanggal.strftime('%d-%m-%Y')}",
             "Tugas dibuat otomatis oleh simulasi data.", tanggal, deadline),
        )
        tugas_id = cursor.lastrowid
        tugas_dibuat += 1

        for siswa_id in siswa_per_kelas.get(gmk["kelas_id"], []):
            if chance(90):  # 90% siswa mengerjakan
                nilai = round(random.uniform(55, 100), 2)
                status = "mengerjakan"
            else:
                nilai = None
                status = "tidak_mengerjakan"
            cursor.execute(
                """INSERT INTO nilai_tugas (tugas_id, siswa_id, nilai, status, created_at, updated_at)
                   VALUES (%s, %s, %s, %s, NOW(), NOW())""",
                (tugas_id, siswa_id, nilai, status),
            )
            nilai_dibuat += 1
    return tugas_dibuat, nilai_dibuat


def generate_ujian_dan_hasil(cursor, tanggal, gmk_list, siswa_per_kelas, bab_per_gmk):
    ujian_dibuat, hasil_dibuat = 0, 0
    for gmk in gmk_list:
        if not chance(PELUANG_UJIAN_BARU):
            continue
        bab_ids = bab_per_gmk.get(gmk["id"], [])
        if not bab_ids:
            continue
        bab_id = random.choice(bab_ids)

        cursor.execute(
            """INSERT INTO ujian_harians
               (guru_mapel_kelas_id, bab_id, judul, tanggal_ujian, durasi_menit, status, created_at, updated_at)
               VALUES (%s, %s, %s, %s, %s, 'selesai', NOW(), NOW())""",
            (gmk["id"], bab_id, f"Ulangan Harian {tanggal.strftime('%d-%m-%Y')}", tanggal, 60),
        )
        ujian_id = cursor.lastrowid
        ujian_dibuat += 1

        jumlah_soal = 10
        for siswa_id in siswa_per_kelas.get(gmk["kelas_id"], []):
            benar = random.randint(3, jumlah_soal)
            salah = jumlah_soal - benar
            nilai = round((benar / jumlah_soal) * 100, 2)
            cursor.execute(
                """INSERT INTO hasil_ujians
                   (ujian_harian_id, siswa_id, jumlah_benar, jumlah_salah, nilai, created_at, updated_at)
                   VALUES (%s, %s, %s, %s, %s, NOW(), NOW())""",
                (ujian_id, siswa_id, benar, salah, nilai),
            )
            hasil_dibuat += 1
    return ujian_dibuat, hasil_dibuat


def generate_perilaku(cursor, tanggal, siswa_per_kelas, gmk_list, positif_ids, negatif_ids):
    if not positif_ids and not negatif_ids:
        return 0
    # ambil 1 guru acak per kelas sebagai pencatat (guru yang mengajar kelas itu)
    guru_per_kelas = {}
    for gmk in gmk_list:
        guru_per_kelas.setdefault(gmk["kelas_id"], []).append(gmk["guru_id"])

    dibuat = 0
    for kelas_id, siswa_ids in siswa_per_kelas.items():
        guru_kandidat = guru_per_kelas.get(kelas_id)
        if not guru_kandidat:
            continue
        for siswa_id in siswa_ids:
            if not chance(PELUANG_PERILAKU):
                continue
            if chance(PROPORSI_PERILAKU_NEGATIF) and negatif_ids:
                perilaku_id = random.choice(negatif_ids)
            elif positif_ids:
                perilaku_id = random.choice(positif_ids)
            else:
                perilaku_id = random.choice(negatif_ids)
            guru_id = random.choice(guru_kandidat)
            cursor.execute(
                """INSERT INTO perilaku_siswas (siswa_id, perilaku_id, guru_id, tanggal, catatan, created_at, updated_at)
                   VALUES (%s, %s, %s, %s, NULL, NOW(), NOW())""",
                (siswa_id, perilaku_id, guru_id, tanggal),
            )
            dibuat += 1
    return dibuat


# =========================================================================
# MAIN
# =========================================================================

def generate_untuk_tanggal(cursor, tanggal, gmk_list, siswa_per_kelas, bab_per_gmk, materi_per_bab,
                            positif_ids, negatif_ids):
    if tanggal.weekday() >= 5:  # 5=Sabtu, 6=Minggu -> skip
        return

    n_absen = generate_absensi(cursor, tanggal, gmk_list, siswa_per_kelas)
    n_tugas, n_nilai = generate_tugas_dan_nilai(cursor, tanggal, gmk_list, siswa_per_kelas,
                                                 materi_per_bab, bab_per_gmk)
    n_ujian, n_hasil = generate_ujian_dan_hasil(cursor, tanggal, gmk_list, siswa_per_kelas, bab_per_gmk)
    n_perilaku = generate_perilaku(cursor, tanggal, siswa_per_kelas, gmk_list, positif_ids, negatif_ids)

    print(f"[{tanggal}] absensi:{n_absen}  tugas_baru:{n_tugas}  nilai_tugas:{n_nilai}  "
          f"ujian_baru:{n_ujian}  hasil_ujian:{n_hasil}  perilaku:{n_perilaku}")


def main():
    parser = argparse.ArgumentParser(description="Generator data simulasi EWS SMANSA")
    parser.add_argument("--days", type=int, default=1,
                         help="Jumlah hari ke belakang yang digenerate (default: 1, hanya hari ini)")
    parser.add_argument("--date", type=str, default=None,
                         help="Tanggal akhir simulasi, format YYYY-MM-DD (default: hari ini)")
    args = parser.parse_args()

    tanggal_akhir = date.fromisoformat(args.date) if args.date else date.today()
    daftar_tanggal = [tanggal_akhir - timedelta(days=i) for i in range(args.days)]
    daftar_tanggal.sort()

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        print("=== Menyiapkan data master pendukung ===")
        ensure_perilakus(cursor)
        ensure_babs_dan_materis(cursor)
        conn.commit()

        semester_id = get_semester_aktif(cursor)
        gmk_list = get_guru_mapel_kelas(cursor, semester_id)
        if not gmk_list:
            sys.exit("Tidak ada data di guru_mapel_kelas untuk semester aktif. Isi dulu penugasan gurunya.")

        siswa_per_kelas = get_siswa_by_kelas(cursor)
        bab_per_gmk = get_bab_by_gmk(cursor)
        materi_per_bab = get_materi_by_bab(cursor)
        positif_ids, negatif_ids = get_perilaku_ids(cursor)

        print(f"\n=== Generate data mentah untuk {len(daftar_tanggal)} hari ({daftar_tanggal[0]} s/d {daftar_tanggal[-1]}) ===")
        for tanggal in daftar_tanggal:
            generate_untuk_tanggal(cursor, tanggal, gmk_list, siswa_per_kelas,
                                    bab_per_gmk, materi_per_bab, positif_ids, negatif_ids)
            conn.commit()

        print("\nSelesai. Semua data mentah berhasil di-generate & disimpan ke database.")

    except Error as e:
        conn.rollback()
        sys.exit(f"Terjadi error, semua perubahan di-rollback: {e}")
    finally:
        cursor.close()
        conn.close()


if __name__ == "__main__":
    main()