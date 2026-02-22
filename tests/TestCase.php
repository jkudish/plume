<?php

namespace Plume\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Plume\XServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            XServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'X' => \Plume\Facades\X::class,
        ];
    }
}
