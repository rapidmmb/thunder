<?php

namespace Mmb\Thunder\Process\Pipe;

use Illuminate\Support\Arr;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Process\ProcessManager;
use Mmb\Thunder\Process\ProcessInfo;
use Mmb\Thunder\Thunder;

class PipeProcessManager implements ProcessManager
{

    /**
     * @var ProcessInfo[]
     */
    protected array $processes = [];

    /**
     * @var (ProcessInfo|int)[][]
     */
    protected array $killBuffer = [];

    /**
     * Idle process list
     *
     * @var ProcessInfo[]
     */
    protected array $idle = [];

    /**
     * Idle last id
     *
     * @var int
     */
    protected int $idleLastId = 0;

    public function startup() : void
    {
        $this->createIdles();
    }

    /**
     * Create required idles
     *
     * @return void
     */
    public function createIdles()
    {
        for ($i = count($this->idle); $i < 3; $i++) // todo : from config
        {
            $this->idle[] = $this->open("IDLE." . ++$this->idleLastId);
        }
    }

    /**
     * Time to checkup
     *
     * @return void
     */
    public function inspection() : void
    {
        foreach ($this->killBuffer as $i => [$process, $at])
        {
            if (!$process->checkAlive())
            {
                unset($this->killBuffer[$i]);
            }
            elseif (time() >= $at)
            {
                $process->kill();
                unset($this->killBuffer[$i]);
            }
        }

        foreach ($this->idle as $key => $process)
        {
            if ($process->isEnded())
            {
                unset($this->idle[$key]);
            }
            else
            {
                if (!$process->checkAlive())
                {
                    $process->end();
                    unset($this->idle[$key]);
                }
            }
        }

        foreach ($this->processes as $key => $process)
        {
            if ($process->isEnded())
            {
                unset($this->processes[$key]);
            }
            else
            {
                if (!$process->checkAlive())
                {
                    $process->end();
                    unset($this->processes[$key]);
                }
                elseif ($process->lastActivityAt() < time() - config('thunder.timeout_interval', 100))
                {
                    $process->end();
                    unset($this->processes[$key]);
                }
            }
        }

        $this->createIdles();
    }

    /**
     * Find a process by tag
     *
     * @param string $tag
     * @return ProcessInfo|null
     */
    public function find(string $tag) : ?ProcessInfo
    {
        if ($process = $this->processes[$tag] ?? null)
        {
            if ($process->isEnded())
            {
                return null;
            }
        }

        return $process;
    }

    /**
     * Open new process
     *
     * @param string $tag
     * @return ProcessInfo
     * @throws \Exception
     */
    public function open(string $tag) : ProcessInfo
    {
        $this->find($tag)?->end();

        $process = new PipeProcessInfo($this, $tag);
        $this->processes[$tag] = $process;

        $process->open();

        return $process;
    }

    /**
     * Find a process or open it
     *
     * @param string $tag
     * @return ProcessInfo
     * @throws \Exception
     */
    public function findOrOpen(string $tag) : ProcessInfo
    {
        return $this->find($tag) ?? $this->open($tag);
    }

    /**
     * Handle the update
     *
     * @param Update $update
     * @return void
     */
    public function handle(Update $update) : void
    {
        $tag = Thunder::getTagger()->tag($update);

        $tries = 3;

        handle:
        $process = $this->find($tag);

        if (!$process)
        {
            $selectedIdle = null;
            foreach ($this->idle as $key => $idle)
            {
                if (!$idle->isEnded() && $idle->checkAlive())
                {
                    $process = $idle;
                    $selectedIdle = $key;
                    break;
                }
            }

            if ($process)
            {
                $process->updateTag($tag); // todo : can throw an error
                unset($this->idle[$selectedIdle]);
                $this->processes[$tag] = $process;
            }
        }

        if (!$process)
        {
            $process = $this->open($tag);
            $this->processes[$tag] = $process;
        }

        try
        {
            $process->handle($update);
        }
        catch (\Throwable $e)
        {
            if (!$process->checkAlive())
            {
                $process->end();

                if (--$tries) goto handle;
                else throw $e;
            }
            else
            {
                throw $e;
            }
        }
    }

    /**
     * Add a timer to kill the process
     *
     * @param PipeProcessInfo $process
     * @param int             $time
     * @return void
     */
    public function killAfter(PipeProcessInfo $process, int $time) : void
    {
        $this->killBuffer[] = [$process, time() + $time];
    }

    /**
     * Stop the child processes
     *
     * @return void
     */
    public function stopAll() : void
    {
        foreach ($this->processes as $process)
        {
            if (!$process->isEnded())
            {
                $process->end();
            }
        }

        foreach ($this->idle as $process)
        {
            if (!$process->isEnded())
            {
                $process->end();
            }
        }
    }

    /**
     * Force kill the child processes
     *
     * @return void
     */
    public function killAll() : void
    {
        /** @var ProcessInfo $process */
        foreach (
            array_unique(array_merge(Arr::pluck($this->killBuffer, 0), $this->processes, $this->idle), SORT_REGULAR)
            as $process
        )
        {
            if ($process->checkAlive())
            {
                $process->kill();
            }
        }

        $this->killBuffer = [];
        $this->processes = [];
        $this->idle = [];
    }

    /**
     * Checks has child process that is active
     *
     * @return bool
     */
    public function hasActiveProcess() : bool
    {
        foreach ($this->processes as $process)
        {
            if (!$process->isEnded() || $process->checkAlive())
            {
                return true;
            }
        }

        foreach ($this->killBuffer as [$process])
        {
            if (!$process->isEnded() || $process->checkAlive())
            {
                return true;
            }
        }

        foreach ($this->idle as $process)
        {
            if (!$process->isEnded() || $process->checkAlive())
            {
                return true;
            }
        }

        return false;
    }

}