<?php

namespace Mmb\Thunder\Puncher;

use Mmb\Thunder\Thunder;

class ProcessPuncher implements Puncher
{

    protected array $processes = [];

    public function punch(string $tag) : void
    {
        if (!array_key_exists($tag, $this->processes) || Thunder::getSharing()->isStop($tag))
        {
            $command = config('thunder.puncher.process.command');

            $command = str_replace('[TAG]', '"' . addslashes($tag) . '"', $command);

            $descriptors = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];

            $this->processes[$tag] = proc_open($command, $descriptors, $pipes);
        }
    }

}