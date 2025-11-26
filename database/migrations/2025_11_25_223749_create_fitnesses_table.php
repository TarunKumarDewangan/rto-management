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
        Schema::create('fitnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('fitness_no')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until'); // Mandatory (Expiry)
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitnesses');
    }
};
