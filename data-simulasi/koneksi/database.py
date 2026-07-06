#!/usr/bin/env python3
"""
koneksi/database.py
===================
Tujuan: Menyediakan koneksi database untuk simulator sekolah.

Tanggung jawab:
- Membaca konfigurasi koneksi dari environment
- Membuka koneksi MySQL/MariaDB
- Menyediakan context manager koneksi + cursor
"""

import os
from contextlib import contextmanager
try:
    import mysql.connector
    from mysql.connector import Error
except ImportError as exc:
    raise SystemExit(
        "Library 'mysql-connector-python' belum terpasang.\n"
        "Install dulu dengan: pip install mysql-connector-python"
    ) from exc

CONFIG_DB = {
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", 3307)),
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", "root"),
    "database": os.environ.get("DB_DATABASE", "ews_smansa"),
}


def buka_koneksi():
    """
    Membuka koneksi database.
    Return:
        mysql.connector connection object.
    """
    try:
        return mysql.connector.connect(**CONFIG_DB)
    except Error as exc:
        raise SystemExit(f"Gagal konek ke database: {exc}") from exc


@contextmanager
def koneksi_cursor(dictionary=True):
    """
    Context manager untuk koneksi dan cursor database.

    Parameter:
        dictionary (bool): Jika True, cursor mengembalikan dict.

    Yield:
        tuple(connection, cursor)
    """
    connection = buka_koneksi()
    cursor = connection.cursor(dictionary=dictionary)
    try:
        yield connection, cursor
    finally:
        cursor.close()
        connection.close()