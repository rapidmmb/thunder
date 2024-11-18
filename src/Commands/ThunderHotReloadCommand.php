<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Thunder\Handle\HotReloadEventHandler;
use Mmb\Thunder\Handle\HotReloadHandler;

class ThunderHotReloadCommand extends Command implements HotReloadEventHandler
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:hot-reload';

    public function handle()
    {
        (new HotReloadHandler($this))->handle();
    }

    public function isNotRunning()
    {
        $this->components->error("Thunder is not running ⚡");
    }

    public function success()
    {
        $this->components->success("Hot reload will applied 🔥");
    }

}