<x-filament-panels::page>
    @include('filament.pages.monitoring.partials.styles')

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
                str_contains($s, 'menunggu') || str_contains($s, 'waiting') => 'menunggu',
                str_contains($s, 'belum') || str_contains($s, 'unconfirmed') || trim($s) === '' => 'unconfirmed',
                str_contains($s, 'tidak') || str_contains($s, 'absent') => 'absent',
                str_contains($s, 'terlambat') || str_contains($s, 'late') => 'late',
                str_contains($s, 'izin') || str_contains($s, 'permission') => 'permission',
                str_contains($s, 'hadir') || str_contains($s, 'present') => 'present',
                default => 'unconfirmed',
            };
        };

        $counted = ['menunggu' => 0, 'unconfirmed' => 0, 'present' => 0, 'late' => 0, 'permission' => 0, 'absent' => 0];
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
            'menunggu' => $pick(['menunggu'], 'menunggu'),
            'unconfirmed' => $pick(['unconfirmed', 'not_confirmed', 'belum_terkonfirmasi'], 'unconfirmed'),
            'present' => $pick(['present', 'hadir'], 'present'),
            'late' => $pick(['late', 'terlambat'], 'late'),
            'permission' => $pick(['permission', 'excused', 'izin'], 'permission'),
            'absent' => $pick(['absent', 'tidak_hadir'], 'absent'),
        ];

        $meta = [
            'menunggu' => ['label' => 'Menunggu', 'color' => '#94a3b8'],
            'unconfirmed' => ['label' => 'Belum terkonfirmasi', 'color' => '#64748b'],
            'present' => ['label' => 'Hadir', 'color' => '#15803d'],
            'late' => ['label' => 'Terlambat', 'color' => '#b45309'],
            'permission' => ['label' => 'Izin', 'color' => '#1d4ed8'],
            'absent' => ['label' => 'Tidak Hadir', 'color' => '#b91c1c'],
        ];

        $icons = [
            'menunggu' => 'M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'unconfirmed' => 'M9.88 9.88a3 3 0 114.24 0c-.6.6-1.12 1.1-1.12 2.12M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'present' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'late' => 'M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'permission' => 'M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5a1.13 1.13 0 01-1.12-1.13v-1.5a3.38 3.38 0 00-3.38-3.37H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z',
            'absent' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
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

    <div class="mon" wire:poll.30s x-data="{
        search: '',
        filter: 'all',
        hasVisibleRows() {
            return Array.from(this.$refs.sessionList.querySelectorAll('.mon-item')).some((row) => {
                const categoryMatches = this.filter === 'all'
                    || (this.filter === 'needs_action'
                        ? row.dataset.needsAction === 'true'
                        : this.filter === 'ongoing'
                            ? row.dataset.ongoing === 'true'
                            : this.filter === row.dataset.cat);

                return categoryMatches && (this.search === '' || row.dataset.text.includes(this.search.toLowerCase()));
            });
        },
    }">

        @include('filament.pages.monitoring.partials.toolbar')

        @include('filament.pages.monitoring.partials.summary')

        @include('filament.pages.monitoring.partials.session-list')
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>