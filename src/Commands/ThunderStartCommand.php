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
        if (!file_exists('thunder.lock'))
        {
            touch('thunder.lock');
        }

        $lock = fopen('thunder.lock', 'w');
        $lockTries = 10;
        while (!flock($lock, LOCK_EX | LOCK_NB) && --$lockTries)
        {
            usleep(100000);
            $lockTries--;
        }

        if (!$lockTries)
        {
            $this->components->error("Thunder is already running in background ⚡");
            return;
        }

        $this->components->info("Thunder Started ⚡");

        try
        {
            bot()->loopUpdates(
                function (Update $update)
                {
                    $this->output->info("New update received");
                    Thunder::punch($update);
                },
                pass: function ()
                {
                    if (file_exists('thunder-stop.command'))
                    {
                        @unlink('thunder-stop.command');
                        throw new \Exception;
                    }
                },
                timeout: config('thunder.hook.long', 15),
            );
        }
        catch (\Exception)
        {
            $this->alert("Turning off...");

            Thunder::getSharing()->dispose();

            $this->components->alert("Thunder turned off ⚡");
        }
        finally
        {
            flock($lock, LOCK_UN);
            fclose($lock);

            @unlink('thunder.lock');
        }
    }

}