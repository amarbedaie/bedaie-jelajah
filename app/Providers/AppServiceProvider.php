<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserHasRole;
use App\Models\Application;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\EventPolicy;
use App\Policies\RegistrationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Livewire hanya mengulang sebahagian middleware pada /livewire/update.
        // Tanpa ini, Penggerak yang diturunkan pangkat kekal boleh memanggil
        // tindakan komponen selagi tabnya terbuka.
        Livewire::addPersistentMiddleware([
            EnsureUserHasRole::class,
        ]);

        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);

        // Eksport data peserta adalah tindakan sensitif — admin sahaja.
        Gate::define('export-participants', fn (User $user) => $user->isAdmin());

        // Nota dalaman & rekod komunikasi tidak boleh dilihat oleh Penggerak.
        Gate::define('view-internal-notes', fn (User $user) => $user->isAdmin());
    }
}
