<?php

namespace Commands;

use Illuminate\Console\Command;

class ThunderStartCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thunder:start';

    public function handle()
    {
        $this->components->info("Thunder Started ⚡");
    }

}