<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Citizens Table
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('mobile_number');
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('relation_type')->nullable();
            $table->string('relation_name')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city_district')->nullable();
            $table->timestamps();
        });

        // 2. Vehicles Table
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained()->onDelete('cascade');
            $table->string('registration_no'); // NOT unique, to allow different agents to add same car
            $table->string('type')->nullable();
            $table->string('make_model')->nullable();
            $table->string('chassis_no')->nullable();
            $table->string('engine_no')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('citizens');
    }
};
