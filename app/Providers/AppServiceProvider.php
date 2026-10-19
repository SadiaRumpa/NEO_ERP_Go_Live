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

        // Defensive model class autoloader for Linux case-sensitive filesystems
        spl_autoload_register(function ($class) {
            $map = [
                'App\\Models\\FJob' => app_path('Models/Fjob.php'),
                'App\\Models\\FJobProgress' => app_path('Models/FjobProgress.php'),
            ];
            if (isset($map[$class]) && file_exists($map[$class])) {
                require_once $map[$class];
            }
        });

        // Register SQLite CONCAT function for cross-database compatibility
        Event::listen(\Illuminate\Database\Events\ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                try {
                    $event->connection->getPdo()->sqliteCreateFunction('CONCAT', function (...$args) {
                        return implode('', $args);
                    });
                } catch (\Throwable $e) {
                    // Ignore if already registered
                }
            }
        });

        // Also register immediately for current connection if SQLite
        try {
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function (...$args) {
                    return implode('', $args);
                });
            }
        } catch (\Throwable $e) {
            // Database not yet configured or migrated
        }

        View::composer('*', function ($view) {
            try {
                $businessinfo = BusinessInfo::first();
                $view->with('appbrand', $businessinfo);
            } catch (\Throwable $e) {
                $view->with('appbrand', null);
            }
        });
    }
}
