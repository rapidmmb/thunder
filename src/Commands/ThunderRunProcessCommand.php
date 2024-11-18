<?php

namespace Mmb\Thunder\Commands;

use Illuminate\Console\Command;
use Mmb\Thunder\Handle\ProcessHandler;

class ThunderRunProcessCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:run-process {tag}';

    protected $hidden = true;

    public function handle()
    {
        $tag = $this->argument('tag');

        (new ProcessHandler($tag))->handle();
    }

}