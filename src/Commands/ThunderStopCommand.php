<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Thunder\Handle\StopEventHandler;
use Mmb\Thunder\Handle\StopHandler;

class ThunderStopCommand extends Command implements StopEventHandler
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:stop';

    public function handle()
    {
        (new StopHandler($this))->handle();
    }

    public function isNotRunning()
    {
        $this->components->error("Thunder is not running ⚡");
    }

    public function processing()
    {
        $this->components->info("Trying to stopping main process... ⚡");
        $this->info("Waiting for main process to response...");
    }

    public function success()
    {
        $this->components->success("Thunder stopped ⚡");
    }

}