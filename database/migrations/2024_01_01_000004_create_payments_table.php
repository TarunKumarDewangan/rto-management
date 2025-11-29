<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('remarks')->nullable();

            // Foreign Keys
            $table->foreignId('tax_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('pucc_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('fitness_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('vltd_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('permit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('speed_governor_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
