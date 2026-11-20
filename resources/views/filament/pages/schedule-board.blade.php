<x-filament-panels::page>
    <style>
        .dashboard-schedule-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .5rem 1rem;
            margin-bottom: .5rem;
        }

        .dashboard-schedule-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
            margin-left: auto;
        }

        .dashboard-schedule-filter {
            width: 100%;
            min-width: 10rem;
            max-width: 14rem;
        }

        .dashboard-schedule-description {
            color: var(--gray-500);
            font-size: .875rem;
            margin-top: -.35rem;
            margin-bottom: .75rem;
        }

        .dashboard-schedule-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .35rem .75rem;
            font-size: .8rem;
        }

        .dashboard-schedule-legend-item {
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .dashboard-schedule-dot {
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .dashboard-schedule-dot.hadir {
            background: #16a34a;
        }

        .dashboard-schedule-dot.terlambat {
            background: #d97706;
        }

        .dashboard-schedule-dot.izin {
            background: #2563eb;
        }

        .dashboard-schedule-dot.tidak-hadir {
            background: #dc2626;
        }

        .dashboard-schedule-dot.menunggu {
            background: #9ca3af;
        }

        @media (max-width: 640px) {
            .dashboard-schedule-filters {
                width: 100%;
                margin-left: 0;
            }

            .dashboard-schedule-filter {
                flex: 1 1 10rem;
                max-width: none;
            }
        }
    </style>

    @php($board = $this->getBoard())

    <div class="dashboard-schedule-toolbar">
        <div>
            <p class="dashboard-schedule-description">
                Jadwal pelajaran dan informasi kehadiran guru berdasarkan sesi pembelajaran.
            </p>

            @if ($board['mode'] === 'day')
            <div class="dashboard-schedule-legend">
                <span class="font-medium">Keterangan status:</span>

                <span class="dashboard-schedule-legend-item">
                    <span class="dashboard-schedule-dot hadir"></span> Hadir
                </span>

                <span class="dashboard-schedule-legend-item">
                    <span class="dashboard-schedule-dot terlambat"></span> Terlambat
                </span>

                <span class="dashboard-schedule-legend-item">
                    <span class="dashboard-schedule-dot izin"></span> Izin
                </span>

                <span class="dashboard-schedule-legend-item">
                    <span class="dashboard-schedule-dot tidak-hadir"></span> Tidak hadir
                </span>

                <span class="dashboard-schedule-legend-item">
                    <span class="dashboard-schedule-dot menunggu"></span> Menunggu
                </span>
            </div>
            @endif
        </div>

        <div class="dashboard-schedule-filters">
            <div class="dashboard-schedule-filter">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="mode">
                        <option value="day">Per hari</option>
                        <option value="class">Per kelas</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            @if ($board['mode'] === 'class')
            <div class="dashboard-schedule-filter">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="classId">
                        <option value="">Pilih kelas</option>
                        @foreach ($this->getClasses() as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->class_name }}
                        </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            @elseif ($board['mode'] === 'day')
            <div class="dashboard-schedule-filter">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="day">
                        @foreach ($this->getDays() as $d)
                        <option value="{{ $d['iso'] }}">
                            {{ $d['name'] }}{{ $d['iso'] === now()->dayOfWeekIso ? ' (hari ini)' : '' }}
                        </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            @endif
        </div>
    </div>

    @if ($board['mode'] === 'day')
    @if (! $board['valid'])
    <x-filament::section>
        <p class="text-sm">Pilih hari sekolah untuk melihat jadwal.</p>
    </x-filament::section>
    @else
    <x-filament::section :heading="'Jadwal Hari '.$board['dayName']">
        @include('filament.partials.timetable-grid', [
        'headers' => $board['columns']->pluck('class_name')->all(),
        'rows' => $board['rows'],
        'highlight' => [],
        ])
    </x-filament::section>

    @include('filament.partials.timetable-unmatched', [
    'unmatched' => $board['unmatched'],
    ])
    @endif

    @elseif ($board['mode'] === 'class')
    @if ($board['class'] === null)
    <x-filament::section>
        <p class="text-sm">Pilih kelas untuk melihat jadwal mingguannya.</p>
    </x-filament::section>
    @else
    <x-filament::section :heading="'Jadwal Kelas '.$board['class']->class_name">
        @include('filament.partials.timetable-grid', [
        'headers' => array_column($board['days'], 'name'),
        'rows' => $board['rows'],
        'highlight' => $board['todayIndex'] === null ? [] : [$board['todayIndex']],
        ])
    </x-filament::section>

    @include('filament.partials.timetable-unmatched', [
    'unmatched' => $board['unmatched'],
    ])
    @endif
    @endif
</x-filament-panels::page>