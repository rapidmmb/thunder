<?php

namespace Mmb\Thunder\Sharing;

use Mmb\Core\Updates\Update;

class FileSharing implements Sharing
{

    protected array $path;

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

            return $this->resources[$tag] = fopen($path, 'w+');
        }

        return $this->resources[$tag];
    }

    protected function getLockFile(string $tag)
    {
        return $this->path . '/' . hash('xxh3', $tag) . '.lock';
    }


    public function send(string $tag, mixed $message) : void
    {
        $file = $this->getResource($tag);

        flock($file, LOCK_EX);

        $msg = serialize($message);
        fwrite($file, pack('J', strlen($msg)));
        fwrite($file, $msg);

        flock($file, LOCK_UN);
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

    public function receive(string $tag) : mixed
    {
        $file = $this->getResource($tag);

        flock($file, LOCK_EX);

        if (feof($file))
        {
            $msg = null;
        }
        else
        {
            $length = unpack('J', fread($file, 8))[1];
            $msg = fread($file, $length);
        }

        flock($file, LOCK_UN);

        return $msg === null ? null : unserialize($msg);
    }
}