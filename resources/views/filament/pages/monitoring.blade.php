<x-filament-panels::page>
    @php
        $summary = $this->summary;
        $rows = $this->rows;

        // Ambil nilai dari array/object dengan beberapa kemungkinan nama key
        $get = function ($row, array $keys, $default = '-') {
            foreach ($keys as $key) {
                $value = data_get($row, $key);
                if ($value instanceof \BackedEnum) {
                    $value = method_exists($value, 'label') ? $value->label() : $value->value;
                }
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
            return $default;
        };

        // Kelompokkan status ke kategori
        $classify = function ($row) use ($get) {
            $s = strtolower(
                $get($row, ['status', 'report_status', 'session_status'], '') . ' ' .
                $get($row, ['status_label', 'label'], '')
            );
            return match (true) {
                str_contains($s, 'belum') || str_contains($s, 'unconfirmed') || trim($s) === '' => 'unconfirmed',
                str_contains($s, 'tidak') || str_contains($s, 'absent') => 'absent',
                str_contains($s, 'terlambat') || str_contains($s, 'late') => 'late',
                str_contains($s, 'izin') || str_contains($s, 'permission') => 'permission',
                str_contains($s, 'hadir') || str_contains($s, 'present') => 'present',
                default => 'unconfirmed',
            };
        };

        $counted = ['unconfirmed' => 0, 'present' => 0, 'late' => 0, 'permission' => 0, 'absent' => 0];
        foreach ($rows as $row) {
            $counted[$classify($row)]++;
        }

        $pick = function (array $keys, string $cat) use ($summary, $counted) {
            foreach ($keys as $k) {
                $v = data_get($summary, $k);
                if ($v !== null) {
                    return (int) $v;
                }
            }
            return $counted[$cat];
        };

        $stats = [
            'needs_action' => (int) data_get($summary, 'needs_action', 0),
            'unconfirmed' => $pick(['unconfirmed', 'not_confirmed', 'belum_terkonfirmasi'], 'unconfirmed'),
            'present' => $pick(['present', 'hadir'], 'present'),
            'late' => $pick(['late', 'terlambat'], 'late'),
            'permission' => $pick(['permission', 'excused', 'izin'], 'permission'),
            'absent' => $pick(['absent', 'tidak_hadir'], 'absent'),
        ];

        $meta = [
            'unconfirmed' => ['label' => 'Belum terkonfirmasi', 'color' => '#64748b'],
            'present' => ['label' => 'Hadir', 'color' => '#15803d'],
            'late' => ['label' => 'Terlambat', 'color' => '#b45309'],
            'permission' => ['label' => 'Izin', 'color' => '#1d4ed8'],
            'absent' => ['label' => 'Tidak Hadir', 'color' => '#b91c1c'],
        ];

        // Donut chart (conic-gradient)
        $total = max(array_sum(array_intersect_key($stats, $meta)), 0);
        $segments = [];
        $angle = 0;
        foreach ($meta as $key => $m) {
            if ($total > 0 && $stats[$key] > 0) {
                $next = $angle + ($stats[$key] / $total) * 360;
                $segments[] = "{$m['color']} {$angle}deg {$next}deg";
                $angle = $next;
            }
        }
        $donut = $segments ? 'conic-gradient(' . implode(', ', $segments) . ')' : 'conic-gradient(#e2e8f0 0deg 360deg)';
    @endphp

    <style>
        .mon { --bg: #ffffff; --soft: #f1f5f9; --line: #e2e8f0; --text: #0f172a; --muted: #475569; --primary: #0d9488; }
        .dark .mon { --bg: #18181b; --soft: #27272a; --line: #3f3f46; --text: #f4f4f5; --muted: #a1a1aa; --primary: #2dd4bf; }
        .mon { color: var(--text); display: flex; flex-direction: column; gap: 1.25rem; }
        .mon * { box-sizing: border-box; }

        /* Toolbar */
        .mon-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; }
        .mon-field { display: flex; flex-direction: column; gap: .25rem; }
        .mon-field label { font-size: .75rem; color: var(--muted); padding-left: .5rem; }
        .mon-input, .mon-select {
            min-height: 2.75rem; padding: 0 1rem; border-radius: 9999px; border: 1px solid var(--line);
            background: var(--bg); color: var(--text); font-size: .9rem; outline: none; min-width: 11rem;
        }
        .mon-input { min-width: 16rem; }
        .mon-input:focus-visible, .mon-select:focus-visible, .mon-action-card:focus-visible {
            outline: 3px solid var(--primary); outline-offset: 2px;
        }
        .mon-live { margin-left: auto; font-size: .78rem; color: var(--muted); display: flex; align-items: center; gap: .4rem; }
        .mon-dot { width: .5rem; height: .5rem; border-radius: 9999px; background: #22c55e; animation: mon-pulse 2s infinite; }
        @keyframes mon-pulse { 0%,100% { opacity: 1 } 50% { opacity: .3 } }

        /* Top: stat cards + donut */
        .mon-top { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 1024px) { .mon-top { grid-template-columns: 1.6fr 1fr; } }
        .mon-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        @media (min-width: 640px) { .mon-stats { grid-template-columns: repeat(3, 1fr); } }
        .mon-card {
            background: var(--bg); border: 1px solid var(--line); border-radius: 1.25rem; padding: 1.1rem 1.25rem;
            position: relative; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .mon-card::after {
            content: ""; position: absolute; right: -2rem; bottom: -2rem; width: 6rem; height: 6rem;
            border-radius: 9999px; background: var(--accent, var(--soft)); opacity: .12;
        }
        .mon-icon {
            width: 2.5rem; height: 2.5rem; border-radius: .75rem; display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--accent, #94a3b8) 15%, transparent); color: var(--accent, #94a3b8); margin-bottom: .6rem;
        }
        .mon-icon svg { width: 1.3rem; height: 1.3rem; }
        .mon-num { font-size: 1.9rem; font-weight: 700; line-height: 1; }
        .mon-lbl { font-size: .85rem; color: var(--muted); margin-top: .3rem; }
        .mon-card.alert { border-color: #d97706; }
        .dark .mon-card.alert { border-color: #fbbf24; }
        .mon-action-card { min-height: 44px; width: 100%; color: inherit; text-align: left; cursor: pointer; }
        .mon-action-card:hover { border-color: var(--primary); }
        .mon-action-card[aria-pressed="true"] { border-color: #b45309; box-shadow: 0 0 0 2px color-mix(in srgb, #b45309 20%, transparent); }

        /* Donut */
        .mon-donut-card { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; justify-content: center; }
        .mon-donut { width: 9rem; height: 9rem; border-radius: 9999px; position: relative; flex-shrink: 0; }
        .mon-donut::after {
            content: ""; position: absolute; inset: 1.9rem; background: var(--bg); border-radius: 9999px;
        }
        .mon-donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1; }
        .mon-donut-center b { font-size: 1.5rem; }
        .mon-donut-center span { font-size: .7rem; color: var(--muted); }
        .mon-legend { display: flex; flex-direction: column; gap: .4rem; font-size: .85rem; }
        .mon-legend div { display: flex; align-items: center; gap: .5rem; }
        .mon-legend i { width: .7rem; height: .7rem; border-radius: 9999px; display: inline-block; }
        .mon-legend b { margin-left: auto; padding-left: 1rem; }

        /* Section header */
        .mon-head { display: flex; flex-direction: column; gap: .15rem; }
        .mon-head h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }
        .mon-head p { font-size: .8rem; color: var(--muted); margin: 0; }

        /* Session list */
        .mon-list { display: flex; flex-direction: column; gap: 1rem; }
        .mon-item {
            display: flex; gap: 1.25rem; background: var(--soft); border-radius: 1.25rem; padding: 1rem;
            border: 1px solid transparent; transition: border-color .15s, transform .15s;
        }
        .mon-item:hover { border-color: var(--primary); }
        .mon-time {
            width: 6.5rem; flex-shrink: 0; border-radius: 1rem; background: var(--bg); border: 1px solid var(--line);
            display: flex; flex-direction: column; align-items: center; justify-content: center; padding: .75rem .25rem; text-align: center;
            border-left: 5px solid var(--accent);
        }
        .mon-time b { font-size: 1.05rem; }
        .mon-time span { font-size: .72rem; color: var(--muted); }
        .mon-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .55rem; }
        .mon-title { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
        .mon-title h4 { font-size: 1.02rem; font-weight: 600; margin: 0; color: var(--primary); text-transform: uppercase; letter-spacing: .01em; }
        .mon-meta { display: flex; flex-wrap: wrap; gap: .4rem 1.25rem; font-size: .85rem; color: var(--muted); }
        .mon-meta span { display: inline-flex; align-items: center; gap: .35rem; }
        .mon-meta svg { width: 1rem; height: 1rem; }
        .mon-foot { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-top: .15rem; }
        .mon-badge {
            display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .8rem; border-radius: 9999px; font-size: .78rem; font-weight: 500;
            background: color-mix(in srgb, var(--accent) 14%, var(--bg)); color: var(--accent-text);
        }
        .mon-badge svg { width: 1rem; height: 1rem; flex-shrink: 0; }
        .mon-actions { margin-left: auto; display: flex; gap: .5rem; flex-wrap: wrap; }
        .mon-actions button { min-height: 44px; }
        .mon-item-actionable { border-color: #b45309; }
        .dark .mon-item-actionable { border-color: #fbbf24; }
        .mon-empty { text-align: center; padding: 2.5rem 1rem; color: var(--muted); background: var(--soft); border-radius: 1.25rem; }

        @media (max-width: 640px) {
            .mon-item { flex-direction: column; }
            .mon-time { width: 100%; flex-direction: row; gap: .5rem; border-left-width: 1px; border-top: 5px solid var(--accent); }
            .mon-actions { margin-left: 0; }
            .mon-live { margin-left: 0; }
            .mon-input, .mon-select { width: 100%; min-width: 0; }
            .mon-field { width: 100%; }
        }
    </style>

    <div class="mon" wire:poll.30s x-data="{
        search: '',
        filter: 'all',
        hasVisibleRows() {
            return Array.from(this.$refs.sessionList.querySelectorAll('.mon-item')).some((row) => {
                const categoryMatches = this.filter === 'all'
                    || (this.filter === 'needs_action'
                        ? row.dataset.needsAction === 'true'
                        : this.filter === row.dataset.cat);

                return categoryMatches && (this.search === '' || row.dataset.text.includes(this.search.toLowerCase()));
            });
        },
    }">

        {{-- TOOLBAR --}}
        <div class="mon-toolbar" role="group" aria-label="Pencarian dan filter sesi">
            <div class="mon-field">
                <label for="monitoring-status-filter">Filter status</label>
                <select id="monitoring-status-filter" class="mon-select" x-model="filter">
                    <option value="all">Semua status</option>
                    <option value="needs_action">Perlu tindakan</option>
                    @foreach ($meta as $key => $m)
                        <option value="{{ $key }}">{{ $m['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mon-field">
                <label for="monitoring-session-search">Cari sesi</label>
                <input id="monitoring-session-search" type="search" class="mon-input" placeholder="Cari guru, kelas, mata pelajaran..." x-model="search">
            </div>
            <div class="mon-live"><span class="mon-dot" aria-hidden="true"></span><span aria-live="polite">Diperbarui otomatis tiap 30 detik</span></div>
        </div>

        {{-- RINGKASAN + DONUT --}}
        <div class="mon-top">
            <div class="mon-stats">
                <button type="button" class="mon-card mon-action-card alert"
                        style="--accent:#b45309"
                        x-on:click="filter = 'needs_action'; $nextTick(() => $refs.sessionList.focus())"
                        x-bind:aria-pressed="filter === 'needs_action'"
                        aria-label="Tampilkan {{ $stats['needs_action'] }} sesi yang perlu tindakan">
                    <span class="mon-icon" aria-hidden="true">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.3 3.37c-.87 1.5.2 3.38 1.93 3.38h14.74c1.73 0 2.8-1.88 1.93-3.38L13.93 3.99a2.25 2.25 0 00-3.86 0L2.7 16.12zM12 15.75h.01"/></svg>
                    </span>
                    <span class="mon-num">{{ $stats['needs_action'] }}</span>
                    <span class="mon-lbl">Perlu Tindakan — tekan untuk melihat</span>
                </button>

                @php
                    $icons = [
                        'unconfirmed' => 'M9.88 9.88a3 3 0 114.24 0c-.6.6-1.12 1.1-1.12 2.12M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'present' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'late' => 'M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'permission' => 'M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5a1.13 1.13 0 01-1.12-1.13v-1.5a3.38 3.38 0 00-3.38-3.37H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z',
                        'absent' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    ];
                @endphp

                @foreach ($meta as $key => $m)
                    <div class="mon-card" style="--accent:{{ $m['color'] }}">
                        <div class="mon-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$key] }}"/></svg>
                        </div>
                        <div class="mon-num">{{ $stats[$key] }}</div>
                        <div class="mon-lbl">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mon-card mon-donut-card">
                <div class="mon-donut" style="background: {{ $donut }}">
                    <div class="mon-donut-center">
                        <b>{{ $total }}</b>
                        <span>Total Sesi</span>
                    </div>
                </div>
                <div class="mon-legend">
                    @foreach ($meta as $key => $m)
                        <div><i style="background:{{ $m['color'] }}"></i> {{ $m['label'] }} <b>{{ $stats[$key] }}</b></div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- DAFTAR SESI --}}
        <div class="mon-head">
            <h3>Jadwal &amp; Status Sesi Hari Ini</h3>
            <p>{{ count($rows) }} sesi terjadwal; {{ $stats['needs_action'] }} perlu tindakan</p>
        </div>

        <div class="mon-list" id="monitoring-sessions" x-ref="sessionList" tabindex="-1">
            @forelse ($rows as $row)
                @php
                    $cat = $classify($row);
                    $color = $meta[$cat]['color'];
                    $needsAction = data_get($row, 'needsAction', false);
                    $start = $get($row, ['start_time', 'schedule.start_time', 'jam_mulai'], null);
                    $end = $get($row, ['end_time', 'schedule.end_time', 'jam_selesai'], null);
                    $timeText = $get($row, ['time', 'time_range', 'jam'], null);
                    if (! $timeText) {
                        $timeText = ($start && $end) ? substr($start, 0, 5) . ' - ' . substr($end, 0, 5) : '-';
                    }
                    $class = $get($row, ['class_name', 'class.name', 'schoolClass.class_name', 'schedule.schoolClass.class_name', 'schedule.class.name', 'kelas']);
                    $subject = $get($row, ['subject_name', 'subject.name', 'schedule.subject.subject_name', 'schedule.subject.name', 'mapel']);
                    $teacher = $get($row, ['teacher_name', 'teacher.name', 'schedule.teacher.full_name', 'schedule.teacher.name', 'guru']);
                    $statusLabel = $meta[$cat]['label'];
                    $scheduleId = $get($row, ['schedule_id', 'schedule.id', 'id'], null);
                    $reportId = $get($row, ['report_id', 'report.id'], null);
                    $followUp = $get($row, ['follow_up_status', 'report.follow_up_status'], null);
                    $searchText = strtolower("$class $subject $teacher $timeText $statusLabel");
                @endphp

                <div class="mon-item {{ $needsAction ? 'mon-item-actionable' : '' }}"
                     style="--accent:{{ $color }}; --accent-text:{{ match ($cat) {
                        'unconfirmed' => '#475569',
                        'present' => '#166534',
                        'late' => '#92400e',
                        'permission' => '#1e40af',
                        'absent' => '#991b1b',
                     } }}"
                     data-text="{{ $searchText }}" data-cat="{{ $cat }}" data-needs-action="{{ $needsAction ? 'true' : 'false' }}"
                     x-show="(filter === 'all' || (filter === 'needs_action' ? $el.dataset.needsAction === 'true' : filter === $el.dataset.cat)) && (search === '' || $el.dataset.text.includes(search.toLowerCase()))">

                    <div class="mon-time">
                        <b>{{ $timeText }}</b>
                        <span>Jam Pelajaran</span>
                    </div>

                    <div class="mon-body">
                        <div class="mon-title">
                            <h4>{{ $subject }} — {{ $class }}</h4>
                        </div>

                        <div class="mon-meta">
                            <span>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0"/></svg>
                                Guru: {{ $teacher }}
                            </span>
                            <span>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15v18h-15zM9 7.5h6M9 12h6"/></svg>
                                Kelas: {{ $class }}
                            </span>
                        </div>

                        <div class="mon-foot">
                            <span class="mon-badge">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$cat] }}"/>
                                </svg>
                                {{ $statusLabel }}
                            </span>

                            <div class="mon-actions">
                                @if ($scheduleId && ! $reportId)
                                    {{ ($this->reportAction)(['schedule_id' => $scheduleId]) }}
                                @endif
                                @if ($reportId)
                                    {{ ($this->updateFollowUpAction)(['report_id' => $reportId, 'current_status' => $followUp]) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mon-empty">Belum ada jadwal sesi untuk hari ini.</div>
            @endforelse
            @if ($rows->isNotEmpty())
                <p class="mon-empty" x-cloak x-show="!hasVisibleRows()">
                    Tidak ada sesi yang cocok dengan pencarian atau filter ini.
                </p>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>