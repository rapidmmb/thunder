<?php

namespace Mmb\Thunder\Tagger;

use Mmb\Core\Updates\Update;

interface Tagger
{

    /**
     * Make a tag for the update
     *
     * @param Update $update
     * @return string
     */
    public function tag(Update $update): string;

}