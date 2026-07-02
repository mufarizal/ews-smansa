@php
    $accent = $accentClass ?? 'text-gray-700';
    $iconColor = $accent;
@endphp
<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-50 {{ $iconColor }}">
            {!! $icon !!}
        </div>
        <div class="min-w-0">
            <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
        </div>
    </div>
</div>
