<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('public_token', 64)->unique();
            $table->string('reference_no', 32)->unique();

            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('gender', 16)->nullable();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('guests_count')->default(0);
            $table->string('status', 32)->default('disahkan')->index();
            $table->string('source', 16)->default('online');
            $table->string('invite_code', 32)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('privacy_consent_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'phone']);
            $table->index(['event_id', 'status']);
            $table->index('email');
        });

        Schema::create('registration_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('gender', 16)->nullable();
            $table->string('age_group', 24)->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 32)->default('manual');
            $table->string('gateway_reference')->nullable()->index();
            $table->json('gateway_payload')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 8)->default('MYR');
            $table->string('status', 32)->default('belum_bayar')->index();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->morphs('tokenable');
            $table->string('purpose', 32)->default('checkin');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('method', 16)->default('qr');
            $table->unsignedTinyInteger('guests_present')->default(0);
            $table->string('device_info')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('registration_id');
            $table->index(['event_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('qr_tokens');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('registration_guests');
        Schema::dropIfExists('registrations');
    }
};
