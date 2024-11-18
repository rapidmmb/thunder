<?php

namespace Mmb\Thunder\Sharing;

use Mmb\Core\Updates\Update;

interface Sharing
{

    public function send(string $tag, mixed $message) : void;

    public function receive(string $tag) : mixed;

    public function isStop(string $tag) : bool;

    public function delete(string $tag) : void;

    public function dispose() : void;

}