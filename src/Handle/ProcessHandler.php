<?php

namespace Mmb\Thunder\Handle;

use Mmb\Action\Memory\Step;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\ModelFinder;
use Mmb\Thunder\Exceptions\ProcessShareFileNotFoundException;
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
        Thunder::setAsChild();
        $sharing = Thunder::getSharing();

        try
        {
            $lastMessageAt = time();
            $release = config('thunder.puncher.release', 100) + 10;

            while (time() - $lastMessageAt <= $release)
            {
                if (null !== $new = $sharing->receive($this->tag))
                {
                    if ($new === 'STOP')
                    {
                        break;
                    }
                    elseif ($new instanceof Update)
                    {
                        // TODO : Remove all the caches
                        ModelFinder::clear();
                        Step::setModel(null);

                        $new->handle();
                    }

                    $lastMessageAt = time();
                }
                else
                {
                    usleep(20000);
                }
            }
        }
        catch (ProcessShareFileNotFoundException) {}
        finally
        {
            $sharing->delete($this->tag);
        }
    }

}