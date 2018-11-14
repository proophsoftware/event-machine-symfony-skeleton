<?php

declare(strict_types=1);

namespace App\Utils;

use App\Infrastructure\Plugin\MetadataEnricherPluginFactory;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventMachine\EventMachine;
use Prooph\EventStore\Pdo\PersistenceStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;

class EventStoreFactory
{
    public static function create(EventMachine $eventMachine, PersistenceStrategy $persistenceStrategy, \PDO $pdoConnection, MetadataEnricherPluginFactory $metadataEnricherPluginFactory): TransactionalActionEventEmitterEventStore
    {
        $eventStore = new PostgresEventStore(
            $eventMachine->messageFactory(),
            $pdoConnection,
            $persistenceStrategy
        );

        $transactionalActionEventEmitterEventStore = new TransactionalActionEventEmitterEventStore(
            $eventStore,
            new ProophActionEventEmitter(TransactionalActionEventEmitterEventStore::ALL_EVENTS)
        );

        $metadataEnricherPluginFactory->createMetadataEnricherPlugin()->attachToEventStore($transactionalActionEventEmitterEventStore);

        return $transactionalActionEventEmitterEventStore;
    }
}
