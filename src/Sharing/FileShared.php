<?php

namespace Mmb\Thunder\Sharing;

use Mmb\Thunder\Exceptions\ProcessShareFileNotFoundException;

class FileShared
{

    public $resource;

    public int $lastMessageAt;

    public bool $isExpired = false;

    public function __construct(
        public string $path,
        public bool $throwIfNotExists,
    )
    {
        if (!file_exists($path))
        {
            if ($this->throwIfNotExists)
            {
                throw new ProcessShareFileNotFoundException("File [$path] is not found");
            }

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

        $msg = null;
        if (fstat($this->resource)['size'] - ftell($this->resource) > 0)
        {
            if ($this->waitForLength(8))
            {
                $length = unpack('J', fread($this->resource, 8))[1];

                if ($this->waitForLength($length))
                {
                    $msg = fread($this->resource, $length);

                    if (fstat($this->resource)['size'] - ftell($this->resource) <= 0)
                    {
                        ftruncate($this->resource, 0);
                        fseek($this->resource, 0);
                    }
                }
                else
                {
                    fseek($this->resource, -8, SEEK_CUR);
                }
            }
        }

        flock($this->resource, LOCK_UN);

        return $msg === null ? null : unserialize($msg);
    }

    /**
     * Block the code for finished writing a length
     *
     * @param int $length
     * @return bool
     */
    public function waitForLength(int $length) : bool
    {
        $tries = 100;
        while (fstat($this->resource)['size'] - ftell($this->resource) < $length && --$tries)
        {
            usleep(2000);
        }

        return $tries > 0;
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
    public function requestStop()
    {
        $this->isExpired = true;
        $this->write('STOP');
        fclose($this->resource);
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