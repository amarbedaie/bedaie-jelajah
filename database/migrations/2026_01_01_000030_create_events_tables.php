<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('short_code', 16)->unique();
            $table->string('slug')->unique();

            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('theme')->nullable();
            $table->text('description')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('hero_image_path')->nullable();

            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('doors_open_at')->nullable();

            $table->string('pricing_mode', 24)->default('percuma');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 8)->default('MYR');

            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('allow_waiting_list')->default(true);
            $table->boolean('allow_guest_registration')->default(true);
            $table->unsignedTinyInteger('max_guests_per_registration')->default(4);
            $table->boolean('requires_approval')->default(false);
            $table->string('invite_code', 32)->nullable();

            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();

            $table->string('status', 24)->default('draf')->index();
            $table->string('target_audience', 24)->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('contact_phone', 32)->nullable();

            $table->boolean('certificate_enabled')->default(true);
            $table->foreignId('certificate_template_id')->nullable();
            $table->unsignedTinyInteger('min_attendance_percent')->default(100);
            $table->boolean('feedback_required_for_certificate')->default(false);
            $table->decimal('learning_hours', 5, 2)->default(2);

            $table->json('tentative')->nullable();
            $table->json('faqs')->nullable();
            $table->text('parking_info')->nullable();

            // Cache kiraan supaya senarai & peta laju
            $table->unsignedInteger('registered_count')->default(0);
            $table->unsignedInteger('attended_count')->default(0);
            $table->unsignedInteger('waitlist_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at']);
            $table->index(['state_id', 'status']);
        });

        Schema::create('event_mobilizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 24)->default('utama');
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('event_mobilizers');
        Schema::dropIfExists('events');
    }
};
