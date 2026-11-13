<div class="mon-toolbar" role="group" aria-label="Pencarian dan filter sesi">
    <div class="mon-field">
        <label for="monitoring-status-filter">Filter status</label>
        <select id="monitoring-status-filter" class="mon-select" x-model="filter">
            <option value="all">Semua status</option>
            <option value="needs_action">Perlu tindakan</option>
            <option value="ongoing">Sedang berlangsung</option>
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