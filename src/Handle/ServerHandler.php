<?php

namespace Mmb\Thunder\Handle;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Exceptions\StopThunderException;
use Mmb\Thunder\Thunder;

class ServerHandler
{

    protected string $lockPath;
    protected string $commandPath;
    protected        $lock;

    public function __construct(
        protected ?ServerEventHandler $events = null,
    )
    {
        $this->lockPath = Thunder::getLockPath();
        $this->commandPath = Thunder::getCommandPath();
    }


    public function handle()
    {
        if (!$this->lock())
        {
            $this->events?->alreadyRunning();
            return;
        }

        $this->events?->started();

        $release = config('thunder.puncher.release', 100);

        $this->registerShutdown();

        declare(ticks=1);
        try
        {
            bot()->loopUpdates(
                callback: function (Update $update)
                {
                    $this->handlePreCommands();

                    $this->events?->newUpdate($update);
                    Thunder::punch($update);
                },
                pass   : function () use ($release)
                {
                    $this->handlePostCommands();

                    if ($killed = Thunder::getSharing()->disposeOlderThan($release))
                    {
                        $this->events?->releasedOld($killed);
                    }
                },
                timeout: config('thunder.hook.long', 60),
            );
        }
        catch (StopThunderException)
        {
            $this->stop();
        }
        finally
        {
            $this->release();
        }
    }

    public function registerShutdown()
    {
        if (function_exists('pcntl_signal'))
        {
            pcntl_signal(SIGINT, $this->onForceShutdown(...));
        }
        elseif (function_exists('sapi_windows_set_ctrl_handler'))
        {
            sapi_windows_set_ctrl_handler($this->onForceShutdownWindows(...));
        }
        else
        {
            $this->events?->suggest(
                "Thunder can't trig the Ctrl+C action, enable [pcntl] extension to better experience"
            );
        }
    }

    /**
     * Event for forcing shutdown like Ctrl+C event
     *
     * @return never
     */
    public function onForceShutdown() : never
    {
        $this->stop();
        $this->release();
        exit;
    }

    /**
     * Event for forcing shutdown like Ctrl+C event
     *
     * @return void
     */
    public function onForceShutdownWindows(int $event)
    {
        switch ($event)
        {
            case PHP_WINDOWS_EVENT_CTRL_C:
            case PHP_WINDOWS_EVENT_CTRL_BREAK:
                $this->onForceShutdown();
        }
    }

    /**
     * Stop the service
     *
     * @return void
     */
    public function stop()
    {
        $this->events?->turningOff();

        Thunder::getSharing()->dispose();

        $this->events?->turnedOff();
    }

    /**
     * Lock the process
     *
     * @return bool
     */
    public function lock()
    {
        if (!file_exists($this->lockPath))
        {
            touch($this->lockPath);
        }

        $this->lock = fopen($this->lockPath, 'w');
        $lockTries = 10;
        while (!flock($this->lock, LOCK_EX | LOCK_NB) && --$lockTries)
        {
            usleep(100000);
        }

        return $lockTries > 0;
    }

    /**
     * Release the process
     *
     * @return void
     */
    public function release()
    {
        try
        {
            flock($this->lock, LOCK_UN);
            fclose($this->lock);

            @unlink($this->lockPath);
        }
        catch (\Throwable)
        {
        }
    }

    protected bool|string $preHandled = false;

    public function handlePreCommands()
    {
        if ($this->preHandled !== false)
        {
            return;
        }
        elseif (file_exists($this->commandPath))
        {
            switch ($this->preHandled = file_get_contents($this->commandPath))
            {
                case 'HOT':
                    @unlink($this->commandPath);
                    Thunder::getSharing()->disposeOlderThan(-1);
                    $this->events?->hotReloaded();
                    break;
            }
        }
        else
        {
            $this->preHandled = true;
        }
    }

    public function handlePostCommands()
    {
        if ($this->preHandled === false)
        {
            $this->preHandled = file_exists($this->commandPath) ? file_get_contents($this->commandPath) : true;
        }

        if (is_string($this->preHandled))
        {
            switch ($this->preHandled)
            {
                case 'STOP':
                    @unlink($this->commandPath);
                    throw new StopThunderException();
            }
        }

        $this->preHandled = false;
    }

}