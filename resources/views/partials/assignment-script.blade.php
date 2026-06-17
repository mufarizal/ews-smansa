<script>
    (function() {
        const globalSemesterSelect = document.getElementById('semester_id_global');

        // ── Semester helpers ──────────────────────────────────────────────────

        function getSemesterDates(semesterId) {
            if (!globalSemesterSelect || !semesterId) return null;
            const option = globalSemesterSelect.querySelector(`option[value="${semesterId}"]`);
            if (!option) return null;
            return {
                valid_from: option.dataset.from,
                valid_until: option.dataset.to
            };
        }

        /**
         * Fill all .semester-date inputs across all tabs with the selected semester dates.
         * Admin can still override manually after this.
         */
        function fillSemesterDates(semesterId) {
            const dates = getSemesterDates(semesterId);

            document.querySelectorAll('.semester-date').forEach(function(el) {
                if (dates) {
                    if (el.dataset.type === 'from') el.value = dates.valid_from;
                    if (el.dataset.type === 'until') el.value = dates.valid_until;
                } else {
                    // Semester di-clear → kosongkan field agar admin isi manual
                    el.value = '';
                }
            });
        }

        /**
         * Sync hidden semester_id fields on all forms.
         */
        function syncSemesterField(semesterId) {
            document.querySelectorAll('input[name="semester_id"]').forEach(function(el) {
                el.value = semesterId;
            });
        }

        if (globalSemesterSelect) {
            // Trigger on change
            globalSemesterSelect.addEventListener('change', function() {
                syncSemesterField(this.value);
                fillSemesterDates(this.value);
            });

            // Auto-fill on page load if semester is pre-selected (e.g. active semester)
            if (globalSemesterSelect.value) {
                syncSemesterField(globalSemesterSelect.value);
                fillSemesterDates(globalSemesterSelect.value);
            }
        }

        // Ensure semester_id is always synced on form submit
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const semesterId = globalSemesterSelect ? globalSemesterSelect.value : '';
                syncSemesterField(semesterId);
            });
        });

        // ── Tab switching ─────────────────────────────────────────────────────

        const activeTab = '{{ $activeTab ?? 'mapel' }}';

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            const tabEl = document.getElementById(tabName + '-tab');
            if (tabEl) tabEl.classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-green-700', 'text-green-700');
                b.classList.add('border-transparent', 'text-gray-600');
            });

            const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-gray-600');
                activeBtn.classList.add('border-b-2', 'border-green-700', 'text-green-700');
            }
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                switchTab(this.dataset.tab);
            });
        });

        switchTab(activeTab);

        // ── Guru search filter ────────────────────────────────────────────────

        [
            ['guru_search_mapel', 'guru_id_mapel'],
            ['guru_search_piket', 'guru_id_piket'],
            ['guru_search_wali', 'guru_id_wali'],
            ['guru_search_bk', 'guru_id_bk'],
        ].forEach(function([searchId, selectId]) {
            const searchEl = document.getElementById(searchId);
            const selectEl = document.getElementById(selectId);
            if (!searchEl || !selectEl) return;

            const allOptions = Array.from(selectEl.options);

            searchEl.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();
                Array.from(selectEl.options).forEach(opt => {
                    if (!opt.value) return; // keep placeholder
                    const match = (opt.dataset.search || opt.text.toLowerCase()).includes(
                        keyword);
                    opt.hidden = !match;
                });
                // Reset selection if current value is hidden
                const selected = selectEl.options[selectEl.selectedIndex];
                if (selected && selected.hidden) selectEl.value = '';
            });
        });

        // ── Dynamic mapel rows ────────────────────────────────────────────────

        const mapelWrapper = document.getElementById('mapel-wrapper');
        const mapelTemplate = document.getElementById('mapel-row-template');
        const btnAddMapel = document.getElementById('btn-add-mapel');

        function reindexMapelRows() {
            if (!mapelWrapper) return;
            mapelWrapper.querySelectorAll('.mapel-row').forEach(function(row, i) {
                row.querySelectorAll('[name]').forEach(function(el) {
                    el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                });
            });
        }

        if (btnAddMapel && mapelTemplate && mapelWrapper) {
            btnAddMapel.addEventListener('click', function() {
                const clone = mapelTemplate.content.cloneNode(true);
                const row = clone.querySelector('.mapel-row');
                const idx = mapelWrapper.querySelectorAll('.mapel-row').length;

                // Set proper names with index
                row.querySelector('.mapel-select').name = `mapel_kelas[${idx}][mapel_id]`;
                row.querySelector('.kelas-select').name = `mapel_kelas[${idx}][kelas_id]`;

                row.querySelector('.btn-remove-mapel-row').addEventListener('click', function() {
                    row.remove();
                    reindexMapelRows();
                });

                mapelWrapper.appendChild(clone);
            });
        }

        // Remove button for pre-rendered rows (from old())
        if (mapelWrapper) {
            mapelWrapper.querySelectorAll('.btn-remove-mapel-row').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btn.closest('.mapel-row').remove();
                    reindexMapelRows();
                });
            });
        }
    })();
</script>
