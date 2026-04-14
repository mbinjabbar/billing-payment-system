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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->index()->constrained('visits')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('insurance_firm_id')->nullable()->constrained('insurance_firms');
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->json('procedure_codes')->nullable();
            $table->decimal('charges', 10, 2);
            $table->decimal('insurance_coverage', 10, 2)->default(0);
            $table->decimal('bill_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('outstanding_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', ['Draft','Pending','Partial','Paid','Cancelled','Written Off']);
            $table->string('generated_document_path')->nullable();
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
