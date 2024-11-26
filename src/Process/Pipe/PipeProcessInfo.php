<?php

namespace Mmb\Thunder\Process\Pipe;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Process\ProcessInfo;

class PipeProcessInfo implements ProcessInfo
{

    public function __construct(
        protected PipeProcessHandler $processHandler,
        protected string $tag,
    )
    {
    }

    protected bool $isEnded = false;

    protected mixed $process;
    protected mixed $in;
    protected mixed $out;

    protected int $lastActivityAt = 0;

    public function handle(Update $update) : void
    {
        $this->write($update);
    }

    public function open() : void
    {
        $command = config('thunder.puncher.process.command');

        $artisan = base_path('artisan');
        $command = str_replace(['[TAG]', '[ARTISAN]'], ['"' . addslashes($this->tag) . '"', '"' . addslashes($artisan) . '"'], $command);

        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        if (!$this->process = proc_open($command, $descriptors, $pipes))
        {
            throw new \Exception("Failed to open child process");
        }

        [$this->in, $this->out] = $pipes;
        $this->lastActivityAt = time();
    }

    public function end() : void
    {
        if ($this->isEnded) return;

        try
        {
            $this->write('FINISH');
            $this->processHandler->killAfter($this, 30); // todo : time from config
        }
        catch (\Throwable)
        {
            $this->kill();
        }

        $this->isEnded = true;
    }

    public function kill() : void
    {
        $this->isEnded = true;
        try
        {
            proc_terminate($this->process);
        }
        catch (\Throwable) { }
    }

    public function checkAlive() : bool
    {
        try
        {
            return is_resource($this->in) && !feof($this->in) && @proc_get_status($this->process)['running'];
        }
        catch (\Throwable)
        {
            return false;
        }
    }

    public function isEnded() : bool
    {
        return $this->isEnded;
    }

    public function lastActivityAt() : int
    {
        return $this->lastActivityAt;
    }

    public function updateTag(string $tag) : void
    {
        $this->tag = $tag;
        $this->write(['UPDATE:TAG', $tag]);
    }

    public function write(mixed $message) : void
    {
        $serialized = serialize($message);

        if (!fwrite($this->in, pack('J', strlen($serialized)) . $serialized))
        {
            throw new \Exception("Failed to write to child process");
        }

        $this->lastActivityAt = time();
    }
}