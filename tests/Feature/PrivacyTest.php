<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_nfr05_audit_logs_store_no_ip_or_user_agent(): void
    {
        $columns = Schema::getColumnListing('audit_logs');

        $this->assertNotContains('ip_address', $columns);
        $this->assertNotContains('user_agent', $columns);
    }

    public function test_nfr05_personal_data_columns_are_limited_to_the_approved_set(): void
    {
        $this->assertEqualsCanonicalizing(
            ['id', 'user_id', 'teacher_code', 'full_name', 'is_active', 'created_at', 'updated_at'],
            Schema::getColumnListing('teachers'),
        );

        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'username', 'email', 'email_verified_at', 'password', 'is_active', 'remember_token', 'created_at', 'updated_at'],
            Schema::getColumnListing('users'),
        );
    }
}
