<?php

namespace App\Core;

interface Job
{
    public function handle();
    public function getName();
    public function getQueue();
    public function getDelay();
}
