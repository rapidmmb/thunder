<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Thunder\Handle\StopEventHandler;
use Mmb\Thunder\Handle\StopHandler;
use Mmb\Thunder\Thunder;

class ThunderStopCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:stop';

    public function handle()
    {
        (new StopHandler(
            new class($this) implements StopEventHandler
            {

                public function __construct(
                    protected ThunderStartCommand $cmd,
                )
                {
                }

                public function isNotRunning()
                {
                    $this->cmd->components->error("Thunder is not running ⚡");
                }

                public function processing()
                {
                    $this->cmd->components->info("Trying to stopping main process... ⚡");
                    $this->cmd->info("Waiting for main process to response...");
                }

                public function success()
                {
                    $this->cmd->components->success("Thunder stopped ⚡");
                }

            }
        ))->handle();
    }

}