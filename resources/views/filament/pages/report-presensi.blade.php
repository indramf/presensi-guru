<x-filament-panels::page>

    <style>
        /* LIGHT MODE */
        :root {
            --report-label: #111827;
            --report-text: #111827;
            --report-heading: #000000;
            --report-border: #e2e8f0;
            --report-input-border: #d1d5db;
            --report-input-bg: #ffffff;
            --report-input-text: #111827;
            --report-table-head: #f1f5f9;
            --report-table-hover: #f8fafc;
            --report-table-bg: #ffffff;
            --report-badge-default-bg: #f1f5f9;
            --report-badge-default-text: #111827;
            color-scheme: light;
        }

        /* DARK MODE */
        .dark {
            --report-label: #f1f5f9;
            --report-text: #f1f5f9;
            --report-heading: #ffffff;
            --report-border: #334155;
            --report-input-border: #475569;
            --report-input-bg: #0f172a;
            --report-input-text: #ffffff;
            --report-table-head: #1e293b;
            --report-table-hover: rgba(148, 163, 184, .07);
            --report-table-bg: #0f172a;
            --report-badge-default-bg: rgba(148, 163, 184, .15);
            --report-badge-default-text: #f1f5f9;
            color-scheme: dark;
        }

        .report-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .report-filters {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px;
        }

        .report-field label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--report-label);
        }

        .report-field input,
        .report-field select {
            display: block;
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid var(--report-input-border);
            border-radius: 8px;
            background: var(--report-input-bg);
            color: var(--report-input-text);
            font-size: 13px;
            outline: none;
            color-scheme: inherit;
        }

        .report-field input:focus,
        .report-field select:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 2px rgba(20, 184, 166, .15);
        }

        .report-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 12px;
        }

        .report-total {
            font-size: 13px;
            color: var(--report-label);
        }

        .report-total strong {
            display: block;
            margin-top: 4px;
            font-size: 28px;
            font-weight: 700;
            color: var(--report-heading);
        }

        .report-table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--report-border);
            border-radius: 10px;
            background: var(--report-table-bg);
        }

        .report-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .report-table th {
            padding: 15px 14px;
            background: var(--report-table-head);
            color: var(--report-heading);
            font-weight: 700;
            white-space: nowrap;
            border-bottom: 1px solid var(--report-border);
        }

        .report-table td {
            padding: 14px;
            border-top: 1px solid var(--report-border);
            color: var(--report-text);
            vertical-align: middle;
        }

        .report-table tbody tr {
            transition: background-color .15s ease;
        }

        .report-table tbody tr:hover {
            background: var(--report-table-hover);
        }

        .report-table .teacher-name {
            color: var(--report-heading);
            font-weight: 600;
        }

        .report-badge {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-hadir {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-terlambat {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-izin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-tidak-hadir {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-default {
            background: var(--report-badge-default-bg);
            color: var(--report-badge-default-text);
        }

        .report-empty {
            padding: 45px 20px !important;
            text-align: center;
            color: var(--report-label) !important;
        }

        .report-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            color: var(--report-label);
            font-size: 12px;
        }

        @media (max-width: 1100px) {
            .report-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .report-filters {
                grid-template-columns: 1fr;
            }

            .report-summary {
                align-items: flex-start;
            }

            .report-footer {
                flex-direction: column;
            }
        }
    </style>

    <div class="report-page">

        {{-- FILTER --}}
        <x-filament::section
            heading="Filter Laporan"
            description="Pilih kriteria untuk menampilkan data presensi guru."
            icon="heroicon-o-funnel">
            <div class="report-filters">

                <div class="report-field">
                    <label>Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom">
                </div>

                <div class="report-field">
                    <label>Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo">
                </div>

                <div class="report-field">
                    <label>Guru</label>
                    <select wire:model.live="teacherId">
                        <option value="">Semua Guru</option>
                        @foreach ($this->teachers as $teacher)
                        <option value="{{ $teacher->id }}">
                            {{ $teacher->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="report-field">
                    <label>Kelas</label>
                    <select wire:model.live="classId">
                        <option value="">Semua Kelas</option>
                        @foreach ($this->classes as $schoolClass)
                        <option value="{{ $schoolClass->id }}">
                            {{ $schoolClass->class_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="report-field">
                    <label>Status Presensi</label>
                    <select wire:model.live="status">
                        <option value="">Semua Status</option>
                        @foreach ($this->statusOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </x-filament::section>

        {{-- DATA PRESENSI --}}
        <x-filament::section
            heading="Data Presensi"
            description="Daftar kehadiran guru berdasarkan filter yang dipilih."
            icon="heroicon-o-clipboard-document-list">
            <div class="report-summary">
                <div class="report-total">
                    Total data ditemukan
                    <strong>{{ $this->rows->count() }}</strong>
                    presensi
                </div>

                @can('attendance_reports.export')
                <x-filament::button
                    wire:click="exportPdf"
                    icon="heroicon-o-document-arrow-down"
                    color="success"
                    wire:loading.attr="disabled"
                    wire:target="exportPdf">
                    <span wire:loading.remove wire:target="exportPdf">
                        Export PDF
                    </span>

                    <span wire:loading wire:target="exportPdf">
                        Menyiapkan PDF...
                    </span>
                </x-filament::button>
                @endcan
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran / Kegiatan</th>
                            <th>Jam</th>
                            <th style="text-align:center">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->rows as $attendance)
                        @php
                        $statusValue = $attendance->status?->value;

                        $statusClass = match ($statusValue) {
                        'hadir' => 'badge-hadir',
                        'terlambat' => 'badge-terlambat',
                        'izin' => 'badge-izin',
                        'tidak_hadir' => 'badge-tidak-hadir',
                        default => 'badge-default',
                        };

                        $statusLabel = match ($statusValue) {
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin' => 'Izin',
                        'tidak_hadir' => 'Tidak Hadir',
                        default => '-',
                        };
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td style="white-space:nowrap">
                                {{ $attendance->attendance_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="teacher-name">
                                {{ $attendance->teacher?->full_name ?? '-' }}
                            </td>

                            <td>
                                {{ $attendance->schedule?->schoolClass?->class_name ?? '-' }}
                            </td>

                            <td>
                                {{ $attendance->schedule?->subject?->subject_name
                                        ?? $attendance->schedule?->activity_name
                                        ?? '-' }}
                            </td>

                            <td style="white-space:nowrap">
                                @if ($attendance->schedule?->start_time && $attendance->schedule?->end_time)
                                {{ substr($attendance->schedule->start_time, 0, 5) }}
                                -
                                {{ substr($attendance->schedule->end_time, 0, 5) }}
                                @else
                                -
                                @endif
                            </td>

                            <td style="text-align:center">
                                <span class="report-badge {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="report-empty">
                                Tidak ada data presensi untuk filter ini.
                                <br>
                                Coba ubah kriteria pencarian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="report-footer">
                <span>
                    Menampilkan {{ $this->rows->count() }} data presensi
                </span>
                <span>
                    Laporan Presensi Guru
                </span>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>