<?php

use Mmb\Thunder\Tagger;

return [

    /*
    |--------------------------------------------------------------------------
    | Thunder puncher
    |--------------------------------------------------------------------------
    |
    |
    |
    | Allowed types: process
    |
    */
    'puncher' => [
        'driver' => 'process',

        'process' => [
            'command' => 'php artisan thunder:run-process [TAG]',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thunder tagger
    |--------------------------------------------------------------------------
    |
    |
    |
    |
    |
    */
    'tagger' => [
        'class' => Tagger\ChatTagger::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Thunder handle sharing
    |--------------------------------------------------------------------------
    |
    |
    |
    | Allowed types: file
    |
    */
    'sharing' => [
        'driver' => 'file',

        'file' => [
            'path' => storage_path('thunder/share'),
        ],
    ],

];
