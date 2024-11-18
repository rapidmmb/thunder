<?php

namespace Mmb\Thunder\Handle;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Thunder;

class ProcessHandler
{

    public function __construct(
        protected string $tag,
    )
    {
    }

    public function handle()
    {
        $sharing = Thunder::getSharing();

        try
        {
            while (true)
            {
                if (null !== $new = $sharing->receive($this->tag))
                {
                    if ($new === 'STOP')
                    {
                        break;
                    }
                    elseif ($new instanceof Update)
                    {
                        $new->handle();
                    }
                }

                usleep(20000);
            }
        }
        finally
        {
            $sharing->delete($this->tag);
        }
    }

}