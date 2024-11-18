<?php

namespace Mmb\Thunder\Handle;

interface HotReloadEventHandler
{

    public function isNotRunning();

    public function success();

}