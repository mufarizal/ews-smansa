#!/usr/bin/env python3
"""
data/tabel.py
=============
Tujuan: Menyediakan akses data dan tracking untuk simulator sekolah.

Tanggung jawab:
- Mengambil data master dan data jadwal aktif
- Menyiapkan tabel pendukung simulator bila belum ada
- Menyediakan tracking agar jadwal tidak diproses dua kali
"""

from datetime import datetime, time, timedelta

from konfigurasi.pengaturan import (
    JUMLAH_BAB_PER_MAPEL,
    JUMLAH_MATERI_PER_BAB,
    MASTER_PERILAKU,
    NAMA_TABEL_TRACKING_SIMULASI,
)

NAMA_HARI = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]


def normalisasi_waktu(nilai_waktu):
    """
    Mengubah nilai waktu dari MySQL menjadi datetime.time.

    Parameter:
        nilai_waktu: Bisa berupa datetime.time, datetime.timedelta, atau string.
    """
    if isinstance(nilai_waktu, time):
        return nilai_waktu

    if isinstance(nilai_waktu, timedelta):
        total_detik = int(nilai_waktu.total_seconds())
        jam = (total_detik // 3600) % 24
        menit = (total_detik % 3600) // 60
        detik = total_detik % 60
        return time(jam, menit, detik)

    if isinstance(nilai_waktu, str):
        bagian = nilai_waktu.split(":")
        if len(bagian) >= 2:
            jam = int(bagian[0])
            menit = int(bagian[1])
            detik = int(bagian[2]) if len(bagian) > 2 else 0
            return time(jam, menit, detik)

    return nilai_waktu


def pastikan_tabel_pendukung(cursor):
    """
    Memastikan tabel pendukung simulator tersedia.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    pastikan_perilaku(cursor)
    pastikan_bab_dan_materi(cursor)
    pastikan_tabel_tracking(cursor)


def pastikan_perilaku(cursor):
    """
    Mengisi master perilaku jika tabel masih kosong.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT COUNT(*) AS total FROM perilakus")
    total = cursor.fetchone()["total"]
    if total > 0:
        return

    for nama, jenis, poin in MASTER_PERILAKU:
        cursor.execute(
            """
            INSERT INTO perilakus (nama_perilaku, jenis, poin, status_aktif, created_at, updated_at)
            VALUES (%s, %s, %s, 1, NOW(), NOW())
            """,
            (nama, jenis, poin),
        )


def pastikan_bab_dan_materi(cursor):
    """
    Membuat bab dan materi untuk GMK yang belum punya data pendukung.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        """
        SELECT gmk.id AS gmk_id, m.nama AS nama_mapel
        FROM guru_mapel_kelas gmk
        JOIN mapels m ON m.id = gmk.mapel_id
        WHERE gmk.id NOT IN (SELECT DISTINCT guru_mapel_kelas_id FROM babs)
        """
    )
    target = cursor.fetchall()
    for baris in target:
        gmk_id = baris["gmk_id"]
        nama_mapel = baris["nama_mapel"]
        for urutan_bab in range(1, JUMLAH_BAB_PER_MAPEL + 1):
            cursor.execute(
                """
                INSERT INTO babs (guru_mapel_kelas_id, nama_bab, urutan, deskripsi, created_at, updated_at)
                VALUES (%s, %s, %s, %s, NOW(), NOW())
                """,
                (gmk_id, f"Bab {urutan_bab} - {nama_mapel}", urutan_bab, f"Materi bab {urutan_bab} untuk {nama_mapel}"),
            )
            bab_id = cursor.lastrowid
            for urutan_materi in range(1, JUMLAH_MATERI_PER_BAB + 1):
                cursor.execute(
                    """
                    INSERT INTO materis (bab_id, judul, isi_materi, urutan, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, NOW(), NOW())
                    """,
                    (bab_id, f"Materi {urutan_materi} Bab {urutan_bab}", f"Ringkasan materi {urutan_materi} bab {urutan_bab}.", urutan_materi),
                )


def pastikan_tabel_tracking(cursor):
    """
    Membuat tabel tracking jadwal yang sudah diproses.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        f"""
        CREATE TABLE IF NOT EXISTS {NAMA_TABEL_TRACKING_SIMULASI} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            jadwal_id BIGINT UNSIGNED NOT NULL,
            semester_id BIGINT UNSIGNED NOT NULL,
            tanggal DATE NOT NULL,
            jam_mulai TIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'processing',
            pesan_error TEXT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            finished_at TIMESTAMP NULL DEFAULT NULL,
            UNIQUE KEY uq_schedule_run (jadwal_id, tanggal, jam_mulai)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """
    )


