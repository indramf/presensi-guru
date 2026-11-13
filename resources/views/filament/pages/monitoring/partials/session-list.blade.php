<div class="mon-head">
    <h3>Jadwal &amp; Status Sesi Hari Ini</h3>
    <p>{{ count($rows) }} sesi terjadwal; {{ $stats['needs_action'] }} perlu tindakan</p>
</div>

<div class="mon-list" id="monitoring-sessions" x-ref="sessionList" tabindex="-1">
    @forelse ($rows as $row)
    @include('filament.pages.monitoring.partials.session-item')
    @empty
    <div class="mon-empty">Belum ada jadwal sesi untuk hari ini.</div>
    @endforelse
    @if ($rows->isNotEmpty())
    <p class="mon-empty" x-cloak x-show="!hasVisibleRows()">
        Tidak ada sesi yang cocok dengan pencarian atau filter ini.
    </p>
    @endif
</div>