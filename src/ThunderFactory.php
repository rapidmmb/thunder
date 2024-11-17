<?php

namespace Mmb\Thunder;

use Mmb\Core\Updates\Update;
use Mmb\Thunder\Puncher\ProcessPuncher;
use Mmb\Thunder\Puncher\Puncher;
use Mmb\Thunder\Sharing\FileSharing;
use Mmb\Thunder\Sharing\Sharing;
use Mmb\Thunder\Tagger\ChatTagger;
use Mmb\Thunder\Tagger\Tagger;

class ThunderFactory
{

    protected Puncher $puncher;

    public function getPuncher() : Puncher
    {
        if (!isset($this->puncher))
        {
            $driver = config('thunder.puncher.driver', 'process');

            $this->puncher = app()->make(
                match ($driver)
                {
                    'process' => ProcessPuncher::class,
                    default   => $driver,
                }
            );
        }

        return $this->puncher;
    }

    protected Tagger $tagger;

    public function getTagger() : Tagger
    {
        if (!isset($this->tagger))
        {
            $driver = config('thunder.tagger.class', ChatTagger::class);

            $this->tagger = app()->make($driver);
        }

        return $this->tagger;
    }

    protected Sharing $sharing;

    public function getSharing() : Sharing
    {
        if (!isset($this->sharing))
        {
            $driver = config('thunder.sharing.driver', 'file');

            $this->sharing = app()->make(
                match ($driver)
                {
                    'file'  => FileSharing::class,
                    default => $driver,
                }
            );
        }

        return $this->sharing;
    }

    public function punch(Update $update)
    {
        $tag = $this->getTagger()->tag($update);

        $this->getPuncher()->punch($tag);

        $this->getSharing()->send($tag, $update);
    }

}