<?php

namespace Tests\Feature;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\User;
use App\Services\AuditIntegrityChecker;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    private string $checkpointPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Checkpoint khusus test, jangan menyentuh berkas asli.
        $this->checkpointPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'audit-cp-'.uniqid().'.json';
        config(['audit.checkpoint_path' => $this->checkpointPath]);
    }

    protected function tearDown(): void
    {
        @unlink($this->checkpointPath);
        parent::tearDown();
    }

    private function makeEntries(int $n = 3): array
    {
        $entries = [];
        for ($i = 1; $i <= $n; $i++) {
            $entries[] = app(AuditLogger::class)->log(
                AuditEventType::DataCreated,
                targetType: 'teacher',
                targetId: $i,
                after: ['name' => "Guru {$i}"],
            );
        }

        return $entries;
    }

    public function test_fr12_entry_contains_required_fields(): void
    {
        $actor = User::factory()->create();

        $entry = app(AuditLogger::class)->log(
            AuditEventType::AttendanceRecorded,
            AuditResult::Success,
            actorId: $actor->id,
            targetType: 'attendance',
            targetId: 10,
            after: ['status' => 'hadir'],
        );

        $this->assertSame($actor->id, $entry->actor_user_id);
        $this->assertSame(AuditEventType::AttendanceRecorded, $entry->event_type);
        $this->assertSame(AuditResult::Success, $entry->action_result);
        $this->assertNotNull($entry->occurred_at);
        $this->assertSame(64, strlen($entry->entry_hash));
    }

    public function test_t10_sensitive_keys_are_never_stored(): void
    {
        $entry = app(AuditLogger::class)->log(
            AuditEventType::DataCreated,
            after: ['name' => 'x', 'password' => 'rahasia123', 'nested' => ['token' => 'abc123', 'ok' => 1]],
        );

        $stored = json_encode($entry->fresh()->after_data);

        $this->assertStringNotContainsString('rahasia123', $stored);
        $this->assertStringNotContainsString('abc123', $stored);
        $this->assertStringContainsString('"ok":1', $stored);
    }

    public function test_fr13_entries_are_linked_by_previous_hash(): void
    {
        [$first, $second] = $this->makeEntries(2);

        $this->assertNull($first->previous_hash);
        $this->assertSame($first->entry_hash, $second->previous_hash);
    }

    public function test_fr13_untouched_chain_is_valid(): void
    {
        $this->makeEntries(3);

        $result = app(AuditIntegrityChecker::class)->verify();

        $this->assertTrue($result['ok'], implode(' | ', $result['problems']));
        $this->assertSame(3, $result['checked']);
    }

    public function test_t11_modified_entry_is_detected(): void
    {
        [, $second] = $this->makeEntries(3);

        DB::table('audit_logs')->where('id', $second->id)->update(['reason' => 'diubah langsung']);

        $this->assertFalse(app(AuditIntegrityChecker::class)->verify()['ok']);
    }

    public function test_t11_deleted_middle_entry_is_detected(): void
    {
        [, $second] = $this->makeEntries(3);

        DB::table('audit_logs')->where('id', $second->id)->delete();

        $this->assertFalse(app(AuditIntegrityChecker::class)->verify()['ok']);
    }

    public function test_t11_deleted_last_entry_is_detected_only_via_checkpoint(): void
    {
        [,, $third] = $this->makeEntries(3);

        DB::table('audit_logs')->where('id', $third->id)->delete();

        $result = app(AuditIntegrityChecker::class)->verify();

        $this->assertFalse($result['ok']);
    }
}
