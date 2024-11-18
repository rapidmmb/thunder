<?php

namespace Mmb\Thunder\Handle;

use Mmb\Thunder\Thunder;

class StopHandler extends SendCommandHandlerAbstract
{

    public function __construct(
        protected ?StopEventHandler $events = null,
    )
    {
    }

    protected string $command = 'STOP';

    protected bool $wait = true;

    protected function isNotRunning()
    {
        $this->events?->isNotRunning();
    }

    protected function processing()
    {
        $this->events?->processing();
    }

    protected function success()
    {
        $this->events?->success();
    }

}