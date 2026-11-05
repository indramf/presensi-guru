<x-filament-panels::page>
    <div wire:poll.60s class="space-y-4">
        
        @php
            $schedules = $this->schedules;
            $attendanceService = app(\App\Services\AttendanceService::class);
        @endphp

        @if($schedules->isEmpty())
            <div class="p-6 text-center bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-gray-500 dark:text-gray-400">Belum ada presensi atau jadwal mengajar untuk hari ini.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($schedules as $schedule)
                    @php
                        $attendance = $schedule->attendances->first();
                        $sessionState = $attendanceService->sessionState($schedule, $attendance, now('Asia/Jakarta'));

                        $badgeLabel = match ($sessionState) {
                            \App\Enums\SessionState::Recorded => match ($attendance?->status) {
                                \App\Enums\AttendanceStatus::Hadir => 'Hadir',
                                \App\Enums\AttendanceStatus::Terlambat => 'Terlambat',
                                \App\Enums\AttendanceStatus::Izin => 'Izin',
                                \App\Enums\AttendanceStatus::TidakHadir => 'Tidak hadir',
                                default => 'Sudah diisi',
                            },
                            \App\Enums\SessionState::Open => 'Dapat diisi',
                            \App\Enums\SessionState::Closed => 'Belum terkonfirmasi',
                            \App\Enums\SessionState::NotOpen => 'Belum dibuka',
                        };

                        $badgeColor = match ($sessionState) {
                            \App\Enums\SessionState::Recorded => match ($attendance?->status) {
                                \App\Enums\AttendanceStatus::Hadir => 'success',
                                \App\Enums\AttendanceStatus::Terlambat => 'warning',
                                \App\Enums\AttendanceStatus::Izin => 'info',
                                \App\Enums\AttendanceStatus::TidakHadir => 'danger',
                                default => 'gray',
                            },
                            \App\Enums\SessionState::Open => 'info',
                            default => 'gray',
                        };

                        $badgeIcon = match ($sessionState) {
                            \App\Enums\SessionState::Recorded => match ($attendance?->status) {
                                \App\Enums\AttendanceStatus::Hadir => 'heroicon-o-check-circle',
                                \App\Enums\AttendanceStatus::Terlambat => 'heroicon-o-clock',
                                \App\Enums\AttendanceStatus::Izin => 'heroicon-o-document-text',
                                \App\Enums\AttendanceStatus::TidakHadir => 'heroicon-o-x-circle',
                                default => 'heroicon-o-check-circle',
                            },
                            \App\Enums\SessionState::Open => 'heroicon-o-pencil-square',
                            default => 'heroicon-o-question-mark-circle',
                        };
                    @endphp

                    <x-filament::section>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                        {{ $schedule->subject->subject_name ?? 'Mata Pelajaran' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Kelas: {{ $schedule->schoolClass->class_name ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-950 dark:text-white">
                                        {{ \Carbon\Carbon::parse($schedule->start_time, 'Asia/Jakarta')->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($schedule->end_time, 'Asia/Jakarta')->format('H:i') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <x-filament::badge :color="$badgeColor" :icon="$badgeIcon">
                                        {{ $badgeLabel }}
                                    </x-filament::badge>
                                </div>

                                @if($sessionState === \App\Enums\SessionState::Open)
                                    <x-filament::button 
                                        wire:click="recordAttendance({{ $schedule->id }})"
                                        class="min-h-11 min-w-11">
                                        Isi Presensi
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>