<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Handle\ServerEventHandler;
use Mmb\Thunder\Handle\ServerHandler;

class ThunderStartCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:start';

    public function handle()
    {
        (new ServerHandler(
            new class($this) implements ServerEventHandler
            {

                public function __construct(
                    protected ThunderStartCommand $cmd,
                )
                {
                }

                public function alreadyRunning()
                {
                    $this->cmd->components->error("Thunder is already running in background ⚡");
                }

                public function started()
                {
                    $this->cmd->components->info("Thunder Started ⚡");
                }

                public function newUpdate(Update $update)
                {
                    $this->cmd->output->info("New update received");
                }

                public function turningOff()
                {
                    $this->cmd->alert("Turning off...");
                }

                public function turnedOff()
                {
                    $this->cmd->components->alert("Thunder turned off ⚡");
                }

                public function releasedOld(int $killed)
                {
                    $this->cmd->output->info("$killed process killed");
                }

                public function suggest(string $message)
                {
                    $this->cmd->output->note($message);
                }

            }
        ))->handle();
    }

}