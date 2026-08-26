<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('reference_no', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Langkah 1 — tentang pemohon
            $table->string('applicant_name');
            $table->string('applicant_phone', 32)->index();
            $table->string('applicant_email')->nullable()->index();
            $table->string('background', 32)->nullable();
            $table->string('background_other')->nullable();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            // Langkah 2 — cadangan lokasi
            $table->string('venue_name');
            $table->text('venue_address')->nullable();
            $table->string('venue_maps_url')->nullable();
            $table->string('venue_consent', 32)->nullable();
            $table->string('venue_pic_name')->nullable();
            $table->string('venue_pic_phone', 32)->nullable();

            // Langkah 3 — cadangan program
            $table->foreignId('event_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('topic')->nullable();
            $table->date('preferred_date_1')->nullable();
            $table->date('preferred_date_2')->nullable();
            $table->string('estimated_attendees', 24)->nullable();
            $table->string('target_audience', 24)->nullable();

            // Langkah 4 — semak & hantar
            $table->text('notes')->nullable();
            $table->timestamp('privacy_consent_at')->nullable();

            // Pengurusan
            $table->string('status', 32)->default('draf')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('event_id')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['state_id', 'status']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('public_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'created_at']);
        });

        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(true);
            $table->string('channel', 24)->default('nota');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'is_internal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('applications');
    }
};
