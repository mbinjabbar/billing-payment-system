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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patientcase_id')->withForeignKey('patientcase_id')->references('id')->on('patientcases');
            $table->enum('appointment_type', ['Initial', 'Follow-up', 'Consultation', 'Procedure', 'Emergency', 'Telehealth', 'Routine Checkup']);
            $table->enum('appointment_status', ['Scheduled', 'Confirmed', 'Checked In', 'In Progress', 'Completed', 'Cancelled', 'No Show', 'Rescheduled']);
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->integer('duration_minutes')->default(30);
            $table->unsignedBigInteger('doctor_id')->withForeignKey('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->string('doctor_name')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
