<?php

namespace Mmb\Thunder\Process;

use Mmb\Core\Updates\Update;

interface ProcessChild
{

    public function receive() : mixed;

    public function handle(Update $update) : void;

}