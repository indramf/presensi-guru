<?php

use App\Services\Anomaly\Rules;
use App\Services\Anomaly\Rules\AdminActivityRule;


return [
    // ASUMSI (D4). Nilai awal untuk prototipe; evaluasi dan sesuaikan dengan sekolah.
    'an01' => ['threshold' => 5, 'window_minutes' => 5, 'dedupe_minutes' => 30],
    'an04' => ['repeat_threshold' => 3, 'window_minutes' => 10, 'dedupe_minutes' => 10],
    'an06' => ['sudden_change_hours' => 2],
    'an07' => [
        'burst_threshold' => 20,
        'window_minutes' => 10,
        'office_hours' => [env('ANOMALY_OFFICE_START', '06:00'), env('ANOMALY_OFFICE_END', '18:00')],
    ],

    // Satu class per aturan. Urutan tidak berpengaruh.
    'rules' => [
        Rules\FailedLoginRule::class,           // AN-01
        Rules\OutsideWindowRule::class,         // AN-02
        Rules\DuplicateAttendanceRule::class,   // AN-03
        Rules\AccessDeniedRule::class,          // AN-04
        Rules\VerifiedStatusChangeRule::class,  // AN-05
        Rules\SuddenScheduleChangeRule::class,  // AN-06 (a)
        Rules\InactiveOrWrongDayRule::class,    // AN-06 (b)
        AdminActivityRule::class,         // AN-07
    ],
];