<?php

namespace Mmb\Thunder\Process;

use Mmb\Core\Updates\Update;

interface ProcessHandler
{

    /**
     * Startup event
     *
     * @return void
     */
    public function startup() : void;

    /**
     * Time to checkup
     *
     * @return void
     */
    public function inspection() : void;

    /**
     * Handle the update
     *
     * @param Update $update
     * @return void
     */
    public function handle(Update $update) : void;

    /**
     * Stop the child processes
     *
     * @return void
     */
    public function stopAll() : void;

    /**
     * Force kill the child processes
     *
     * @return void
     */
    public function killAll() : void;

    /**
     * Checks has child process that is active
     *
     * @return bool
     */
    public function hasActiveProcess() : bool;

}