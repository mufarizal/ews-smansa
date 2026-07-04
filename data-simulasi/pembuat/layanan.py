#!/usr/bin/env python3
"""
pembuat/layanan.py
==================
Tujuan: Menjalankan simulasi aktivitas sekolah per jadwal.

Tanggung jawab:
- Memproses seluruh jadwal aktif pada slot waktu tertentu
- Menghasilkan absensi, tugas, ujian, dan perilaku
- Menjaga idempotensi lewat tabel tracking jadwal
"""

import random
import time
from datetime import date, datetime, timedelta

from bantuan.acak import peluang, pilih_berbobot
from bantuan.log import tulis_log
from konfigurasi.pengaturan import (
    BOBOT_ABSENSI,
    JEDA_LOOP_DETIK,
    JUMLAH_SOAL_UJIAN,
    PELUANG_PERILAKU,
    PELUANG_PENGERJAAN_TUGAS,
    PELUANG_TUGAS,
    PELUANG_UJIAN,
    PROPORSI_PERILAKU_NEGATIF,
    PROFIL_ABSENSI,
    PROFIL_AKADEMIK_VARIAN,
)
from koneksi.database import koneksi_cursor
from data.tabel import (
    ambil_bab_per_gmk,
    ambil_id_perilaku,
    ambil_jadwal_harian,
    ambil_jadwal_sedang_berjalan,
    ambil_label_tabel,
    ambil_materi_per_bab,
    ambil_info_semester_aktif,
    ambil_tanggal_terakhir_diproses,
    ambil_siswa_per_kelas,
    gagal_jadwal,
    pastikan_tabel_pendukung,
    nama_hari_dari_tanggal,
    klaim_jadwal,
    selesaikan_jadwal,
)


def buat_absensi(cursor, tanggal, jadwal, siswa_ids):
    """
    Membuat absensi mapel untuk seluruh siswa pada satu jadwal.

    Parameter:
        cursor: MySQL cursor dictionary.
        tanggal (date): Tanggal simulasi.
        jadwal (dict): Data jadwal aktif.
        siswa_ids (list): Daftar siswa kelas terkait.
    """
    total = 0
    for siswa_id in siswa_ids:
        profil = PROFIL_ABSENSI["normal"]
        if siswa_id % 100 < 20:
            profil = PROFIL_ABSENSI["premium"]
        elif siswa_id % 100 < 50:
            profil = PROFIL_ABSENSI["normal"]
        elif siswa_id % 100 < 80:
            profil = PROFIL_ABSENSI["preman"]
        else:
            profil = PROFIL_ABSENSI["aktif"]
        
        status = pilih_berbobot(profil)
        terlambat_menit = random.randint(1, 20) if status == "terlambat" else 0
        jam_masuk = None
        if status in ("hadir", "terlambat"):
            jam_masuk = f"{tanggal.strftime('%Y-%m-%d')} {jadwal['jam_mulai']}"

cursor.execute(
             """
             INSERT INTO absensis
                 (siswa_id, tanggal, tipe, guru_id, mapel_id, status, jam_masuk, terlambat_menit, sudah_disetujui, is_simulated, created_at, updated_at)
             VALUES (%s, %s, 'mapel', %s, %s, %s, %s, %s, 1, 1, NOW(), NOW())
             """,
             (siswa_id, tanggal, jadwal["guru_id"], jadwal["mapel_id"], status, jam_masuk, terlambat_menit),
         )
        total += 1
    return total


def buat_tugas_dan_nilai(cursor, tanggal, jadwal, siswa_ids, bab_ids, materi_per_bab):
    """
    Membuat tugas dan nilai tugas jika probabilitasnya terpenuhi.

    Parameter:
        cursor: MySQL cursor dictionary.
        tanggal (date): Tanggal simulasi.
        jadwal (dict): Data jadwal aktif.
        siswa_ids (list): Daftar siswa kelas terkait.
        bab_ids (list): Daftar bab untuk GMK.
        materi_per_bab (dict): Daftar materi per bab.
    """
    if not bab_ids or not peluang(PELUANG_TUGAS):
        return 0, 0

    bab_id = random.choice(bab_ids)
    materi_ids = materi_per_bab.get(bab_id, [])
    if not materi_ids:
        return 0, 0

    materi_id = random.choice(materi_ids)
    tanggal_deadline = tanggal + timedelta(days=random.randint(3, 10))
    cursor.execute(
        """
        INSERT INTO tugas
            (guru_mapel_kelas_id, materi_id, judul, deskripsi, tanggal_tugas, tanggal_deadline, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
        """,
        (
            jadwal["gmk_id"],
            materi_id,
            f"Tugas {tanggal.strftime('%d-%m-%Y')}",
            "Tugas dibuat otomatis oleh simulator sekolah.",
            tanggal,
            tanggal_deadline,
        ),
    )
    tugas_id = cursor.lastrowid

    jumlah_nilai = 0
    for siswa_id in siswa_ids:
        if peluang(PELUANG_PENGERJAAN_TUGAS):
            if siswa_id % 100 < 20:
                nilai_range = PROFIL_AKADEMIK_VARIAN["bagus"]
            elif siswa_id % 100 < 50:
                nilai_range = PROFIL_AKADEMIK_VARIAN["sedang"]
            else:
                nilai_range = PROFIL_AKADEMIK_VARIAN["lemah"]
            
            nilai = round(random.uniform(nilai_range[0], nilai_range[1]), 2)
            status = "mengerjakan"
        else:
            nilai = None
            status = "tidak_mengerjakan"
