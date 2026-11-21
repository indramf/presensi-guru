<x-filament-panels::page>
    @php($board = $this->getSchedule())

    @if (! auth()->user()->teacher)
    <x-filament::section>
        <p class="text-sm text-gray-500">
            Data guru belum terhubung dengan akun ini.
            Silakan hubungi Administrator.
        </p>
    </x-filament::section>
    @else
    <p class="text-sm text-gray-500">
        Berikut jadwal pelajaran mingguan Anda.
    </p>

    <x-filament::section heading="Jadwal Pelajaran Mingguan">
        @include('filament.partials.timetable-grid', [
        'headers' => array_column($board['days'], 'name'),
        'rows' => $board['rows'],
        'highlight' => $board['todayIndex'] === null
        ? []
        : [$board['todayIndex']],
        'showClass' => true,
        ])
    </x-filament::section>

    @include('filament.partials.timetable-unmatched', [
    'unmatched' => $board['unmatched'],
    ])
    @endif
</x-filament-panels::page>