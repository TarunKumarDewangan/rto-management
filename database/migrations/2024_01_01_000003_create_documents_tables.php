<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Taxes
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('tax_mode')->nullable();
            $table->decimal('govt_fee', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->string('type')->nullable();
            $table->date('from_date')->nullable();
            $table->date('upto_date'); // Mandatory
            $table->timestamps();
        });

        // 2. Insurances
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('company')->nullable();
            $table->string('type')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date'); // Mandatory
            $table->timestamps();
        });

        // 3. PUCC
        Schema::create('puccs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('pucc_number')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });

        // 4. Fitness
        Schema::create('fitnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('fitness_no')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });

        // 5. Permits
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('permit_number')->nullable();
            $table->string('permit_type')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });

        // 6. VLTD
        Schema::create('vltds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('vendor_name')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });

        // 7. Speed Governors
        Schema::create('speed_governors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('governor_number')->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speed_governors');
        Schema::dropIfExists('vltds');
        Schema::dropIfExists('permits');
        Schema::dropIfExists('fitnesses');
        Schema::dropIfExists('puccs');
        Schema::dropIfExists('insurances');
        Schema::dropIfExists('taxes');
    }
};
