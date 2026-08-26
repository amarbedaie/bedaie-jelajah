<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64);
            $table->string('channel', 24);
            $table->string('name');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['key', 'channel']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key', 64)->nullable()->index();
            $table->string('channel', 24)->index();
            $table->nullableMorphs('notifiable');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->string('status', 24)->default('queued')->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notifications');
    }
};
