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
        Schema::create('class_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->restrictOnDelete();
            $table->date('report_date');
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->string('report_status', 30);
            $table->text('description')->nullable();
            $table->string('follow_up_status', 20)->default('belum');
            $table->timestamps();

            $table->index(['schedule_id', 'report_date']);
            $table->index('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_reports');
    }
};
