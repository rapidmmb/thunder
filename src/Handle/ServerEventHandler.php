<?php

namespace Mmb\Thunder\Handle;

use Mmb\Core\Updates\Update;

interface ServerEventHandler
{

    public function alreadyRunning();

    public function started();

    public function newUpdate(Update $update);

    public function turningOff();

    public function turnedOff();

    public function releasedOld(int $killed);

    public function suggest(string $message);

}