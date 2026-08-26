<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aliran KELUAR: staf BeDaie memilih lokasi sasaran dan mengejarnya
        // sendiri — pelengkap kepada aliran MASUK (permohonan awam).
        Schema::create('outreach_targets', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('reference_no', 32)->unique();

            $table->string('name');
            $table->string('type', 24)->default('masjid');
            $table->foreignId('state_id')->constrained();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('postcode', 8)->nullable();
            $table->string('google_maps_url', 500)->nullable();
            $table->unsignedInteger('estimated_attendees')->nullable();

            // Kontak lokasi
            $table->string('contact_name')->nullable();
            $table->string('contact_role', 120)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_note')->nullable();
            $table->timestamp('contact_found_at')->nullable();

            // Dari mana sasaran ini datang
            $table->string('source', 32)->default('staf_terus')->index();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referrer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referrer_name')->nullable();
            $table->string('referrer_phone', 20)->nullable();

            // Siapa yang mengejar
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('stage', 32)->default('sasaran')->index();
            $table->timestamp('stage_changed_at')->nullable();
            $table->string('priority', 16)->default('sederhana')->index();

            $table->date('next_action_at')->nullable()->index();
            $table->string('next_action_note')->nullable();
            $table->text('notes')->nullable();
            $table->string('closed_reason')->nullable();

            // Apabila berjaya, sasaran menjadi permohonan rasmi.
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('won_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage', 'assigned_to']);
            $table->index(['state_id', 'stage']);
        });

        Schema::create('outreach_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24)->default('nota');
            $table->text('body')->nullable();
            $table->string('outcome')->nullable();
            $table->string('from_stage', 32)->nullable();
            $table->string('to_stage', 32)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_activities');
        Schema::dropIfExists('outreach_targets');
    }
};
