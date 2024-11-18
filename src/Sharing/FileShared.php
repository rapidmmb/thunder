<?php

namespace Mmb\Thunder\Sharing;

class FileShared
{

    public $resource;

    public int $lastMessageAt;

    public bool $isExpired = false;

    public function __construct(
        public string $path,
    )
    {
        if (!file_exists($path))
        {
            touch($path);
        }

        $this->resource = fopen($path, 'r+');
        fseek($this->resource, 0);
        $this->lastMessageAt = time();
    }

    /**
     * Write a message
     *
     * @param $message
     * @return void
     */
    public function write($message) : void
    {
        flock($this->resource, LOCK_EX);

        if (fstat($this->resource)['size'] <= 0)
        {
            fseek($this->resource, 0);
        }

        $msg = serialize($message);

        ftruncate($this->resource, ftell($this->resource) + 8 + strlen($msg));

        fwrite($this->resource, pack('J', strlen($msg)));
        fwrite($this->resource, $msg);

        flock($this->resource, LOCK_UN);

        $this->lastMessageAt = time();
    }

    /**
     * Read a message
     *
     * @return mixed
     */
    public function read() : mixed
    {
        flock($this->resource, LOCK_EX);

        if (fstat($this->resource)['size'] - ftell($this->resource) <= 0)
        {
            $msg = null;
        }
        else
        {
            $length = unpack('J', fread($this->resource, 8))[1];
            $msg = fread($this->resource, $length);

            if (fstat($this->resource)['size'] - ftell($this->resource) <= 0)
            {
                ftruncate($this->resource, 0);
                fseek($this->resource, 0);
            }
        }

        flock($this->resource, LOCK_UN);

        return $msg === null ? null : unserialize($msg);
    }

    /**
     * Checks the process is stopped
     *
     * @return bool
     */
    public function isStopped() : bool
    {
        return !file_exists($this->path);
    }

    /**
     * @return void
     */
    public function forceStop() : void
    {
        fclose($this->resource);
        @unlink($this->path);
    }

}