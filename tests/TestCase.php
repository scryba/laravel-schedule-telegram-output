<?php

namespace Scryba\LaravelScheduleTelegramOutput\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Scryba\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [ScheduleTelegramOutputServiceProvider::class];
    }
} 