def ambil_semester_aktif(cursor):
    """
    Mengambil semester aktif.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id FROM semesters WHERE is_active = 1 LIMIT 1")
    baris = cursor.fetchone()
    if not baris:
        raise SystemExit("Tidak ada semester aktif. Set satu semester sebagai is_active=1.")
    return baris["id"]


def ambil_info_semester_aktif(cursor):
    """
    Mengambil data semester aktif beserta tanggal mulai dan selesai.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        """
        SELECT id, nama, tanggal_mulai, tanggal_selesai
        FROM semesters
        WHERE is_active = 1
        LIMIT 1
        """
    )
    baris = cursor.fetchone()
    if not baris:
        raise SystemExit("Tidak ada semester aktif. Set satu semester sebagai is_active=1.")
    return baris


def ambil_tanggal_terakhir_diproses(cursor, semester_id):
    """
    Mengambil tanggal terakhir yang sudah diproses untuk semester aktif.

    Parameter:
        cursor: MySQL cursor dictionary.
        semester_id (int): Semester aktif.
    """
    cursor.execute(
        f"""
        SELECT MAX(tanggal) AS tanggal_terakhir
        FROM {NAMA_TABEL_TRACKING_SIMULASI}
        WHERE semester_id = %s AND status = 'done'
        """,
        (semester_id,),
    )
    baris = cursor.fetchone() or {}
    return baris.get("tanggal_terakhir")


def ambil_siswa_per_kelas(cursor):
    """
    Mengambil siswa dan mengelompokkannya per kelas.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, kelas_id FROM siswas")
    hasil = {}
    for baris in cursor.fetchall():
        hasil.setdefault(baris["kelas_id"], []).append(baris["id"])
    return hasil


def ambil_bab_per_gmk(cursor):
    """
    Mengambil bab dan mengelompokkannya per GMK.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, guru_mapel_kelas_id FROM babs")
    hasil = {}
    for baris in cursor.fetchall():
        hasil.setdefault(baris["guru_mapel_kelas_id"], []).append(baris["id"])
    return hasil


def ambil_materi_per_bab(cursor):
    """
    Mengambil materi dan mengelompokkannya per bab.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, bab_id FROM materis")
    hasil = {}
    for baris in cursor.fetchall():
        hasil.setdefault(baris["bab_id"], []).append(baris["id"])
    return hasil


def ambil_id_perilaku(cursor):
    """
    Mengambil ID perilaku positif dan negatif.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, jenis FROM perilakus WHERE status_aktif = 1")
    positif = []
    negatif = []
    for baris in cursor.fetchall():
        if baris["jenis"] == "positif":
            positif.append(baris["id"])
        else:
            negatif.append(baris["id"])
    return positif, negatif


def ambil_jadwal_sedang_berjalan(cursor, semester_id, nama_hari, jam_sekarang):
    """
    Mengambil semua jadwal aktif pada hari dan jam tertentu.

    Parameter:
        cursor: MySQL cursor dictionary.
        semester_id (int): Semester aktif.
        nama_hari (str): Nama hari dalam bahasa Indonesia.
        jam_sekarang (str): Jam sekarang format HH:MM:SS.
    """
    cursor.execute(
        """
        SELECT
            j.id AS jadwal_id,
            j.kelas_id,
            j.jam_mulai,
            j.jam_selesai,
            gmk.id AS gmk_id,
            gmk.guru_id,
            gmk.mapel_id
        FROM jadwals j
                JOIN guru_mapel_kelas gmk
                    ON gmk.guru_id = j.guru_id
                 AND gmk.mapel_id = j.mapel_id
                 AND gmk.kelas_id = j.kelas_id
                 AND gmk.semester_id = j.semester_id
                WHERE j.semester_id = %s
          AND LOWER(j.hari) = LOWER(%s)
          AND j.jam_mulai <= %s
          AND j.jam_selesai >= %s
        ORDER BY j.jam_mulai ASC, j.id ASC
        """,
        (semester_id, nama_hari, jam_sekarang, jam_sekarang),
    )
    hasil = cursor.fetchall()
    for baris in hasil:
        baris["jam_mulai"] = normalisasi_waktu(baris["jam_mulai"])
        baris["jam_selesai"] = normalisasi_waktu(baris["jam_selesai"])
    return hasil


