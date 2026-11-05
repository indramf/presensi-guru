<?php

namespace Tests\Feature;

use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InputSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'admin@presensi.test')->firstOrFail());
    }

    public function test_t08_script_payload_is_escaped_not_executed(): void
    {
        $payload = '<script>alert("xss")</script>';
        Teacher::factory()->create(['full_name' => $payload]);

        Livewire::test(ListTeachers::class)
            ->assertSee($payload)          // tampil sebagai teks (sudah di-escape)
            ->assertDontSeeHtml($payload); // tidak muncul sebagai tag mentah
    }

    public function test_t08_sql_injection_in_search_does_not_return_everything(): void
    {
        $this->assertGreaterThan(0, Teacher::count());

        Livewire::test(ListTeachers::class)
            ->searchTable("' OR '1'='1")
            ->assertCountTableRecords(0);
    }

    public function test_t08_invalid_input_is_rejected_with_validation_errors(): void
    {
        Livewire::test(CreateTeacher::class)
            ->fillForm(['teacher_code' => str_repeat('X', 100), 'full_name' => ''])
            ->call('create')
            ->assertHasFormErrors();
    }
}
