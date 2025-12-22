<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Agent

            // Basic Details
            $table->string('applicant_name');
            $table->date('dob');
            $table->string('mobile_number');
            $table->string('address')->nullable();
            $table->string('categories')->nullable(); // Stored as comma separated string

            // LL Details
            $table->string('application_no')->nullable();
            $table->string('ll_number')->nullable();
            $table->string('ll_status')->nullable();
            $table->date('ll_valid_from')->nullable();
            $table->date('ll_valid_upto')->nullable();

            // DL Details
            $table->string('dl_app_no')->nullable();
            $table->string('dl_number')->nullable();
            $table->string('dl_status')->nullable();
            $table->date('dl_valid_from')->nullable();
            $table->date('dl_valid_upto')->nullable();

            // Financials
            $table->decimal('ll_bill_amount', 10, 2)->default(0);
            $table->decimal('ll_paid_amount', 10, 2)->default(0);
            $table->decimal('dl_bill_amount', 10, 2)->default(0);
            $table->decimal('dl_paid_amount', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
