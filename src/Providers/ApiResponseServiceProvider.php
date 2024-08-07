<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse\Providers;

use Bpartner\ApiResponse\ResponseManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/api-response.php',
            'api-response',
        );

        $this->app->bind('apiResponse', fn($app) => $app->make($app['config']['api-response.factory']));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/api-response.php' => config_path('api-response.php'),
        ], 'api-response-config');

        $this->configure();
    }

    private function configure(): void
    {
        ResponseManager::wrapper(config('api-response.wrapper'));
        ResponseManager::paginate(config('api-response.pagination.paginate_meta_field'));

        if ( ! config('api-response.useMeta')) {
            ResponseManager::withoutMeta();
        }

        if ( ! config('api-response.useStatus')) {
            ResponseManager::withoutStatus();
        }

        if (App::isProduction() || config('api-response.disable_exceptions_details')) {
            ResponseManager::disableException();
        }
    }
}
