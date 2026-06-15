@props([
    'name' => 'User',
    'size' => 'w-8 h-8 text-sm',
])

@php
    $words = explode(' ', trim($name));
    $firstWord = $words[0] ?? '';
    $firstChar = $firstWord[0] ?? '?';

    // Inisial: huruf pertama kata 1 + huruf pertama kata 2 (kalau 1 kata pakai 1 huruf saja)
    if (isset($words[1])) {
        $initials = strtoupper($firstChar . $words[1][0]);
    } else {
        $initials = strtoupper($firstChar);
    }

    // Warna konsisten dari hash nama
    $colors = [
        'bg-red-500', 'bg-orange-500', 'bg-amber-500', 'bg-green-500',
        'bg-emerald-500', 'bg-teal-500', 'bg-cyan-500', 'bg-sky-500',
        'bg-blue-500', 'bg-indigo-500', 'bg-violet-500', 'bg-purple-500',
        'bg-fuchsia-500', 'bg-pink-500', 'bg-rose-500',
    ];
    $color = $colors[crc32(strtolower($name)) % count($colors)];
@endphp

<div class="rounded-full flex items-center justify-center {{ $color }} text-white font-medium {{ $size }}"
     aria-hidden="true">
    {{ $initials }}
</div>