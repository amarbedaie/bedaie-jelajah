<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Membezakan kata laluan yang ditetapkan pemilik daripada
            // kata laluan rawak yang dijana semasa akaun dicipta automatik.
            $table->timestamp('password_set_at')->nullable()->after('password');
        });

        // Akaun sedia ada semuanya dicipta dengan kata laluan yang dipilih
        // pemiliknya, jadi tandakannya supaya mereka tetap diminta
        // mengesahkan kata laluan semasa sebelum menukarnya.
        DB::table('users')
            ->whereNull('password_set_at')
            ->update(['password_set_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set_at');
        });
    }
};
