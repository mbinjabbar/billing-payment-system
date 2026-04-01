<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); 
            $table->string('name'); 
            $table->decimal('standard_charge', 10, 2); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_masters');
    }
};