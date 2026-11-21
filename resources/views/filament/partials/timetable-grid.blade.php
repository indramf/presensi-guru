<div style="overflow-x: auto;">
    <table class="timetable">
        <thead>
            <tr>
                <th class="tt-time-heading">Jam</th>

                @foreach ($headers as $i => $header)
                <th>
                    {{ $header }}

                    @if (in_array($i, $highlight ?? [], true))
                    <span class="tt-today">(hari ini)</span>
                    @endif
                </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($rows as $row)
            <tr>
                @if ($row['type'] === 'break')
                <td class="tt-break tt-time">
                    {{ $row['label'] }}
                </td>
                <td class="tt-break" colspan="{{ count($headers) }}">
                    Istirahat
                </td>
                @else
                <td class="tt-time {{ $row['current'] ? 'tt-current' : '' }}">
                    {{ $row['label'] }}

                    @if ($row['current'])
                    <span class="tt-current-label">Sedang berlangsung</span>
                    @endif
                </td>

                @foreach ($row['cells'] as $cell)
                <td class="tt-cell">
                    @forelse ($cell as $entry)
                    <div class="tt-subject">
                        {{ $entry['subject'] }}
                    </div>

                    @if (($showClass ?? false) && ! empty($entry['class']))
                    <div class="tt-teacher">
                        {{ $entry['class'] }}
                    </div>
                    @elseif (! ($showClass ?? false) && ! empty($entry['teacher']))
                    <div class="tt-teacher">
                        {{ $entry['teacher'] }}
                    </div>
                    @endif

                    @isset($entry['status_label'])
                    <div class="tt-status">
                        <x-filament::badge :color="$entry['status_color']">
                            {{ $entry['status_label'] }}
                        </x-filament::badge>
                    </div>
                    @endisset
                    @empty
                    {{-- Sel kosong tetap kosong --}}
                    @endforelse
                </td>
                @endforeach
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    .timetable {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: .75rem;
        border: 1px solid rgba(128, 128, 128, .35);
        border-radius: .5rem;
        overflow: hidden;
    }

    .timetable th,
    .timetable td {
        border-right: 1px solid rgba(128, 128, 128, .3);
        border-bottom: 1px solid rgba(128, 128, 128, .3);
        padding: .75rem;
        text-align: left;
        vertical-align: top;
    }

    .timetable th {
        background: rgba(128, 128, 128, .10);
        font-weight: 700;
        white-space: nowrap;
    }

    .timetable tr:last-child td {
        border-bottom: 0;
    }

    .timetable th:last-child,
    .timetable td:last-child {
        border-right: 0;
    }

    .timetable .tt-time {
        min-width: 110px;
        white-space: nowrap;
        font-weight: 650;
    }

    .timetable .tt-time-heading {
        min-width: 110px;
    }

    .timetable .tt-label {
        font-weight: 700;
        vertical-align: middle;
        white-space: nowrap;
        background: rgba(128, 128, 128, .08);
    }

    .timetable .tt-subject {
        font-weight: 650;
        line-height: 1.45;
    }

    .timetable .tt-teacher {
        margin-top: .15rem;
        color: var(--gray-500);
        line-height: 1.45;
    }

    .timetable .tt-status {
        margin-top: .45rem;
    }

    .timetable .tt-break {
        background: #fef08a;
        color: #713f12;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
    }

    .timetable .tt-current {
        background: rgba(59, 130, 246, .10);
    }

    .timetable .tt-current-label {
        display: block;
        margin-top: .25rem;
        color: #3b82f6;
        font-size: .7rem;
        font-weight: 500;
    }

    .timetable .tt-today {
        margin-left: .25rem;
        color: #16a34a;
        font-size: .75rem;
        font-weight: 500;
    }

    .timetable .tt-subject {
        font-size: .75rem;
    }

    .timetable .tt-teacher {
        font-size: .7rem;
    }

    .timetable .tt-time {
        font-size: .75rem;
    }

    .timetable .tt-status {
        font-size: .7rem;
    }
</style>