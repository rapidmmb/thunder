<?php

namespace Mmb\Thunder\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Mmb\Thunder\Tests\TestCase;
use Psy\Output\ShellOutput;

class Test1 extends TestCase
{

    public function test_a()
    {
        Artisan::call('thunder:run-process 123', [], $output = new ShellOutput());
        readline();
    }

}