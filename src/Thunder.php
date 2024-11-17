<?php

namespace Mmb\Thunder;

use Illuminate\Support\Facades\Facade;
use Mmb\Thunder\Puncher\Puncher;
use Mmb\Thunder\Sharing\Sharing;
use Mmb\Thunder\Tagger\Tagger;

/**
 * @method static Puncher getPuncher()
 * @method static Tagger getTagger()
 * @method static Sharing getSharing()
 */
class Thunder extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ThunderFactory::class;
    }

}