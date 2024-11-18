<?php

namespace Mmb\Thunder\Puncher;

interface Pipeable
{

    public function getInput(string $tag);

    public function getOutput(string $tag);

    public function getAllTags() : array;

}