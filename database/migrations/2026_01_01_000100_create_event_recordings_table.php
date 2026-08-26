<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rakaman dan bahan program. Akses dikawal: lazimnya hanya peserta
        // yang benar-benar hadir boleh menontonnya semula.
        Schema::create('event_recordings', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 24)->default('video');      // video | audio | nota | pautan
            $table->string('provider', 24)->default('youtube'); // youtube | vimeo | pautan | fail
            $table->string('url', 500)->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            // Siapa boleh menonton
            $table->string('visibility', 24)->default('hadir'); // hadir | berdaftar | awam
            $table->boolean('is_published')->default(false);
            $table->timestamp('available_from')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_id', 'is_published']);
        });

        // Jejak tontonan — membolehkan laporan "berapa ramai menonton semula".
        Schema::create('recording_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_recording_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->index(['event_recording_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recording_views');
        Schema::dropIfExists('event_recordings');
    }
};
