#!/usr/bin/env python3
"""
inisialisasi.py
===============
Tujuan: Mengisi histori simulasi sekolah untuk beberapa hari ke belakang.

Tanggung jawab:
- Menjalankan mode bootstrap satu kali
- Memudahkan pengisian data awal untuk Laravel
"""

import argparse
from datetime import date

from konfigurasi.pengaturan import HARI_BOOTSTRAP_DEFAULT
from pembuat.layanan import jalankan_bootstrap


def main():
    """
    Entry point mode bootstrap.
    """
    parser = argparse.ArgumentParser(description="Bootstrap data historis simulator sekolah")
    parser.add_argument("--hari", type=int, default=HARI_BOOTSTRAP_DEFAULT, help="Jumlah hari ke belakang yang digenerate")
    parser.add_argument("--tanggal-akhir", type=str, default=None, help="Tanggal akhir simulasi format YYYY-MM-DD")
    args = parser.parse_args()

    tanggal_akhir = date.fromisoformat(args.tanggal_akhir) if args.tanggal_akhir else None
    jalankan_bootstrap(hari=args.hari, tanggal_akhir=tanggal_akhir)


if __name__ == "__main__":
    main()
