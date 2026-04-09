<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nf2_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('patient_cases')->onDelete('cascade');
            $table->string('policyholder_name')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('claim_number')->nullable();
            $table->date('accident_date')->nullable();
            $table->time('accident_time')->nullable();
            $table->string('accident_location')->nullable();
            $table->text('accident_description')->nullable();
            $table->text('injury_description')->nullable();
            $table->string('vehicle_owner_name')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->enum('vehicle_type', ['Bus', 'Truck', 'Automobile', 'Motorcycle'])->nullable();
            $table->boolean('is_driver')->default(false);
            $table->boolean('is_passenger')->default(false);
            $table->boolean('is_pedestrian')->default(false);
            $table->boolean('is_household_member')->default(false);
            $table->boolean('is_relative_owner')->default(false);
            $table->string('patient_ssn')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('nf2_details');
    }
};