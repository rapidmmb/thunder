<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Handle\ServerEventHandler;
use Mmb\Thunder\Handle\ServerHandler;

class ThunderStartCommand extends Command implements ServerEventHandler
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:start';

    public function handle()
    {
        (new ServerHandler($this))->handle();
    }

    public function alreadyRunning()
    {
        $this->components->error("Thunder is already running in background ⚡");
    }

    public function started()
    {
        $this->components->info("Thunder Started ⚡");
    }

    public function newUpdate(Update $update)
    {
        $this->output->info("New update received");
    }

    public function turningOff()
    {
        $this->alert("Turning off...");
    }

    public function turnedOff()
    {
        $this->components->alert("Thunder turned off ⚡");
    }

    public function releasedOld(int $killed)
    {
        $this->output->info("$killed process killed");
    }

    public function suggest(string $message)
    {
        $this->output->note($message);
    }

    public function hotReloaded()
    {
        $this->output->info("Hot reloaded! 🔥");
    }

}