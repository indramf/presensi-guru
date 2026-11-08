<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\AuditEventType;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceStatusActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_piket_can_change_attendance_status_through_table_action(): void
    {
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();

        $attendance = Attendance::factory()->create([
            'status' => AttendanceStatus::Terlambat,
        ]);

        $this->actingAs($piket);

        Livewire::test(ListAttendances::class)
            ->callTableAction('change_status', $attendance, [
                'status' => AttendanceStatus::Hadir,
                'reason' => 'Guru sudah di kelas sejak awal',
            ]);

        $this->assertSame(
            AttendanceStatus::Hadir,
            $attendance->fresh()->status,
        );

        $log = AuditLog::where(
            'event_type',
            AuditEventType::AttendanceStatusChanged->value,
        )->firstOrFail();

        $this->assertSame('terlambat', $log->before_data['status']);
        $this->assertSame('hadir', $log->after_data['status']);
        $this->assertSame('Guru sudah di kelas sejak awal', $log->reason);
        $this->assertSame($piket->id, $log->actor_user_id);
    }
}
