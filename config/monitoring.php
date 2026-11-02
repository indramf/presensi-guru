<?php

return [
    // ASUMSI: sesi tanpa presensi dianggap "belum terkonfirmasi" setelah N menit sejak jam mulai.
    'unconfirmed_after_minutes' => (int) env('MONITORING_UNCONFIRMED_AFTER', 5),
];
