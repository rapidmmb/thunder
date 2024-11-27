<?php

namespace Mmb\Thunder;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Process\Pipe\PipeProcessChild;
use Mmb\Thunder\Process\Pipe\PipeProcessManager;
use Mmb\Thunder\Process\ProcessChild;
use Mmb\Thunder\Process\ProcessManager;
use Mmb\Thunder\Tagger\ChatTagger;
use Mmb\Thunder\Tagger\Tagger;

class ThunderFactory
{

    public function createManager() : ProcessManager
    {
        $driver = config('thunder.driver', 'pipe');

        return app()->make(
            match ($driver)
            {
                'pipe'  => PipeProcessManager::class,
                default => $driver,
            }
        );
    }

    public function createChild() : ProcessChild
    {
        $driver = config('thunder.driver', 'pipe');

        return app()->make(
            match ($driver)
            {
                'pipe'  => PipeProcessChild::class,
                default => $driver,
            }
        );
    }

    protected Tagger $tagger;

    public function getTagger() : Tagger
    {
        if (!isset($this->tagger))
        {
            $driver = config('thunder.tagger', ChatTagger::class);

            $this->tagger = app()->make($driver);
        }

        return $this->tagger;
    }

    public function getLockPath() : string
    {
        return base_path('thunder.lock');
    }

    public function getCommandPath() : string
    {
        return base_path('thunder.command');
    }

    protected bool $isChild = false;

    public function setAsChild()
    {
        $this->isChild = true;
    }

    public function getIsChild() : bool
    {
        return $this->isChild;
    }

}