def ambil_jadwal_harian(cursor, semester_id, nama_hari):
    """
    Mengambil semua jadwal pada hari tertentu untuk mode bootstrap.

    Parameter:
        cursor: MySQL cursor dictionary.
        semester_id (int): Semester aktif.
        nama_hari (str): Nama hari dalam bahasa Indonesia.
    """
    cursor.execute(
        """
        SELECT
            j.id AS jadwal_id,
            j.kelas_id,
            j.jam_mulai,
            j.jam_selesai,
            gmk.id AS gmk_id,
            gmk.guru_id,
            gmk.mapel_id
        FROM jadwals j
                JOIN guru_mapel_kelas gmk
                    ON gmk.guru_id = j.guru_id
                 AND gmk.mapel_id = j.mapel_id
                 AND gmk.kelas_id = j.kelas_id
                 AND gmk.semester_id = j.semester_id
                WHERE j.semester_id = %s
          AND LOWER(j.hari) = LOWER(%s)
        ORDER BY j.jam_mulai ASC, j.id ASC
        """,
        (semester_id, nama_hari),
    )
    hasil = cursor.fetchall()
    for baris in hasil:
        baris["jam_mulai"] = normalisasi_waktu(baris["jam_mulai"])
        baris["jam_selesai"] = normalisasi_waktu(baris["jam_selesai"])
    return hasil


def ambil_label_tabel(cursor, nama_tabel, id_baris):
    """
    Mengambil label tampilan dari tabel referensi secara fleksibel.

    Parameter:
        cursor: MySQL cursor dictionary.
        nama_tabel (str): Nama tabel referensi.
        id_baris (int): ID baris.
    """
    cursor.execute(f"SELECT * FROM {nama_tabel} WHERE id = %s LIMIT 1", (id_baris,))
    baris = cursor.fetchone() or {}
    for kunci in ("nama", "nama_kelas", "nama_mapel", "name", "judul", "kode", "label"):
        nilai = baris.get(kunci)
        if nilai:
            return nilai
    return f"{nama_tabel}:{id_baris}"


def ambil_status_jadwal(cursor, jadwal_id, tanggal, jam_mulai):
    """
    Mengambil status tracking jadwal.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        f"""
        SELECT id, status
        FROM {NAMA_TABEL_TRACKING_SIMULASI}
        WHERE jadwal_id = %s AND tanggal = %s AND jam_mulai = %s
        LIMIT 1
        """,
        (jadwal_id, tanggal, jam_mulai),
    )
    return cursor.fetchone()


def klaim_jadwal(cursor, jadwal_id, semester_id, tanggal, jam_mulai):
    """
    Menandai jadwal sebagai sedang diproses jika belum pernah diproses.

    Return:
        id tracking row jika berhasil claim, atau None jika sudah pernah ada.
    """
    status = ambil_status_jadwal(cursor, jadwal_id, tanggal, jam_mulai)
    if status:
        return None

    cursor.execute(
        f"""
        INSERT INTO {NAMA_TABEL_TRACKING_SIMULASI}
            (jadwal_id, semester_id, tanggal, jam_mulai, status, created_at, updated_at)
        VALUES (%s, %s, %s, %s, 'processing', NOW(), NOW())
        """,
        (jadwal_id, semester_id, tanggal, jam_mulai),
    )
    return cursor.lastrowid


def selesaikan_jadwal(cursor, tracking_id):
    """
    Menandai jadwal sebagai selesai.

    Parameter:
        cursor: MySQL cursor dictionary.
        tracking_id (int): ID tracking row.
    """
    cursor.execute(
        f"""
        UPDATE {NAMA_TABEL_TRACKING_SIMULASI}
        SET status = 'done', finished_at = NOW(), updated_at = NOW()
        WHERE id = %s
        """,
        (tracking_id,),
    )


def gagal_jadwal(cursor, tracking_id, pesan_error):
    """
    Menandai jadwal sebagai gagal.

    Parameter:
        cursor: MySQL cursor dictionary.
        tracking_id (int): ID tracking row.
        pesan_error (str): Pesan error singkat.
    """
    cursor.execute(
        f"""
        UPDATE {NAMA_TABEL_TRACKING_SIMULASI}
        SET status = 'failed', pesan_error = %s, updated_at = NOW()
        WHERE id = %s
        """,
        (pesan_error[:2000], tracking_id),
    )


def nama_hari_dari_tanggal(tanggal):
    """
    Mengubah tanggal menjadi nama hari Indonesia.

    Parameter:
        tanggal (date|datetime): Tanggal acuan.
    """
    weekday = tanggal.weekday()
    return NAMA_HARI[weekday]
