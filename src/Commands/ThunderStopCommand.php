<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;

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
        if (!file_exists('thunder.lock'))
        {
            $this->components->error("Thunder is not running ⚡");
            return;
        }

        $lock = fopen('thunder.lock', 'w');
        try
        {
            if (flock($lock, LOCK_EX | LOCK_NB))
            {
                $this->components->error("Thunder is not running ⚡");
                return;
            }
        }
        finally
        {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        if (!file_exists('thunder-stop.command'))
        {
            touch('thunder-stop.command');
        }

        $this->components->info("Trying to stopping main process... ⚡");
        $this->info("Waiting for main process to response...");

        while (file_exists('thunder-stop.command'))
        {
            sleep(1);
        }

        $this->components->success("Thunder stopped ⚡");
    }

}