<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Vehicle Documents
            $table->integer('days_tax')->default(15);
            $table->integer('days_insurance')->default(15);
            $table->integer('days_fitness')->default(15);
            $table->integer('days_permit')->default(15);
            $table->integer('days_pucc')->default(7);
            $table->integer('days_vltd')->default(15);
            $table->integer('days_speed')->default(15);

            // Separate License Days
            $table->integer('days_ll')->default(30); // Learning License
            $table->integer('days_dl')->default(60); // Driving License (Usually needs more time)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'days_tax',
                'days_insurance',
                'days_fitness',
                'days_permit',
                'days_pucc',
                'days_vltd',
                'days_speed',
                'days_ll',
                'days_dl'
            ]);
        });
    }
};
