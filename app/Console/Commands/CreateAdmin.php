<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Mencipta akaun Super Admin sebenar.
 *
 * Diperlukan selepas data demo dibersihkan: seeder produksi sengaja
 * tidak mencipta sebarang pengguna, jadi tanpa perintah ini tiada
 * sesiapa boleh masuk.
 */
class CreateAdmin extends Command
{
    protected $signature = 'jelajah:admin
        {--name= : Nama penuh}
        {--email= : Alamat e-mel}
        {--phone= : Nombor WhatsApp}
        {--password= : Kata laluan (dijana rawak jika tiada)}';

    protected $description = 'Mencipta atau mengemas kini akaun Super Admin';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama penuh');
        $email = $this->option('email') ?: $this->ask('Alamat e-mel');
        $phone = Phone::normalise($this->option('phone') ?: $this->ask('Nombor WhatsApp'));

        if (! $phone) {
            $this->error('Nombor WhatsApp tidak sah.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(16);

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => $password,
                'password_set_at' => now(),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->info("Super Admin sedia: {$user->name} <{$user->email}>");
        $this->line("Kata laluan: {$password}");
        $this->warn('Simpan kata laluan ini sekarang — ia tidak dipaparkan lagi.');

        return self::SUCCESS;
    }
}
