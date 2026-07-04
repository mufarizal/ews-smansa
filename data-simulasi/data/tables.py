#!/usr/bin/env python3
"""
data/tables.py
==============
Tujuan: Menyediakan akses data dan pemetaan tabel untuk simulator.

Tanggung jawab:
- Mengambil data master dan data jadwal aktif
- Menyiapkan tabel pendukung simulator bila belum ada
- Menyediakan helper tracking agar jadwal tidak diproses dua kali
"""

from datetime import datetime

from config.settings import (
    MASTER_PERILAKU,
    BABS_PER_MAPEL,
    MATERIS_PER_BAB,
    SIMULATION_TRACKING_TABLE,
)

DAY_NAMES_ID = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]


def ensure_support_tables(cursor):
    """
    Memastikan tabel pendukung simulator tersedia.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    ensure_perilakus(cursor)
    ensure_babs_dan_materis(cursor)
    ensure_tracking_table(cursor)


def ensure_perilakus(cursor):
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


def ensure_babs_dan_materis(cursor):
    """
    Membuat bab dan materi untuk GMK yang belum punya data pendukung.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        """
        SELECT gmk.id AS gmk_id, m.nama AS mapel_nama
        FROM guru_mapel_kelas gmk
        JOIN mapels m ON m.id = gmk.mapel_id
        WHERE gmk.id NOT IN (SELECT DISTINCT guru_mapel_kelas_id FROM babs)
        """
    )
    targets = cursor.fetchall()
    for row in targets:
        gmk_id = row["gmk_id"]
        mapel_nama = row["mapel_nama"]
        for bab_urutan in range(1, BABS_PER_MAPEL + 1):
            cursor.execute(
                """
                INSERT INTO babs (guru_mapel_kelas_id, nama_bab, urutan, deskripsi, created_at, updated_at)
                VALUES (%s, %s, %s, %s, NOW(), NOW())
                """,
                (gmk_id, f"Bab {bab_urutan} - {mapel_nama}", bab_urutan, f"Materi bab {bab_urutan} untuk {mapel_nama}"),
            )
            bab_id = cursor.lastrowid
            for materi_urutan in range(1, MATERIS_PER_BAB + 1):
                cursor.execute(
                    """
                    INSERT INTO materis (bab_id, judul, isi_materi, urutan, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, NOW(), NOW())
                    """,
                    (bab_id, f"Materi {materi_urutan} Bab {bab_urutan}", f"Ringkasan materi {materi_urutan} bab {bab_urutan}.", materi_urutan),
                )


def ensure_tracking_table(cursor):
    """
    Membuat tabel tracking jadwal yang sudah diproses.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        f"""
        CREATE TABLE IF NOT EXISTS {SIMULATION_TRACKING_TABLE} (
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


def get_active_semester_id(cursor):
    """
    Mengambil semester aktif.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id FROM semesters WHERE is_active = 1 LIMIT 1")
    row = cursor.fetchone()
    if not row:
        raise SystemExit("Tidak ada semester aktif. Set satu semester sebagai is_active=1.")
    return row["id"]


def get_students_by_class(cursor):
    """
    Mengambil siswa dan mengelompokkannya per kelas.

    Parameter:
        cursor: MySQL cursor dictionary.

    Return:
        dict {kelas_id: [siswa_id, ...]}
    """
    cursor.execute("SELECT id, kelas_id FROM siswas")
    grouped = {}
    for row in cursor.fetchall():
        grouped.setdefault(row["kelas_id"], []).append(row["id"])
    return grouped


def get_babs_by_gmk(cursor):
    """
    Mengambil bab dan mengelompokkannya per GMK.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, guru_mapel_kelas_id FROM babs")
    grouped = {}
    for row in cursor.fetchall():
        grouped.setdefault(row["guru_mapel_kelas_id"], []).append(row["id"])
    return grouped


def get_materis_by_bab(cursor):
    """
    Mengambil materi dan mengelompokkannya per bab.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, bab_id FROM materis")
    grouped = {}
    for row in cursor.fetchall():
        grouped.setdefault(row["bab_id"], []).append(row["id"])
    return grouped


def get_perilaku_ids(cursor):
    """
    Mengambil ID perilaku positif dan negatif.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute("SELECT id, jenis FROM perilakus WHERE status_aktif = 1")
    positif = []
    negatif = []
    for row in cursor.fetchall():
        if row["jenis"] == "positif":
            positif.append(row["id"])
        else:
            negatif.append(row["id"])
    return positif, negatif


def get_schedule_rows(cursor, semester_id, day_name, current_time):
    """
    Mengambil semua jadwal aktif pada hari dan jam tertentu.

    Parameter:
        cursor: MySQL cursor dictionary.
        semester_id (int): Semester aktif.
        day_name (str): Nama hari dalam bahasa Indonesia.
        current_time (str): Jam sekarang format HH:MM:SS.
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
        JOIN guru_mapel_kelas gmk ON gmk.id = j.guru_mapel_kelas_id
                WHERE gmk.semester_id = %s
                    AND LOWER(j.hari) = LOWER(%s)
          AND j.jam_mulai <= %s
          AND j.jam_selesai >= %s
        ORDER BY j.jam_mulai ASC, j.id ASC
        """,
        (semester_id, day_name, current_time, current_time),
    )
    return cursor.fetchall()


