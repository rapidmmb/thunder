<?php

namespace Mmb\Thunder\Process;

use Mmb\Core\Updates\Update;

interface ProcessInfo
{

    public function handle(Update $update) : void;

    public function open() : void;

    public function end() : void;

    public function kill() : void;

    public function checkAlive() : bool;

    public function isEnded() : bool;

    public function lastActivityAt() : int;

    public function updateTag(string $tag) : void;

}