#!/usr/bin/env bash
#
# start.sh — Peluncur simulator sekolah (Linux/cPanel).
#
# Cara pakai:
#   chmod +x start.sh
#   ./start.sh            # jalankan simulasi (loop terus)
#   ./start.sh bootstrap  # isi histori 30 hari sekali jalan
#   ./start.sh bootstrap 90
#
# Script ini otomatis membuat virtualenv (jika belum ada), menginstal
# dependensi, dan memuat file .env ke environment sebelum menjalankan Python.
set -euo pipefail

cd "$(dirname "$0")"

# Muat .env kalau ada (DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_DATABASE)
if [ -f .env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./.env
    set +a
fi

# Siapkan virtualenv bila perlu
if [ ! -d .venv ]; then
    echo ">>> Membuat virtual environment..."
    python3 -m venv .venv
fi

# shellcheck disable=SC1091
source .venv/bin/activate

# Instal dependensi
python -m pip install --quiet --upgrade pip
python -m pip install --quiet -r requirements.txt

MODE="${1:-simulasi}"
shift || true

if [ "$MODE" = "bootstrap" ]; then
    HARI="${1:-}"
    if [ -n "$HARI" ]; then
        exec python inisialisasi.py --hari "$HARI"
    else
        exec python inisialisasi.py
    fi
else
    exec python simulasi.py "$@"
fi
