<?php

namespace Mmb\Thunder\Handle;

class HotReloadHandler extends SendCommandHandlerAbstract
{

    public function __construct(
        protected ?HotReloadEventHandler $events = null,
    )
    {
    }

    protected string $command = 'HOT';

    protected bool $wait = false;

    protected function isNotRunning()
    {
        $this->events?->isNotRunning();
    }

    protected function success()
    {
        $this->events?->success();
    }

}