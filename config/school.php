<?php

return [
    // Hari sekolah (ISO: 1 = Senin ... 7 = Minggu), urut sesuai tampilan kolom.
    // ASUMSI dari tabel jadwal sekolah: Sabtu sampai Kamis. Konfirmasi ke sekolah.
    'days' => [6, 7, 1, 2, 3, 4],

    // Jam pelajaran dan istirahat. 'type' => 'lesson' atau 'break'.
    'periods' => [
        ['start' => '07:30', 'end' => '08:10', 'type' => 'lesson'],
        ['start' => '08:10', 'end' => '08:50', 'type' => 'lesson'],
        ['start' => '08:50', 'end' => '09:30', 'type' => 'lesson'],
        ['start' => '09:30', 'end' => '10:10', 'type' => 'lesson'],
        ['start' => '10:10', 'end' => '10:30', 'type' => 'break'],
        ['start' => '10:30', 'end' => '11:10', 'type' => 'lesson'],
        ['start' => '11:10', 'end' => '11:50', 'type' => 'lesson'],
        ['start' => '11:50', 'end' => '13:15', 'type' => 'break'],
        ['start' => '13:15', 'end' => '13:50', 'type' => 'lesson'],
        ['start' => '13:50', 'end' => '14:25', 'type' => 'lesson'],
        ['start' => '14:25', 'end' => '15:00', 'type' => 'lesson'],
    ],
];
