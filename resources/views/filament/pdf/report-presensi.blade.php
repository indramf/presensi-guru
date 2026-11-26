<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Guru</title>


    <style>
        @page {
            size: A4 portrait;
            margin: 35px 30px 45px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000000;
            background: #ffffff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0 0 5px;
            text-transform: uppercase;
            color: #000000;
        }

        .header h2 {
            font-size: 13px;
            margin: 0 0 5px;
            font-weight: normal;
            color: #000000;
        }

        .header p {
            margin: 0;
            font-size: 9px;
            color: #000000;
        }

        .report-title {
            text-align: center;
            margin: 18px 0;
            color: #000000;
        }

        .report-title h3 {
            font-size: 14px;
            margin: 0 0 5px;
        }

        .report-title p {
            margin: 0;
            font-size: 9px;
        }

        .filter-info {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            background: #ffffff;
        }

        .filter-info td {
            padding: 6px;
            border: 1px solid #999999;
            color: #000000;
            background: #ffffff;
        }

        .filter-info td:first-child {
            width: 130px;
            font-weight: bold;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #ffffff;
        }

        table.data th {
            background: #ffffff;
            color: #000000;
            padding: 9px 6px;
            border: 1px solid #777777;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }

        table.data td {
            padding: 8px 6px;
            border: 1px solid #999999;
            font-size: 9px;
            color: #000000;
            background: #ffffff;
        }

        .center {
            text-align: center;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #999999;
            color: #000000;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #000000;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>SMK Fadris</h1>
        <h2>Laporan Presensi Guru</h2>
        <p>Dokumen laporan data kehadiran guru</p>
    </div>

    <div class="report-title">
        <h3>LAPORAN DATA PRESENSI</h3>
        <p>
            Dicetak pada:
            {{ now()->translatedFormat('d F Y, H:i') }}
        </p>
    </div>

    <table class="filter-info">
        <tr>
            <td>Periode</td>
            <td>
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Semua tanggal' }}
                -
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Semua tanggal' }}
            </td>
        </tr>
        <tr>
            <td>Guru</td>
            <td>{{ $teacherName ?? 'Semua Guru' }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>{{ $className ?? 'Semua Kelas' }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ $status ?: 'Semua Status' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="12%">Tanggal</th>
                <th width="20%">Nama Guru</th>
                <th width="10%">Kelas</th>
                <th width="24%">Mata Pelajaran / Kegiatan</th>
                <th width="14%">Jam</th>
                <th width="16%">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $attendance)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>

                <td class="center">
                    {{ $attendance->attendance_date?->format('d/m/Y') ?? '-' }}
                </td>

                <td>
                    {{ $attendance->teacher?->full_name ?? '-' }}
                </td>

                <td class="center">
                    {{ $attendance->schedule?->schoolClass?->class_name ?? '-' }}
                </td>

                <td>
                    {{ $attendance->schedule?->subject?->subject_name
                            ?? $attendance->schedule?->activity_name
                            ?? '-' }}
                </td>

                <td class="center">
                    @if ($attendance->schedule?->start_time && $attendance->schedule?->end_time)
                    {{ substr($attendance->schedule->start_time, 0, 5) }}
                    -
                    {{ substr($attendance->schedule->end_time, 0, 5) }}
                    @else
                    -
                    @endif
                </td>

                <td class="center">
                    {{ $attendance->status?->value ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center">
                    Tidak ada data presensi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Data Presensi: {{ $rows->count() }} data
    </div>

    <div class="footer">
        Laporan Presensi Guru - SMK Fadris
        | Dicetak {{ now()->format('d/m/Y H:i') }}
    </div>

</body>

</html>