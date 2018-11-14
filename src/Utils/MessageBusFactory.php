<?php

declare(strict_types=1);

namespace App\Utils;

use App\Infrastructure\ServiceBus\CommandBus;
use App\Infrastructure\ServiceBus\ErrorHandler;
use App\Infrastructure\ServiceBus\EventBus;
use App\Infrastructure\ServiceBus\QueryBus;

class MessageBusFactory
{
    public static function createCommandBus(): CommandBus
    {
        $errorHandler = new ErrorHandler();

        $commandBus = new CommandBus();

        $errorHandler->attachToMessageBus($commandBus);

        return $commandBus;
    }

    public static function createEventBus(): EventBus
    {
        $errorHandler = new ErrorHandler();

        $eventBus = new EventBus();

        $errorHandler->attachToMessageBus($eventBus);

        return $eventBus;
    }

    public static function createQueryBus(): QueryBus
    {
        $errorHandler = new ErrorHandler();

        $queryBus = new QueryBus();

        $errorHandler->attachToMessageBus($queryBus);

        return $queryBus;
    }
}
