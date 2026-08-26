<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StateSeeder::class,
            CatalogSeeder::class,
            SettingSeeder::class,
            NotificationTemplateSeeder::class,
        ]);

        // Data demo hanya untuk persekitaran bukan-produksi.
        if (! app()->environment('production')) {
            $this->call(DemoSeeder::class);
            $this->call(OutreachSeeder::class);
        }
    }
}
