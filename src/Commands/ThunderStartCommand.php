<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Thunder;

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
        $this->components->info("Thunder Started ⚡");

        bot()->loopUpdates(
            function (Update $update)
            {
                $this->output->info("New update received");
                Thunder::punch($update);
            }
        );
    }

}