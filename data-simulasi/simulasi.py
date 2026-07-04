#!/usr/bin/env python3
"""
simulasi.py
===========
Tujuan: Menjalankan service simulator sekolah secara terus-menerus.

Tanggung jawab:
- Loop pengecekan jadwal aktif
- Menjalankan generator ketika ada jadwal yang sedang berlangsung
"""

import argparse

from konfigurasi.pengaturan import JEDA_LOOP_DETIK
from pembuat.layanan import jalankan_simulasi


def main():
    """
    Entry point mode simulasi.
    """
    parser = argparse.ArgumentParser(description="Service simulator sekolah")
    parser.add_argument("--jeda", type=int, default=JEDA_LOOP_DETIK, help="Jeda pengecekan dalam detik")
    args = parser.parse_args()
    jalankan_simulasi(jeda_detik=args.jeda)


if __name__ == "__main__":
    main()
