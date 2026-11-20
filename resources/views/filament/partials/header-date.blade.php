@php
$today = now('Asia/Jakarta')
->locale('id')
->translatedFormat('l, d F Y');
@endphp

<div class="hidden sm:flex flex-col items-end justify-center px-3 text-right">
    <span class="text-xs font-medium text-gray-950 dark:text-white">
        {{ $today }}
    </span>
</div>