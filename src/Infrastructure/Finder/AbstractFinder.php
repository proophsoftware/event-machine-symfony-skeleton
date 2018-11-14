<?php

declare(strict_types=1);

namespace App\Infrastructure\Finder;

use Prooph\EventMachine\Messaging\Message;
use Prooph\EventMachine\Persistence\DocumentStore;
use React\Promise\Deferred;

abstract class AbstractFinder
{
    /**
     * @var DocumentStore
     */
    protected $documentStore;

    /**
     * @var string
     */
    protected $collectionName;

    public function __construct(string $collectionName, DocumentStore $documentStore)
    {
        $this->collectionName = $collectionName;
        $this->documentStore = $documentStore;
    }

    abstract public function __invoke(Message $query, Deferred $deferred): void;
}
