<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $结构) {
            $结构->id();
            $结构->string('mobile');
            $结构->text('message');
            $结构->boolean('status')->default(false);
            $结构->text('response_body')->nullable();
            $结构->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
