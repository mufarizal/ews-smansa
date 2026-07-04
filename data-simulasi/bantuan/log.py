#!/usr/bin/env python3
"""
bantuan/log.py
==============
Tujuan: Menulis log simulator ke folder logs/.

Tanggung jawab:
- Memastikan folder logs ada
- Menulis log harian yang mudah dibaca
"""

import os
from datetime import datetime

FOLDER_LOG = os.path.join(os.path.dirname(os.path.dirname(__file__)), "logs")


def pastikan_folder_log():
    """
    Memastikan folder logs tersedia.

    Return:
        Path folder logs.
    """
    os.makedirs(FOLDER_LOG, exist_ok=True)
    return FOLDER_LOG


def tulis_log(pesan):
    """
    Menulis satu baris log ke file harian.

    Parameter:
        pesan (str): Pesan log.
    """
    pastikan_folder_log()
    tanggal_hari_ini = datetime.now().strftime("%Y-%m-%d")
    nama_file = os.path.join(FOLDER_LOG, f"simulation_{tanggal_hari_ini}.log")
    with open(nama_file, "a", encoding="utf-8") as handle:
        handle.write(f"{datetime.now().strftime('%H:%M:%S')} - {pesan}\n")
