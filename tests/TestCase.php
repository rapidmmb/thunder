<?php

namespace Mmb\Thunder\Tests;

use Mmb\Thunder\ThunderServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            ThunderServiceProvider::class,
        ];
    }

}