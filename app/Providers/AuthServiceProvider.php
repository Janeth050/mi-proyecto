<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [ /* ... */ ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, ?string $ability = null) {
            return ($user && ($user->role ?? null) === 'admin') ? true : null;
        });

        Gate::define('manage-products',  fn ($u) => ($u->role ?? null) === 'admin');
        Gate::define('manage-suppliers', fn ($u) => ($u->role ?? null) === 'admin');
        Gate::define('manage-movements', fn ($u) => ($u->role ?? null) === 'admin');
        Gate::define('manage-lists',     fn ($u) => ($u->role ?? null) === 'admin');
        Gate::define('manage-users',     fn ($u) => ($u->role ?? null) === 'admin');

        Gate::define('view-movements', fn ($u) => in_array(($u->role ?? ''), ['admin','empleado'], true));
        Gate::define('view-kardex',    fn ($u) => in_array(($u->role ?? ''), ['admin','empleado'], true));
    }
}