cursor.execute(
             """
             INSERT INTO nilai_tugas (tugas_id, siswa_id, nilai, status, is_simulated, created_at, updated_at)
             VALUES (%s, %s, %s, %s, 1, NOW(), NOW())
             """,
             (tugas_id, siswa_id, nilai, status),
         )
        jumlah_nilai += 1
    return 1, jumlah_nilai


def buat_ujian_dan_hasil(cursor, tanggal, jadwal, siswa_ids, bab_ids):
    """
    Membuat ujian harian dan hasil ujian jika probabilitasnya terpenuhi.

    Parameter:
        cursor: MySQL cursor dictionary.
        tanggal (date): Tanggal simulasi.
        jadwal (dict): Data jadwal aktif.
        siswa_ids (list): Daftar siswa kelas terkait.
        bab_ids (list): Daftar bab untuk GMK.
    """
    if not bab_ids or not peluang(PELUANG_UJIAN):
        return 0, 0

    bab_id = random.choice(bab_ids)
    cursor.execute(
        """
        INSERT INTO ujian_harians
            (guru_mapel_kelas_id, bab_id, judul, tanggal_ujian, durasi_menit, status, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, 'selesai', NOW(), NOW())
        """,
        (
            jadwal["gmk_id"],
            bab_id,
            f"Ulangan Harian {tanggal.strftime('%d-%m-%Y')}",
            tanggal,
            60,
        ),
    )
    ujian_id = cursor.lastrowid

    jumlah_hasil = 0
    for siswa_id in siswa_ids:
        benar = random.randint(3, JUMLAH_SOAL_UJIAN)
        salah = JUMLAH_SOAL_UJIAN - benar
        nilai = round((benar / JUMLAH_SOAL_UJIAN) * 100, 2)
cursor.execute(
             """
             INSERT INTO hasil_ujians
                 (ujian_harian_id, siswa_id, jumlah_benar, jumlah_salah, nilai, is_simulated, created_at, updated_at)
             VALUES (%s, %s, %s, %s, %s, 1, NOW(), NOW())
             """,
             (ujian_id, siswa_id, benar, salah, nilai),
         )
        jumlah_hasil += 1
    return 1, jumlah_hasil


def buat_perilaku(cursor, tanggal, jadwal, siswa_ids, positif_ids, negatif_ids):
    """
    Membuat catatan perilaku siswa jika probabilitasnya terpenuhi.

    Parameter:
        cursor: MySQL cursor dictionary.
        tanggal (date): Tanggal simulasi.
        jadwal (dict): Data jadwal aktif.
        siswa_ids (list): Daftar siswa kelas terkait.
        positif_ids (list): ID perilaku positif.
        negatif_ids (list): ID perilaku negatif.
    """
    if not siswa_ids or (not positif_ids and not negatif_ids):
        return 0

    total = 0
    for siswa_id in siswa_ids:
        profil_peluang = PELUANG_PERILAKU
        if siswa_id % 100 < 30:
            profil_peluang = int(PELUANG_PERILAKU * 1.5)
        elif siswa_id % 100 < 70:
            profil_peluang = PELUANG_PERILAKU
        else:
            profil_peluang = int(PELUANG_PERILAKU * 0.5)
        
        if not peluang(profil_peluang):
            continue
        if peluang(PROPORSI_PERILAKU_NEGATIF) and negatif_ids:
            perilaku_id = random.choice(negatif_ids)
        elif positif_ids:
            perilaku_id = random.choice(positif_ids)
        else:
            perilaku_id = random.choice(negatif_ids)

cursor.execute(
             """
             INSERT INTO perilaku_siswas (siswa_id, perilaku_id, guru_id, tanggal, catatan, is_simulated, created_at, updated_at)
             VALUES (%s, %s, %s, %s, NULL, 1, NOW(), NOW())
             """,
             (siswa_id, perilaku_id, jadwal["guru_id"], tanggal),
         )
        total += 1
    return total


