<?php

declare(strict_types=1);

namespace AddressCompletion\Tests;

use AddressCompletion\AddressCompletionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AddressCompletionServiceProvider::class,
        ];
    }
}
