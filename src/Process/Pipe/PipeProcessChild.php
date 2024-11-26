<?php

namespace Mmb\Thunder\Process\Pipe;

use Mmb\Action\Memory\Step;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\ModelFinder;
use Mmb\Thunder\Process\ProcessChild;

class PipeProcessChild implements ProcessChild
{

    public function receive() : mixed
    {
        try
        {
            $length = unpack('J', fread(STDIN, 8))[1];

            return @unserialize(fread(STDIN, $length));
        }
        catch (\Throwable)
        {
            return null;
        }
    }

    public function handle(Update $update) : void
    {
        // TODO : Remove all the caches
        ModelFinder::clear();
        Step::setModel(null);

        $update->handle();
    }

}