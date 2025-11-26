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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('company')->nullable();
            $table->string('type')->nullable(); // 1st Party / 3rd Party
            $table->decimal('actual_amount', 10, 2)->nullable(); // New Field
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date'); // Mandatory (Upto)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
