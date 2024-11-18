<?php

namespace Mmb\Thunder\Sharing;

use Mmb\Core\Updates\Update;

class FileSharing implements Sharing
{

    protected string $path;

    public function __construct()
    {
        $this->path = config('thunder.sharing.file.path', storage_path('thunder/sharing'));

        if (!file_exists($this->path))
        {
            mkdir($this->path, recursive: true);
        }
    }

    /**
     * @var array<string, FileShared>
     */
    protected array $resources = [];

    /**
     * Get a tag resource
     *
     * @param string $tag
     * @return FileShared
     */
    protected function getResource(string $tag) : FileShared
    {
        if (isset($this->resources[$tag]))
        {
            $file = $this->resources[$tag];

            if ($file->isExpired)
            {
                $tries = 10;
                while (!$file->isStopped() && --$tries)
                {
                    usleep(20000);
                }
            }
            elseif (!$file->isStopped())
            {
                return $file;
            }
        }

        return $this->resources[$tag] = new FileShared($this->getLockFile($tag));
    }

    /**
     * Get lock file from tag name
     *
     * @param string $tag
     * @return string
     */
    protected function getLockFile(string $tag)
    {
        return $this->path . '/' . hash('xxh3', $tag) . '.lock';
    }


    /**
     * Checks the process of tag is stopped or not
     *
     * @param string $tag
     * @return bool
     */
    public function isStopped(string $tag) : bool
    {
        return @$this->resources[$tag]?->isStopped() ?? true;
    }

    /**
     * Force stop and delete the tag
     *
     * @param string $tag
     * @return void
     */
    public function delete(string $tag) : void
    {
        @$this->resources[$tag]?->forceStop();

        if (file_exists($path = $this->getLockFile($tag)))
        {
            unlink($path);
        }

        unset($this->resources[$tag]);
    }

    public function send(string $tag, mixed $message) : void
    {
        $this->getResource($tag)->write($message);
    }

    public function receive(string $tag) : mixed
    {
        return $this->getResource($tag)->read();
    }

    public function dispose() : void
    {
        $willStop = array_filter($this->resources, fn ($file) => !$file->isStopped());

        foreach ($willStop as $tag => $file)
        {
            $this->delete($tag);
        }

        while ($willStop)
        {
            foreach ($willStop as $tag => $file)
            {
                if ($file->isStopped())
                {
                    unset($willStop[$tag]);
                }
            }

            usleep(100000);
        }
    }

    public function disposeOlderThan(int $timeout) : void
    {
        foreach ($this->resources as $file)
        {
            if (!$file->isExpired && !$file->isStopped() && time() - $file->lastMessageAt > $timeout)
            {
                $file->write('STOP');
                $file->isExpired = true;
            }
        }
    }
}