def get_schedule_rows_for_date(cursor, semester_id, day_name):
    """
    Mengambil semua jadwal pada hari tertentu untuk mode bootstrap.

    Parameter:
        cursor: MySQL cursor dictionary.
        semester_id (int): Semester aktif.
        day_name (str): Nama hari dalam bahasa Indonesia.
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
        JOIN guru_mapel_kelas gmk ON gmk.id = j.guru_mapel_kelas_id
                WHERE gmk.semester_id = %s
                    AND LOWER(j.hari) = LOWER(%s)
        ORDER BY j.jam_mulai ASC, j.id ASC
        """,
        (semester_id, day_name),
    )
    return cursor.fetchall()


def get_table_label(cursor, table_name, row_id):
    """
    Mengambil label tampilan dari tabel referensi secara fleksibel.

    Parameter:
        cursor: MySQL cursor dictionary.
        table_name (str): Nama tabel referensi.
        row_id (int): ID row.
    """
    cursor.execute(f"SELECT * FROM {table_name} WHERE id = %s LIMIT 1", (row_id,))
    row = cursor.fetchone() or {}
    for key in ("nama", "nama_kelas", "nama_mapel", "name", "judul", "kode", "label"):
        value = row.get(key)
        if value:
            return value
    return f"{table_name}:{row_id}"


def get_run_status(cursor, jadwal_id, tanggal, jam_mulai):
    """
    Mengambil status tracking jadwal.

    Parameter:
        cursor: MySQL cursor dictionary.
    """
    cursor.execute(
        f"""
        SELECT id, status
        FROM {SIMULATION_TRACKING_TABLE}
        WHERE jadwal_id = %s AND tanggal = %s AND jam_mulai = %s
        LIMIT 1
        """,
        (jadwal_id, tanggal, jam_mulai),
    )
    return cursor.fetchone()


def claim_schedule_run(cursor, jadwal_id, semester_id, tanggal, jam_mulai):
    """
    Menandai jadwal sebagai sedang diproses jika belum pernah diproses.

    Return:
        id tracking row jika berhasil claim, atau None jika sudah pernah ada.
    """
    existing = get_run_status(cursor, jadwal_id, tanggal, jam_mulai)
    if existing:
        return None

    cursor.execute(
        f"""
        INSERT INTO {SIMULATION_TRACKING_TABLE}
            (jadwal_id, semester_id, tanggal, jam_mulai, status, created_at, updated_at)
        VALUES (%s, %s, %s, %s, 'processing', NOW(), NOW())
        """,
        (jadwal_id, semester_id, tanggal, jam_mulai),
    )
    return cursor.lastrowid


def finish_schedule_run(cursor, tracking_id):
    """
    Menandai jadwal sebagai selesai.

    Parameter:
        cursor: MySQL cursor dictionary.
        tracking_id (int): ID tracking row.
    """
    cursor.execute(
        f"""
        UPDATE {SIMULATION_TRACKING_TABLE}
        SET status = 'done', finished_at = NOW(), updated_at = NOW()
        WHERE id = %s
        """,
        (tracking_id,),
    )


def fail_schedule_run(cursor, tracking_id, error_message):
    """
    Menandai jadwal sebagai gagal.

    Parameter:
        cursor: MySQL cursor dictionary.
        tracking_id (int): ID tracking row.
        error_message (str): Pesan error singkat.
    """
    cursor.execute(
        f"""
        UPDATE {SIMULATION_TRACKING_TABLE}
        SET status = 'failed', pesan_error = %s, updated_at = NOW()
        WHERE id = %s
        """,
        (error_message[:2000], tracking_id),
    )


def day_name_from_date(date_value):
    """
    Mengubah tanggal menjadi nama hari Indonesia.

    Parameter:
        date_value (date|datetime): Tanggal acuan.
    """
    if isinstance(date_value, datetime):
        weekday = date_value.weekday()
    else:
        weekday = date_value.weekday()
    return DAY_NAMES_ID[weekday]
