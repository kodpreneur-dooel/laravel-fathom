<?php

declare(strict_types=1);

namespace Codepreneur\Fathom;

use Codepreneur\Fathom\Authentication\ApiKeyAuthenticator;
use Codepreneur\Fathom\Http\Middleware\VerifyFathomWebhook;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class FathomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fathom.php', 'fathom');

        $this->app->singleton(FathomClient::class, function (Application $app): FathomClient {
            $config = config('fathom');

            return new FathomClient(
                authenticator: new ApiKeyAuthenticator($config['api_key'] ?? ''),
                baseUrl: $config['base_url'],
                timeout: (int) $config['timeout'],
                retryTimes: (int) ($config['retry']['times'] ?? 3),
                retrySleep: (int) ($config['retry']['sleep'] ?? 500),
            );
        });

        $this->app->singleton('fathom', function (Application $app): Fathom {
            $config = config('fathom');

            return new Fathom(
                client: $app->make(FathomClient::class),
                webhookSecret: $config['webhook']['secret'] ?? null,
                webhookTolerance: (int) ($config['webhook']['tolerance'] ?? 300),
            );
        });

        $this->app->alias('fathom', Fathom::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fathom.php' => config_path('fathom.php'),
            ], 'fathom-config');
        }

        $router = $this->app->make('router');
        $router->aliasMiddleware('fathom.webhook', VerifyFathomWebhook::class);
    }
}
