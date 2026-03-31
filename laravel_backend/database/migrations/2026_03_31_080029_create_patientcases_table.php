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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->withForeignKey('patient_id')->references('id')->on('patients');
            $table->enum('case_type', ['New', 'Followup', 'Emergency','Consultation','Surgical','Chronic']);
            $table->enum('case_category', ['General Medicine', ' Pediatrics', 'Cardiology', 'Orthopedics', 'Dermatology','Neurology', 'Gynecology','Ophthalmology', 'ENT', 'Dental', 'Psychiatry' ,'Other']);
            $table->enum('priority', ['Low', 'Normal', 'High','Urgent']);
            $table->enum('status', ['Active', 'Closed','Transfered','On Hold']);
            $table->text('description');
            $table->date('opened_date');
            $table->date('closed_date')->nullable();
            $table->string('referring_doctor')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_cases');
    }
};