def proses_jadwal(cursor, tanggal, jadwal, siswa_per_kelas, bab_per_gmk, materi_per_bab, positif_ids, negatif_ids, semester_id):
    """
    Memproses satu jadwal aktif secara penuh.

    Parameter:
        cursor: MySQL cursor dictionary.
        tanggal (datetime): Waktu simulasi.
        jadwal (dict): Data jadwal.
        siswa_per_kelas (dict): Siswa per kelas.
        bab_per_gmk (dict): Bab per GMK.
        materi_per_bab (dict): Materi per bab.
        positif_ids (list): ID perilaku positif.
        negatif_ids (list): ID perilaku negatif.
        semester_id (int): Semester aktif.
    """
    tracking_id = klaim_jadwal(cursor, jadwal["jadwal_id"], semester_id, tanggal.date(), jadwal["jam_mulai"])
    if not tracking_id:
        return None

    siswa_ids = siswa_per_kelas.get(jadwal["kelas_id"], [])
    nama_kelas = ambil_label_tabel(cursor, "kelas", jadwal["kelas_id"])
    nama_guru = ambil_label_tabel(cursor, "gurus", jadwal["guru_id"])
    nama_mapel = ambil_label_tabel(cursor, "mapels", jadwal["mapel_id"])

    try:
        jumlah_absensi = buat_absensi(cursor, tanggal.date(), jadwal, siswa_ids)
        jumlah_tugas, jumlah_nilai = buat_tugas_dan_nilai(
            cursor,
            tanggal.date(),
            jadwal,
            siswa_ids,
            bab_per_gmk.get(jadwal["gmk_id"], []),
            materi_per_bab,
        )
        jumlah_ujian, jumlah_hasil = buat_ujian_dan_hasil(
            cursor,
            tanggal.date(),
            jadwal,
            siswa_ids,
            bab_per_gmk.get(jadwal["gmk_id"], []),
        )
        jumlah_perilaku = buat_perilaku(cursor, tanggal.date(), jadwal, siswa_ids, positif_ids, negatif_ids)
        selesaikan_jadwal(cursor, tracking_id)
        tulis_log(
            f"{tanggal.strftime('%H:%M')} | jadwal {jadwal['jadwal_id']} | kelas {nama_kelas} | mapel {nama_mapel} | guru {nama_guru} | siswa {len(siswa_ids)} | absensi {jumlah_absensi} | tugas {jumlah_tugas} | nilai {jumlah_nilai} | ujian {jumlah_ujian} | hasil {jumlah_hasil} | perilaku {jumlah_perilaku}"
        )
        return {
            "absensi": jumlah_absensi,
            "tugas": jumlah_tugas,
            "nilai": jumlah_nilai,
            "ujian": jumlah_ujian,
            "hasil": jumlah_hasil,
            "perilaku": jumlah_perilaku,
        }
    except Exception as exc:
        gagal_jadwal(cursor, tracking_id, str(exc))
        raise


def sinkronisasi_terlewat(connection, cursor, semester_info, siswa_per_kelas, bab_per_gmk, materi_per_bab, positif_ids, negatif_ids):
    """
    Mengejar jadwal yang terlewat sejak checkpoint terakhir sampai hari ini.

    Parameter:
        connection: Koneksi database aktif.
        cursor: MySQL cursor dictionary.
        semester_info (dict): Data semester aktif.
        siswa_per_kelas (dict): Siswa per kelas.
        bab_per_gmk (dict): Bab per GMK.
        materi_per_bab (dict): Materi per bab.
        positif_ids (list): ID perilaku positif.
        negatif_ids (list): ID perilaku negatif.
    """
    semester_id = semester_info["id"]
    tanggal_mulai_semester = semester_info["tanggal_mulai"]
    tanggal_hari_ini = date.today()
    tanggal_terakhir = ambil_tanggal_terakhir_diproses(cursor, semester_id)

    if tanggal_terakhir:
        tanggal_mulai_sync = max(tanggal_mulai_semester, tanggal_terakhir + timedelta(days=1))
    else:
        tanggal_mulai_sync = tanggal_mulai_semester

    tanggal_akhir_sync = tanggal_hari_ini - timedelta(days=1)

    if tanggal_mulai_sync <= tanggal_akhir_sync:
        for tanggal in (tanggal_mulai_sync + timedelta(days=offset) for offset in range((tanggal_akhir_sync - tanggal_mulai_sync).days + 1)):
            nama_hari = nama_hari_dari_tanggal(tanggal)
            jadwal_list = ambil_jadwal_harian(cursor, semester_id, nama_hari)

            for jadwal in jadwal_list:
                waktu_jadwal = datetime.combine(tanggal, jadwal["jam_mulai"])
                proses_jadwal(
                    cursor,
                    waktu_jadwal,
                    jadwal,
                    siswa_per_kelas,
                    bab_per_gmk,
                    materi_per_bab,
                    positif_ids,
                    negatif_ids,
                    semester_id,
                )

            connection.commit()

    nama_hari_hari_ini = nama_hari_dari_tanggal(tanggal_hari_ini)
    jadwal_hari_ini = ambil_jadwal_harian(cursor, semester_id, nama_hari_hari_ini)

    for jadwal in jadwal_hari_ini:
        if jadwal["jam_mulai"] > datetime.now().time():
            continue

        waktu_jadwal = datetime.combine(tanggal_hari_ini, jadwal["jam_mulai"])
        proses_jadwal(
            cursor,
            waktu_jadwal,
            jadwal,
            siswa_per_kelas,
            bab_per_gmk,
            materi_per_bab,
            positif_ids,
            negatif_ids,
            semester_id,
        )

    connection.commit()


