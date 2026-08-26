<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 32)->default('penyertaan');
            $table->string('orientation', 16)->default('landscape');
            $table->string('background_path')->nullable();
            $table->string('accent_color', 16)->default('#8875FF');
            $table->text('intro_text')->nullable();
            $table->text('closing_text')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('signature_title')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('certificate_number', 48)->unique();
            $table->string('verification_token', 64)->unique();

            $table->string('type', 32)->index();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('certificate_template_id')->nullable()->constrained()->nullOnDelete();

            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone', 32)->nullable();
            $table->string('organization_name')->nullable();

            $table->string('event_title')->nullable();
            $table->string('speaker_name')->nullable();
            $table->string('venue_name')->nullable();
            $table->date('event_date')->nullable();
            $table->decimal('learning_hours', 5, 2)->nullable();

            $table->string('status', 24)->default('dikeluarkan')->index();
            $table->foreignId('superseded_by_id')->nullable();
            $table->text('revoke_reason')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'type']);
        });

        Schema::create('certificate_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('superseded_by_id')->references('id')->on('certificates')->nullOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('certificate_template_id')->references('id')->on('certificate_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['certificate_template_id']);
        });
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['superseded_by_id']);
        });
        Schema::dropIfExists('certificate_status_histories');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('certificate_templates');
    }
};
