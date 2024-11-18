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

    protected array $resources = [];

    protected function getResource(string $tag)
    {
        if (!isset($this->resources[$tag]))
        {
            $path = $this->getLockFile($tag);

            if (!file_exists($path))
            {
                touch($path);
            }

            $resource = fopen($path, 'r+');
            fseek($resource, 0);
            return $this->resources[$tag] = $resource;
        }

        return $this->resources[$tag];
    }

    protected function getLockFile(string $tag)
    {
        return $this->path . '/' . hash('xxh3', $tag) . '.lock';
    }


    public function isStop(string $tag) : bool
    {
        return !file_exists($this->getLockFile($tag));
    }

    public function delete(string $tag) : void
    {
        if (file_exists($path = $this->getLockFile($tag)))
        {
            unlink($path);
        }

        unset($this->resources[$tag]);
    }

    public function send(string $tag, mixed $message) : void
    {
        $file = $this->getResource($tag);

        flock($file, LOCK_EX);

        if (fstat($file)['size'] <= 0)
        {
            fseek($file, 0);
        }

        $msg = serialize($message);

        ftruncate($file, ftell($file) + 8 + strlen($msg));

        fwrite($file, pack('J', strlen($msg)));
        fwrite($file, $msg);

        flock($file, LOCK_UN);
    }

    public function receive(string $tag) : mixed
    {
        $file = $this->getResource($tag);

        flock($file, LOCK_EX);

        if (fstat($file)['size'] - ftell($file) <= 0)
        {
            $msg = null;
        }
        else
        {
            $length = unpack('J', fread($file, 8))[1];
            $msg = fread($file, $length);

            if (fstat($file)['size'] - ftell($file) <= 0)
            {
                ftruncate($file, 0);
                fseek($file, 0);
            }
        }

        flock($file, LOCK_UN);

        return $msg === null ? null : unserialize($msg);
    }

    public function dispose() : void
    {
        $tags = array_keys($this->resources);

        foreach ($tags as $tag)
        {
            $this->send($tag, 'STOP');
        }

        $tags = array_flip($tags);

        while ($tags)
        {
            foreach ($tags as $tag => $_)
            {
                if ($this->isStop($tag))
                {
                    unset($tags[$tag]);
                }
            }

            usleep(100000);
        }
    }
}