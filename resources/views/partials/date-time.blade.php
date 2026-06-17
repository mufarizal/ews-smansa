{{-- DATE INPUT --}}
@if (isset($dateInput))
    @php
        $dateTheme = $dateInput['theme'] ?? 'blue';
        $borderColor = match ($dateTheme) {
            'green' => 'border-green-600',
            'red' => 'border-red-600',
            'indigo' => 'border-indigo-600',
            default => 'border-blue-600',
        };
        $focusColor = match ($dateTheme) {
            'green' => 'focus:ring-green-500',
            'red' => 'focus:ring-red-500',
            'indigo' => 'focus:ring-indigo-500',
            default => 'focus:ring-blue-500',
        };
        $bgColor = match ($dateTheme) {
            'green' => 'bg-green-50',
            'red' => 'bg-red-50',
            'indigo' => 'bg-indigo-50',
            default => 'bg-blue-50',
        };
        $accentColor = match ($dateTheme) {
            'green' => 'from-green-500 to-green-600',
            'red' => 'from-red-500 to-red-600',
            'indigo' => 'from-indigo-500 to-indigo-600',
            default => 'from-blue-500 to-blue-600',
        };
        $hasDateError = $errors->has($dateInput['name']);
    @endphp
    <div class="mb-5">
        <label for="{{ $dateInput['name'] }}" class="block text-base font-bold text-gray-900 mb-2">
            📅 {{ $dateInput['label'] }}
            @if ($dateInput['required'] ?? false)
                <span class="text-red-600">*</span>
            @endif
        </label>
        <input type="date" id="{{ $dateInput['name'] }}" name="{{ $dateInput['name'] }}"
            value="{{ $dateInput['value'] ?? '' }}" @if ($dateInput['required'] ?? false) required @endif
            class="w-full px-4 py-3 text-base font-semibold border-2 rounded-lg {{ $hasDateError ? 'border-red-600' : $borderColor }} 
                {{ $bgColor }} text-gray-900 focus:outline-none focus:ring-2 {{ $focusColor }} transition-all
                cursor-pointer hover:shadow-md" />

        @error($dateInput['name'])
            <p class="mt-2 text-sm font-semibold text-red-600">⚠️ {{ $message }}</p>
        @enderror
        @if (isset($dateInput['help']))
            <p class="mt-2 text-xs text-gray-600 font-medium">💡 {{ $dateInput['help'] }}</p>
        @endif
    </div>
@endif

{{-- TIME INPUT (Custom 24-hour format) --}}
@if (isset($timeInput))
    @php
        $timeTheme = $timeInput['theme'] ?? 'blue';
        $borderColor = match ($timeTheme) {
            'green' => 'border-green-600',
            'red' => 'border-red-600',
            'indigo' => 'border-indigo-600',
            default => 'border-blue-600',
        };
        $focusColor = match ($timeTheme) {
            'green' => 'focus:ring-green-500',
            'red' => 'focus:ring-red-500',
            'indigo' => 'focus:ring-indigo-500',
            default => 'focus:ring-blue-500',
        };
        $bgColor = match ($timeTheme) {
            'green' => 'bg-green-50',
            'red' => 'bg-red-50',
            'indigo' => 'bg-indigo-50',
            default => 'bg-blue-50',
        };
        $accentColor = match ($timeTheme) {
            'green' => 'from-green-500 to-green-600',
            'red' => 'from-red-500 to-red-600',
            'indigo' => 'from-indigo-500 to-indigo-600',
            default => 'from-blue-500 to-blue-600',
        };
        $hasTimeError = $errors->has($timeInput['name']);

        // Parse time value (HH:mm format)
        $jam = '00';
        $menit = '00';
        if (!empty($timeInput['value'])) {
            [$jam, $menit] = explode(':', $timeInput['value']);
        } elseif (old($timeInput['name'])) {
            [$jam, $menit] = explode(':', old($timeInput['name']));
        }
    @endphp
    <div class="mb-5">
        <label class="block text-base font-bold text-gray-900 mb-2">
            ⏰ {{ $timeInput['label'] }}
            @if ($timeInput['required'] ?? false)
                <span class="text-red-600">*</span>
            @endif
        </label>

        {{-- Hidden input untuk submit (HH:mm format) --}}
        <input type="hidden" id="{{ $timeInput['name'] }}" name="{{ $timeInput['name'] }}"
            value="{{ old($timeInput['name'], $timeInput['value'] ?? '') }}"
            @if ($timeInput['required'] ?? false) required @endif />

        {{-- Custom time picker: Jam dan Menit --}}
        <div class="flex gap-3 items-center">
            <div class="flex-1">
                <label class="text-xs text-gray-600 font-semibold">Jam</label>
                <input type="number" id="{{ $timeInput['name'] }}_jam" min="0" max="23" step="1"
                    value="{{ $jam }}"
                    class="w-full px-3 py-2.5 text-base font-semibold border-2 rounded-lg {{ $hasTimeError ? 'border-red-600' : $borderColor }} 
                        {{ $bgColor }} text-gray-900 focus:outline-none focus:ring-2 {{ $focusColor }} transition-all"
                    onchange="updateTimeDisplay('{{ $timeInput['name'] }}')"
                    oninput="updateTimeDisplay('{{ $timeInput['name'] }}')" />
            </div>

            <div class="text-2xl font-bold text-gray-400 mt-6">:</div>

            <div class="flex-1">
                <label class="text-xs text-gray-600 font-semibold">Menit</label>
                <input type="number" id="{{ $timeInput['name'] }}_menit" min="0" max="59" step="1"
                    value="{{ $menit }}"
                    class="w-full px-3 py-2.5 text-base font-semibold border-2 rounded-lg {{ $hasTimeError ? 'border-red-600' : $borderColor }} 
                        {{ $bgColor }} text-gray-900 focus:outline-none focus:ring-2 {{ $focusColor }} transition-all"
                    onchange="updateTimeDisplay('{{ $timeInput['name'] }}')"
                    oninput="updateTimeDisplay('{{ $timeInput['name'] }}')" />
            </div>

            {{-- Display hasil --}}
            <div class="mt-6">
                <div class="px-4 py-2 bg-white border-2 {{ $borderColor }} rounded-lg font-semibold text-gray-900"
                    id="{{ $timeInput['name'] }}_display">
                    {{ sprintf('%02d:%02d', $jam, $menit) }}
                </div>
            </div>
        </div>

        <script>
            function updateTimeDisplay(fieldName) {
                const jamField = document.getElementById(fieldName + '_jam');
                const menitField = document.getElementById(fieldName + '_menit');
                const displayField = document.getElementById(fieldName + '_display');
                const hiddenField = document.getElementById(fieldName); // ← ambil hidden input

                let jam = parseInt(jamField.value) || 0;
                let menit = parseInt(menitField.value) || 0;

                // Normalize
                jam = Math.max(0, Math.min(23, jam));
                menit = Math.max(0, Math.min(59, menit));

                const timeValue = String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');

                // Update display
                displayField.textContent = timeValue;

                // ← FIX: Update hidden input agar nilai terkirim ke server
                hiddenField.value = timeValue;
            }
        </script>

        @error($timeInput['name'])
            <p class="mt-2 text-sm font-semibold text-red-600">⚠️ {{ $message }}</p>
        @enderror
        @if (isset($timeInput['help']))
            <p class="mt-2 text-xs text-gray-600 font-medium">💡 {{ $timeInput['help'] }}</p>
        @endif
    </div>
@endif
