<?php

namespace Mmb\Thunder\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            LaplusServiceProvider::class,
        ];
    }

}