<?php

namespace Mmb\Thunder\Handle;

use Mmb\Thunder\Thunder;

class StopHandler
{

    public function __construct(
        protected ?StopEventHandler $events = null,
    )
    {
    }

    public function handle()
    {
        $lockPath = Thunder::getLockPath();
        $stopCommandPath = Thunder::getStopCommandPath();

        if (!file_exists($lockPath))
        {
            $this->events?->isNotRunning();
            return;
        }

        $lock = fopen($lockPath, 'w');
        try
        {
            if (flock($lock, LOCK_EX | LOCK_NB))
            {
                $this->events?->isNotRunning();
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

        $this->events?->processing();

        while (file_exists($stopCommandPath))
        {
            sleep(1);
        }

        $this->events?->success();
    }

}