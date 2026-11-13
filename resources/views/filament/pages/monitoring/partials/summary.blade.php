<div class="mon-top">
    <div class="mon-stats">
        <button type="button" class="mon-card mon-action-card alert"
            style="--accent:#b45309"
            x-on:click="filter = 'needs_action'; $nextTick(() => $refs.sessionList.focus())"
            x-bind:aria-pressed="filter === 'needs_action'"
            aria-label="Tampilkan {{ $stats['needs_action'] }} sesi yang perlu tindakan">
            <span class="mon-icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.3 3.37c-.87 1.5.2 3.38 1.93 3.38h14.74c1.73 0 2.8-1.88 1.93-3.38L13.93 3.99a2.25 2.25 0 00-3.86 0L2.7 16.12zM12 15.75h.01" />
                </svg>
            </span>
            <span class="mon-num">{{ $stats['needs_action'] }}</span>
            <span class="mon-lbl">Perlu Tindakan — tekan untuk melihat</span>
        </button>

        @foreach ($meta as $key => $m)
        @php
        $cardStyle = ['--accent: '.$m['color']];
        @endphp

        <div class="mon-card" @style($cardStyle)>
            <div class="mon-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$key] }}" />
                </svg>
            </div>
            <div class="mon-num">{{ $stats[$key] }}</div>
            <div class="mon-lbl">{{ $m['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="mon-card mon-donut-card">
        @php
        $donutStyle = ['background: '.$donut];
        @endphp

        <div class="mon-donut" @style($donutStyle)>
            <div class="mon-donut-center">
                <b>{{ $total }}</b>
                <span>Total Sesi</span>
            </div>
        </div>
        <div class="mon-legend">
            @foreach ($meta as $key => $m)
            @php
            $legendStyle = ['background: '.$m['color']];
            @endphp

            <div>
                <i @style($legendStyle)></i>
                {{ $m['label'] }}
                <b>{{ $stats[$key] }}</b>
            </div>
            @endforeach
        </div>
    </div>
</div>