<?php
declare(ticks=1);

namespace Mmb\Thunder\Handle;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Exceptions\StopThunderException;
use Mmb\Thunder\Thunder;

class ServerHandler
{

    protected string $lockPath;
    protected string $stopCommandPath;
    protected        $lock;

    public function __construct(
        protected ?ServerEventHandler $events = null,
    )
    {
        $this->lockPath = Thunder::getLockPath();
        $this->stopCommandPath = Thunder::getStopCommandPath();
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

        try
        {
            bot()->loopUpdates(
                function (Update $update)
                {
                    $this->events?->newUpdate($update);
                    Thunder::punch($update);
                },
                pass   : function () use ($release)
                {
                    if (file_exists($this->stopCommandPath))
                    {
                        @unlink($this->stopCommandPath);
                        throw new StopThunderException();
                    }

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
     * @return void
     */
    public function onForceShutdown()
    {
        $this->stop();
        $this->release();
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
                exit;
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

}