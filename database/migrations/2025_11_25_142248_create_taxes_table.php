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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('tax_mode'); // MTT, QTT, etc.
            $table->decimal('govt_fee', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->string('type')->nullable(); // Vehicle Type override
            $table->date('from_date')->nullable();
            $table->date('upto_date'); // Mandatory
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
