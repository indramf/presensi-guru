<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('type', 20)->default('lesson')->after('subject_id');
            $table->string('activity_name')->nullable()->after('type');

            $table->foreignId('teacher_id')->nullable()->change();
            $table->foreignId('subject_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['type', 'activity_name']);

            $table->foreignId('teacher_id')->nullable(false)->change();
            $table->foreignId('subject_id')->nullable(false)->change();
        });
    }
};
