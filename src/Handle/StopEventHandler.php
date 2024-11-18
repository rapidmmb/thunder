<?php

namespace Mmb\Thunder\Handle;

interface StopEventHandler
{

    public function isNotRunning();

    public function processing();

    public function success();

}