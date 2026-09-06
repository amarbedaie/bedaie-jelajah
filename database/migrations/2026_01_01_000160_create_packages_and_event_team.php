<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pakej jualan dan pasukan bertugas.
 *
 * Pakej ialah katalog boleh guna semula — staf tidak menaip semula
 * "Pakej Tadabbur" untuk setiap program. Pasukan bertugas pula khusus
 * setiap program kerana AJK masjid berbeza setiap tempat.
 *
 * Kedua-duanya perancangan dalaman: Super Admin sahaja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name', 140);
            $table->string('tagline', 200)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->text('items')->nullable();          // satu barang satu baris
            $table->text('pitch')->nullable();          // ayat jualan untuk staf
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('event_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('target_units')->nullable();
            $table->boolean('is_focus')->default(false);   // pakej utama untuk ditolak
            $table->timestamps();

            $table->unique(['event_id', 'package_id']);
        });

        Schema::create('event_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('role', 100);          // "Ketua program", "Kaunter jualan"
            $table->string('name', 140);
            $table->string('phone', 20)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_team_members');
        Schema::dropIfExists('event_package');
        Schema::dropIfExists('packages');
    }
};
