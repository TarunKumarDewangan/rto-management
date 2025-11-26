<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('puccs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('pucc_number')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable(); // Added
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable(); // Start Date
            $table->date('valid_until'); // Expiry Date (Mandatory)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puccs');
    }
};
