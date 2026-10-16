<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\BusinessInfo;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (request()->isSecure() || request()->header('x-forwarded-proto') === 'https' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        // // Grant all abilities to super_admin role
        // Gate::before(function (User $user, string $ability): bool|null {
        //     return $user->hasRole('super_admin') ? true : null;
        // });

        try{
            $businessinfo = BusinessInfo::first();
            View::share('appbrand', $businessinfo);
        }
        catch(Exception){

        }
    }
}
