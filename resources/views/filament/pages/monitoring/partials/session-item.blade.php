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
$accentText = match ($cat) {
'menunggu' => '#64748b',
'unconfirmed' => '#475569',
'present' => '#166534',
'late' => '#92400e',
'permission' => '#1e40af',
'absent' => '#991b1b',
};
$itemStyle = [
    '--accent: '.$color,
    '--accent-text: '.$accentText,
];
$searchText = strtolower("$class $subject $teacher $timeText $statusLabel");
@endphp

<div class="mon-item {{ $needsAction ? 'mon-item-actionable' : '' }}"
    @style($itemStyle)
    data-text="{{ $searchText }}"
    data-cat="{{ $cat }}"
    data-needs-action="{{ $needsAction ? 'true' : 'false' }}"
    data-ongoing="{{ data_get($row, 'isOngoing', false) ? 'true' : 'false' }}"
    x-show="(filter === 'all' || (filter === 'needs_action'
    ? $el.dataset.needsAction === 'true'
    : filter === 'ongoing'
    ? $el.dataset.ongoing === 'true'
    : filter === $el.dataset.cat))
    && (search === '' || $el.dataset.text.includes(search.toLowerCase()))">

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
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0" />
                </svg>
                Guru: {{ $teacher }}
            </span>
            <span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15v18h-15zM9 7.5h6M9 12h6" />
                </svg>
                Kelas: {{ $class }}
            </span>
        </div>

        <div class="mon-foot">
            <span class="mon-badge">
                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$cat] }}" />
                </svg>
                {{ $statusLabel }}
            </span>

            <div class="mon-actions">
                @if (
                $scheduleId &&
                ! $reportId &&
                auth()->user()?->can('create', \App\Models\ClassReport::class)
                )
                {{ ($this->reportAction)(['schedule_id' => $scheduleId]) }}
                @endif
                @if ($reportId && $followUp !== 'Selesai')
                {{ ($this->updateFollowUpAction)(['report_id' => $reportId, 'current_status' => $followUp]) }}
                @endif
            </div>
        </div>
    </div>
</div>