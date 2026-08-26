<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 8)->unique();
            $table->string('region', 32)->default('semenanjung');
            $table->longText('map_path')->nullable();
            $table->decimal('label_x', 8, 2)->nullable();
            $table->decimal('label_y', 8, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['state_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('states');
    }
};