def jalankan_bootstrap(hari=30, tanggal_akhir=None):
    """
    Menjalankan backfill historis untuk sejumlah hari ke belakang.

    Parameter:
        hari (int): Jumlah hari yang digenerate.
        tanggal_akhir (date|None): Tanggal terakhir simulasi.
    """
    with koneksi_cursor(dictionary=True) as (connection, cursor):
        pastikan_tabel_pendukung(cursor)
        connection.commit()

        semester_id = ambil_semester_aktif(cursor)
        siswa_per_kelas = ambil_siswa_per_kelas(cursor)
        bab_per_gmk = ambil_bab_per_gmk(cursor)
        materi_per_bab = ambil_materi_per_bab(cursor)
        positif_ids, negatif_ids = ambil_id_perilaku(cursor)

        akhir = tanggal_akhir or datetime.now().date()
        daftar_tanggal = [akhir - timedelta(days=offset) for offset in range(hari - 1, -1, -1)]

        for tanggal in daftar_tanggal:
            nama_hari = nama_hari_dari_tanggal(tanggal)
            jadwal_list = ambil_jadwal_harian(cursor, semester_id, nama_hari)
            for jadwal in jadwal_list:
                waktu_jadwal = datetime.combine(tanggal, jadwal["jam_mulai"])
                proses_jadwal(
                    cursor,
                    waktu_jadwal,
                    jadwal,
                    siswa_per_kelas,
                    bab_per_gmk,
                    materi_per_bab,
                    positif_ids,
                    negatif_ids,
                    semester_id,
                )
            connection.commit()


def jalankan_simulasi(jeda_detik=JEDA_LOOP_DETIK):
    """
    Menjalankan simulator tanpa henti.

    Parameter:
        jeda_detik (int): Jeda antar pengecekan jadwal aktif.
    """
    with koneksi_cursor(dictionary=True) as (connection, cursor):
        pastikan_tabel_pendukung(cursor)
        connection.commit()

        semester_info = ambil_info_semester_aktif(cursor)
        semester_id = semester_info["id"]
        siswa_per_kelas = ambil_siswa_per_kelas(cursor)
        bab_per_gmk = ambil_bab_per_gmk(cursor)
        materi_per_bab = ambil_materi_per_bab(cursor)
        positif_ids, negatif_ids = ambil_id_perilaku(cursor)

        sinkronisasi_terlewat(
            connection,
            cursor,
            semester_info,
            siswa_per_kelas,
            bab_per_gmk,
            materi_per_bab,
            positif_ids,
            negatif_ids,
        )
        connection.commit()

    while True:
        with koneksi_cursor(dictionary=True) as (connection, cursor):
            sekarang = datetime.now()
            nama_hari = nama_hari_dari_tanggal(sekarang)
            jam_sekarang = sekarang.strftime("%H:%M:%S")
            jadwal_list = ambil_jadwal_sedang_berjalan(cursor, semester_id, nama_hari, jam_sekarang)

            if not jadwal_list:
                tulis_log(f"{sekarang.strftime('%H:%M')} | tidak ada jadwal aktif")
                connection.commit()
            else:
                for jadwal in jadwal_list:
                    proses_jadwal(
                        cursor,
                        sekarang,
                        jadwal,
                        siswa_per_kelas,
                        bab_per_gmk,
                        materi_per_bab,
                        positif_ids,
                        negatif_ids,
                        semester_id,
                    )
                connection.commit()

        if jeda_detik > 0:
            time.sleep(jeda_detik)
