@php
    $map = [
        'binaan' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
        'perhatian' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
        'aman' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
    ];
    $style = $map[$kategori] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-500', 'border' => 'border-gray-200'];
    $label = match ($kategori) {
        'binaan' => 'Perlu Bimbingan',
        'perhatian' => 'Perlu Perhatian',
        'aman' => 'Baik',
        default => 'Belum ada data',
    };
@endphp
<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $style['bg'] }} {{ $style['text'] }} {{ $style['border'] }}">
    {{ $label }}
</span>
