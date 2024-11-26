<?php

namespace Mmb\Thunder\Process;

use Mmb\Core\Updates\Update;

class ThunderProcess
{

    public function __construct(
        protected ProcessManager $handler,
    )
    {
    }

    public function start()
    {
        $this->handler->startup();
    }

    public function handle(Update $update)
    {
        $this->handler->handle($update);
    }

    public function inspection()
    {
        $this->handler->inspection();
    }

    public function hotReload()
    {
        $this->stop();

        $this->handler->startup();
    }

    public function stop()
    {
        $this->handler->stopAll();

        $tries = 10;
        while ($this->handler->hasActiveProcess() && --$tries)
        {
            sleep(1);
        }

        $this->handler->killAll();
    }

}