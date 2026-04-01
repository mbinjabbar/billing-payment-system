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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_mode', ['Cash','Check','Bank Transfer','Credit Card','Debit Card','Insurance','Online Payment']);
            $table->string('check_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date');
            $table->enum('payment_status', ['Completed','Pending','Failed','Refunded']);
            $table->string('cheque_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
