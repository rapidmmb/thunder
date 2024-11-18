<?php

namespace Mmb\Thunder\Sharing;

use Mmb\Thunder\Puncher\Pipeable;
use Mmb\Thunder\Thunder;

class PipeSharing implements Sharing
{

    protected function getPipeable() : ?Pipeable
    {
        if ($puncher = Thunder::getPuncher())
        {
            if ($puncher instanceof Pipeable)
            {
                return $puncher;
            }

            throw new \TypeError("Puncher is not a Pipeable");
        }

        return null;
    }

    protected array $lastMessagesAt = [];

    public function send(string $tag, mixed $message) : void
    {
        if ($input = $this->getPipeable()?->getInput($tag))
        {
            $msg = serialize($message);

            fwrite($input, pack('J', strlen($msg)) . $msg);

            $this->lastMessagesAt[$tag] = time();
        }
    }

    public function receive(string $tag) : mixed
    {
        if ($input = $this->getPipeable()?->getInput($tag))
        {
            $length = unpack('J', fread($input, 8))[1];

            return unserialize(fread($input, $length));
        }

        return null;
    }

    public function isStopped(string $tag) : bool
    {
        if ($input = $this->getPipeable()?->getInput($tag))
        {
            return feof($input);
        }

        return true;
    }

    public function delete(string $tag) : void
    {
        // $this->getPipeable()?->closePipe($tag);
    }

    public function disposeOlderThan(int $timeout) : int
    {
        $count = 0;

        foreach ($this->getPipeable()?->getAllTags() ?? [] as $tag)
        {
            if (!$this->isStopped($tag) && time() - ($this->lastMessagesAt[$tag] ?? 0) > $timeout)
            {
                $this->send($tag, 'STOP');
                unset($this->lastMessagesAt[$tag]);
                $count++;
            }
        }

        return $count;
    }

    public function dispose() : void
    {
        $pending = [];

        foreach ($this->getPipeable()?->getAllTags() ?? [] as $tag)
        {
            if (!$this->isStopped($tag))
            {
                $this->send($tag, 'STOP');
                $pending[] = $tag;
            }
        }

        $tries = 100;
        while ($pending && --$tries)
        {
            foreach ($pending as $i => $tag)
            {
                if ($this->isStopped($tag))
                {
                    unset($pending[$i]);
                }
            }

            usleep(100000);
        }
    }
}