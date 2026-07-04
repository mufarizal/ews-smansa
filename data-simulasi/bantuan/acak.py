#!/usr/bin/env python3
"""
bantuan/acak.py
===============
Tujuan: Menyediakan helper acak untuk simulator sekolah.

Tanggung jawab:
- Memilih nilai berdasarkan bobot
- Mengecek peluang kejadian
"""

import random


def pilih_berbobot(bobot: dict):
    """
    Memilih 1 key dari dictionary secara acak sesuai bobot.

    Parameter:
        bobot (dict): Format {key: bobot}
    """
    kunci = list(bobot.keys())
    nilai_bobot = list(bobot.values())
    return random.choices(kunci, weights=nilai_bobot, k=1)[0]


def peluang(persen: int) -> bool:
    """
    True dengan peluang persen tertentu.

    Parameter:
        persen (int): 1-100.
    """
    return random.randint(1, 100) <= persen
