<?php

use Mmb\Thunder\Tagger;

return [

    /*
    |--------------------------------------------------------------------------
    | Thunder driver
    |--------------------------------------------------------------------------
    | Thunder driver manage the process and handling update in the background,
    | kill the unused process and so much other.
    |
    | Allowed types: pipe
    |
    */
    'driver' => 'pipe',

    /*
    |--------------------------------------------------------------------------
    | Idle worker count
    |--------------------------------------------------------------------------
    | Thunder keep some idle workers in the background ready, to handle updates
    | using that ready processes.
    | This feature makes the thunder so efficient.
    |
    */
    'idle_count' => 3,

    /*
    |--------------------------------------------------------------------------
    | Timeout interval
    |--------------------------------------------------------------------------
    | If a process has no job anymore, for example a user send an update and
    | then close the bot. In this case, a timeout is enabled to close unused
    | child processes.
    |
    */
    'timeout_interval' => 100,

    /*
    |--------------------------------------------------------------------------
    | Command
    |--------------------------------------------------------------------------
    | This command run a new process using Thunder core. You can create a
    | custom command and replace that in this section.
    |
    */
    'command' => 'php [ARTISAN] thunder:run-process [TAG]',

    /*
    |--------------------------------------------------------------------------
    | Tagger class
    |--------------------------------------------------------------------------
    | Tagger, tag the update by some cases like [Chat], and then pass the
    | updates in the specific process that is marked as same tag.
    | The updates will run as a queue, so selection of tagger is so important.
    |
    */
    'tagger' => Tagger\ChatTagger::class,

    /*
    |--------------------------------------------------------------------------
    | Hook long
    |--------------------------------------------------------------------------
    | Time that telegram waits for new updates in the [getUpdates] method.
    | Long time is a good choice.
    |
    */
    'hook_long' => 60,

];
