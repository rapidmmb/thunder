<?php

namespace Mmb\Thunder\Tagger;

use Mmb\Core\Updates\Update;

class ChatTagger implements Tagger
{

    public function tag(Update $update) : string
    {
        return $update->getChat()?->id ?? 'global';
    }

}