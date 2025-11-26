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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('mobile_number');
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('relation_type')->nullable(); // Father/Husband
            $table->string('relation_name')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city_district')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
