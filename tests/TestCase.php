<?php

namespace Oxalis\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Oxalis\OxalisServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [OxalisServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('oxalis.rp_id', 'localhost');
        $app['config']->set('oxalis.origins', ['http://localhost']);
        $app['config']->set('oxalis.routes.prefix', 'oxalis');
    }

    protected function defineRoutes($router): void
    {
        require __DIR__.'/../routes/web.php';
    }
}
