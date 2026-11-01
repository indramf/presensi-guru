<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('rule_code', 10);
            $table->string('severity', 10);
            $table->foreignId('related_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('related_audit_log_id')->nullable()->constrained('audit_logs')->restrictOnDelete();
            $table->text('description');
            $table->dateTime('detected_at');
            $table->string('status', 10)->default('baru');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('rule_code');
            $table->index('detected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
