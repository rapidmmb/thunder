<?php

namespace Mmb\Thunder;

use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Update;
use Mmb\Thunder\Tagger\Tagger;

/**
 * @method static Tagger getTagger()
 * @method static void punch(Update $update)
 * @method static string getLockPath()
 * @method static string getCommandPath()
 * @method static void setAsChild()
 * @method static bool getIsChild()
 */
class Thunder extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ThunderFactory::class;
    }

}