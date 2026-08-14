<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Tests;

use Codepreneur\Fathom\Facades\Fathom;
use Codepreneur\Fathom\FathomServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FathomServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Fathom' => Fathom::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('fathom.api_key', 'test-api-key');
        $app['config']->set('fathom.base_url', 'https://api.fathom.ai/external/v1');
        $app['config']->set('fathom.timeout', 30);
        $app['config']->set('fathom.retry.times', 0);
        $app['config']->set('fathom.retry.sleep', 0);
        $app['config']->set('fathom.webhook.secret', 'whsec_5WbX5kEWLlfzsGNjH64I8lOOqUB6e8FH');
        $app['config']->set('fathom.webhook.tolerance', 300);
    }
}
