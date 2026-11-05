<?php

use Database\Seeders\BlueprintSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint SOP: senarai semak keperluan sebelum, semasa dan selepas
 * program mengikut jenis lokasi (masjid, sekolah, institusi/kolej/NGO).
 * Item blueprint disalin ke program supaya pasukan boleh tanda siap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('venue_type', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('blueprint_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 12);
            $table->string('title', 200);
            $table->text('details')->nullable();
            $table->smallInteger('due_offset_days')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['blueprint_id', 'phase', 'sort_order']);
        });

        Schema::create('event_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blueprint_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phase', 12);
            $table->string('title', 200);
            $table->text('details')->nullable();
            $table->smallInteger('due_offset_days')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_id', 'phase', 'sort_order']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('blueprint_id')->nullable()->constrained()->nullOnDelete();
        });

        // Tiga blueprint permulaan — hanya dicipta jika belum wujud.
        (new BlueprintSeeder)->run();
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blueprint_id');
        });

        Schema::dropIfExists('event_checklist_items');
        Schema::dropIfExists('blueprint_items');
        Schema::dropIfExists('blueprints');
    }
};
