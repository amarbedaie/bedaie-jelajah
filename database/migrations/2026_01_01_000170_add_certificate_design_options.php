<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pilihan reka bentuk sijil (gaya + orientasi A4) pada tiga peringkat:
 * templat (lalai), kategori program, dan program itu sendiri.
 * Tambah sahaja — tiada kolum sedia ada diubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->string('design', 20)->default('klasik')->after('orientation');
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('certificate_design', 20)->nullable();
            $table->string('certificate_orientation', 12)->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('certificate_design', 20)->nullable()->after('certificate_template_id');
            $table->string('certificate_orientation', 12)->nullable()->after('certificate_design');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['certificate_design', 'certificate_orientation']);
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn(['certificate_design', 'certificate_orientation']);
        });

        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn('design');
        });
    }
};
