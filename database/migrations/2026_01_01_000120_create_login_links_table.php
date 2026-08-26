<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Log masuk sekali-guna melalui pautan. WhatsApp ialah saluran utama
        // pengguna kami; ramai Penggerak tidak mempunyai e-mel yang aktif,
        // dan akaun mereka dicipta automatik tanpa kata laluan yang diketahui.
        Schema::create('login_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 24)->default('whatsapp');
            $table->string('requested_ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_links');
    }
};
