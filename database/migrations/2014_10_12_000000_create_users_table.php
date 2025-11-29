<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user'); // 'admin' or 'user'
            $table->boolean('is_active')->default(true);

            // WhatsApp Config
            $table->string('whatsapp_key')->nullable();
            $table->string('whatsapp_host')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Default Admin (Optional)
        // \App\Models\User::create(['name'=>'Admin','email'=>'admin@rto.com','password'=>bcrypt('password'),'role'=>'admin']);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
