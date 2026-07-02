@php
    if (!is_array($trend) || ($trend['arah'] ?? null) === 'baru' || ($trend['arah'] ?? null) === 'belum_cukup_data') {
        $label = match ($trend['arah'] ?? null) {
            'baru' => 'Data baru',
            'belum_cukup_data' => 'Belum cukup data',
            default => 'Belum ada data',
        };
        echo '<span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">' . $label . '</span>';
        return;
    }

    $arah = $trend['arah'] ?? 'tetap';
    $selisih = $trend['selisih'] ?? null;

    if ($arah === 'naik') {
        $icon = '<svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>';
        $color = 'text-emerald-700';
        $bg = 'bg-emerald-50';
        $border = 'border-emerald-200';
        $arrowText = 'Naik ' . number_format($selisih, 2);
    } elseif ($arah === 'turun') {
        $icon = '<svg class="h-3.5 w-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
        $color = 'text-rose-700';
        $bg = 'bg-rose-50';
        $border = 'border-rose-200';
        $arrowText = 'Turun ' . number_format($selisih, 2);
    } else {
        $icon = '<svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" /></svg>';
        $color = 'text-gray-700';
        $bg = 'bg-gray-50';
        $border = 'border-gray-200';
        $arrowText = 'Tetap';
    }
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $bg }} {{ $color }} {{ $border }}">
    {!! $icon !!}
    <span>{{ $arrowText }}</span>
</span>
