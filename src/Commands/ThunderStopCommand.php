<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
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
        $lockPath = Thunder::getLockPath();
        $stopCommandPath = Thunder::getStopCommandPath();

        if (!file_exists($lockPath))
        {
            $this->components->error("Thunder is not running ⚡");
            return;
        }

        $lock = fopen($lockPath, 'w');
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

        if (!file_exists($stopCommandPath))
        {
            touch($stopCommandPath);
        }

        $this->components->info("Trying to stopping main process... ⚡");
        $this->info("Waiting for main process to response...");

        while (file_exists($stopCommandPath))
        {
            sleep(1);
        }

        $this->components->success("Thunder stopped ⚡");
    }

}