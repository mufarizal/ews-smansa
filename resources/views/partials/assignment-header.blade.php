<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
    <label for="semester_id_global" class="block text-sm font-medium text-gray-700 mb-2">
        Pilih Semester <span class="text-gray-400 font-normal">(Opsional)</span>
    </label>
    <select id="semester_id_global"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
        <option value="">-- Tidak pakai semester (isi tanggal manual) --</option>
        @foreach ($semesters as $semester)
            <option value="{{ $semester->id }}" data-from="{{ $semester->tanggal_mulai }}"
                data-to="{{ $semester->tanggal_selesai }}" {{ $semester->is_active ? 'selected' : '' }}>
                {{ $semester->nama }}
                ({{ \Carbon\Carbon::parse($semester->tanggal_mulai)->format('d/m/Y') }} –
                {{ \Carbon\Carbon::parse($semester->tanggal_selesai)->format('d/m/Y') }})
                @if ($semester->is_active)
                    ✓ Aktif
                @endif
            </option>
        @endforeach
    </select>
    <p class="mt-2 text-xs text-gray-500">
        Pilih semester untuk mengisi otomatis kolom <em>Berlaku Dari</em> dan <em>Berlaku Sampai</em> pada semua
        form di bawah. Anda tetap bisa mengubah tanggal secara manual setelahnya.
    </p>
</div>

<div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200">
    <button type="button" data-tab="mapel"
        class="tab-btn px-4 py-2 text-sm font-medium transition border-b-2 {{ $activeTab === 'mapel' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
        Penugasan Mapel
    </button>
    <button type="button" data-tab="piket"
        class="tab-btn px-4 py-2 text-sm font-medium transition border-b-2 {{ $activeTab === 'piket' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
        Penugasan Piket
    </button>
    <button type="button" data-tab="wali"
        class="tab-btn px-4 py-2 text-sm font-medium transition border-b-2 {{ $activeTab === 'wali' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
        Penugasan Wali Kelas
    </button>
    <button type="button" data-tab="bk"
        class="tab-btn px-4 py-2 text-sm font-medium transition border-b-2 {{ $activeTab === 'bk' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
        Penugasan Guru BK
    </button>
</div>
