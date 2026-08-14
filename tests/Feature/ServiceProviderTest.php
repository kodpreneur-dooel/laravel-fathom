<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Tests\Feature;

use Codepreneur\Fathom\Facades\Fathom;
use Codepreneur\Fathom\Fathom as FathomManager;
use Codepreneur\Fathom\FathomClient;
use Codepreneur\Fathom\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_fathom_singleton(): void
    {
        $this->assertInstanceOf(FathomManager::class, $this->app->make('fathom'));
        $this->assertInstanceOf(FathomManager::class, $this->app->make(FathomManager::class));
    }

    public function test_service_provider_registers_fathom_client(): void
    {
        $this->assertInstanceOf(FathomClient::class, $this->app->make(FathomClient::class));
    }

    public function test_facade_resolves_to_fathom_manager(): void
    {
        $this->assertInstanceOf(FathomManager::class, Fathom::getFacadeRoot());
    }

    public function test_config_is_merged(): void
    {
        $this->assertEquals('test-api-key', config('fathom.api_key'));
        $this->assertEquals('https://api.fathom.ai/external/v1', config('fathom.base_url'));
    }

    public function test_config_can_be_published(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'fathom-config'])
            ->assertExitCode(0);

        $this->assertFileExists(config_path('fathom.php'));
    }

    public function test_middleware_alias_is_registered(): void
    {
        $router = $this->app['router'];
        $middleware = $router->getMiddleware();

        $this->assertArrayHasKey('fathom.webhook', $middleware);
    }
}
