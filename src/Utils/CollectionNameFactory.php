<?php

declare(strict_types=1);

namespace App\Utils;

use Prooph\EventMachine\EventMachine;

class CollectionNameFactory
{
    private $eventMachine;

    public function __construct(EventMachine $eventMachine)
    {
        $this->eventMachine = $eventMachine;
    }
}
