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
        $process = Thunder::createChild();

        try
        {
            $lastMessageAt = time();
            $release = config('thunder.timeout_interval', 100) + 10;

            while (time() - $lastMessageAt <= $release)
            {
                if (null !== $new = $process->receive())
                {
                    if ($new === 'STOP')
                    {
                        break;
                    }
                    elseif (is_array($new) && $new[0] === 'UPDATE:TAG')
                    {
                        // Nothing
                    }
                    elseif ($new instanceof Update)
                    {
                        $process->handle($new);
                    }

                    $lastMessageAt = time();
                }
                else
                {
                    usleep(20000);
                }
            }
        }
        catch (\Throwable $e)
        {
            report($e);
        }
    }

}