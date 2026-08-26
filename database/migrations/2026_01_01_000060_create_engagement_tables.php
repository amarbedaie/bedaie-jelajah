<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('most_beneficial')->nullable();
            $table->text('next_topic')->nullable();
            $table->boolean('wants_advanced')->default(false);
            $table->text('comments')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique('registration_id');
            $table->index(['event_id', 'rating']);
        });

        Schema::create('event_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['event_id', 'is_approved']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role_label')->nullable();
            $table->text('quote');
            $table->string('photo_path')->nullable();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('area_interest_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->index();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('postcode', 10)->nullable()->index();
            $table->foreignId('event_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('baharu')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['state_id', 'district_id']);
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 24)->default('rakan');
            $table->string('logo_path')->nullable();
            $table->string('website_url')->nullable();
            $table->string('tier', 24)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('area_interest_requests');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('event_galleries');
        Schema::dropIfExists('feedback');
    }
};
