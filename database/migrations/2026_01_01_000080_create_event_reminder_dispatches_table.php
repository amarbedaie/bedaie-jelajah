<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jejak peringatan yang telah dihantar supaya tugas berjadual
        // boleh dijalankan berulang kali tanpa menghantar mesej berganda.
        Schema::create('event_reminder_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_key', 32);
            $table->timestamp('dispatched_at');
            $table->timestamps();

            $table->unique(['registration_id', 'reminder_key'], 'reminder_once_per_registration');
            $table->index(['event_id', 'reminder_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reminder_dispatches');
    }
};
