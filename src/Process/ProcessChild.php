<?php

namespace Mmb\Thunder\Process;

interface ProcessChild
{

    public function receive() : mixed;

}