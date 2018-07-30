<?php

namespace App\Utils;

use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventMachine\EventMachine;
use Prooph\EventStore\Pdo\PersistenceStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;

class EventStoreFactory
{
    public static function create(EventMachine $eventMachine, PersistenceStrategy $persistenceStrategy, \PDO $pdoConnection): TransactionalActionEventEmitterEventStore
    {
        $eventStore = new PostgresEventStore(
                $eventMachine->messageFactory(),
                $pdoConnection,
                $persistenceStrategy
            );

        return new TransactionalActionEventEmitterEventStore(
                $eventStore,
                new ProophActionEventEmitter(TransactionalActionEventEmitterEventStore::ALL_EVENTS)
            );
    }
}
