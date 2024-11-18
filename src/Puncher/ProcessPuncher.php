<?php

namespace Mmb\Thunder\Puncher;

use Mmb\Thunder\Thunder;

class ProcessPuncher implements Puncher, Pipeable
{

    protected array $processes = [];

    public function punch(string $tag) : void
    {
        if (!array_key_exists($tag, $this->processes) || Thunder::getSharing()->isStopped($tag))
        {
            $command = config('thunder.puncher.process.command');

            $command = str_replace('[TAG]', '"' . addslashes($tag) . '"', $command);

            $descriptors = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];

            if ($proc = proc_open($command, $descriptors, $pipes))
            {
                $this->processes[$tag] = [$proc, ...$pipes];
            }
        }
    }

    public function getInput(string $tag)
    {
        if (Thunder::getIsChild())
        {
            return STDIN;
        }

        if ($proc = @$this->processes[$tag])
        {
            return $proc[1];
        }

        return null;
    }

    public function getOutput(string $tag)
    {
        if (Thunder::getIsChild())
        {
            return STDOUT;
        }

        if ($proc = @$this->processes[$tag])
        {
            return $proc[2];
        }

        return null;
    }

    public function getAllTags() : array
    {
        $all = [];

        foreach ($this->processes as $tag => [$proc, $in])
        {
            if ($proc && /*@proc_get_status($proc)['running'] &&*/ is_resource($in) && !feof($in))
            {
                $all[] = $tag;
            }
        }

        return $all;
    }

}