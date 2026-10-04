<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Tests;

use Emeq\MistralApi\MistralServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MistralServiceProvider::class,
        ];
    }
}
