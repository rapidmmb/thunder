<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Exceptions\StopThunderException;
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
        $lockPath = Thunder::getLockPath();
        $stopCommandPath = Thunder::getStopCommandPath();

        if (!file_exists($lockPath))
        {
            touch($lockPath);
        }

        $lock = fopen($lockPath, 'w');
        $lockTries = 10;
        while (!flock($lock, LOCK_EX | LOCK_NB) && --$lockTries)
        {
            usleep(100000);
        }

        if (!$lockTries)
        {
            $this->components->error("Thunder is already running in background ⚡");
            return;
        }

        $this->components->info("Thunder Started ⚡");

        $release = config('thunder.puncher.release', 100);

        try
        {
            bot()->loopUpdates(
                function (Update $update)
                {
                    $this->output->info("New update received");
                    Thunder::punch($update);
                },
                pass: function () use ($release, $stopCommandPath)
                {
                    if (file_exists($stopCommandPath))
                    {
                        @unlink($stopCommandPath);
                        throw new StopThunderException();
                    }

                    Thunder::getSharing()->disposeOlderThan($release);
                },
                timeout: config('thunder.hook.long', 15),
            );
        }
        catch (StopThunderException)
        {
            $this->alert("Turning off...");

            Thunder::getSharing()->dispose();

            $this->components->alert("Thunder turned off ⚡");
        }
        finally
        {
            flock($lock, LOCK_UN);
            fclose($lock);

            @unlink($lockPath);
        }
    }

}