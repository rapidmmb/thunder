<?php

namespace Mmb\Thunder\Handle;

use Mmb\Thunder\Thunder;

abstract class SendCommandHandlerAbstract
{

    protected string $command;

    protected bool $wait = true;

    public function handle()
    {
        $lockPath = Thunder::getLockPath();
        $commandPath = Thunder::getCommandPath();

        if (!file_exists($lockPath))
        {
            $this->isNotRunning();
            return;
        }

        $lock = fopen($lockPath, 'w');
        try
        {
            if (flock($lock, LOCK_EX | LOCK_NB))
            {
                $this->isNotRunning();
                return;
            }
        }
        finally
        {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        if (!file_exists($commandPath))
        {
            file_put_contents($commandPath, $this->command);
        }

        if ($this->wait)
        {
            $this->processing();

            while (file_exists($commandPath))
            {
                sleep(1);
            }
        }

        $this->success();
    }

    protected abstract function isNotRunning();

    protected function processing()
    {
    }

    protected function success()
    {
    }

}