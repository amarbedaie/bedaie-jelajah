<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perancangan dalaman program: objektif, SOP dan aliran acara.
 *
 * Ini dokumen kerja pasukan BeDaie. Ia TIDAK PERNAH dipaparkan kepada
 * peserta, orang awam, mahupun Penggerak — hanya Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('objectives')->nullable()->after('theme');
            $table->text('sop')->nullable()->after('objectives');
            $table->text('flow_notes')->nullable()->after('sop');
        });

        Schema::create('event_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('time_label', 40)->nullable();   // "8:30 malam"
            $table->string('activity', 200);
            $table->string('responsible', 120)->nullable(); // "Penggerak", "Imam"
            $table->string('note', 250)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_flow_steps');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['objectives', 'sop', 'flow_notes']);
        });
    }
};
