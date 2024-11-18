<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Thunder;

class ThunderRunProcessCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:run-process {tag}';

    public function handle()
    {
        $tag = $this->argument('tag');

        $sharing = Thunder::getSharing();

        try
        {
            while (true)
            {
                if (null !== $new = $sharing->receive($tag))
                {
                    if ($new === false)
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
            $sharing->delete($tag);
        }
    }

}