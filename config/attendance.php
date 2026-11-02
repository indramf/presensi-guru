<?php

return [
    // ASUMSI (D1). Konfirmasi dengan sekolah.
    'open_before_minutes' => (int) env('ATTENDANCE_OPEN_BEFORE', 15),
    'late_after_minutes' => (int) env('ATTENDANCE_LATE_AFTER', 10),
];
