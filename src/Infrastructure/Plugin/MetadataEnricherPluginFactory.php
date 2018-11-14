<?php

declare(strict_types=1);

namespace App\Infrastructure\Plugin;

use Assert\Assertion;
use Prooph\EventStore\Metadata\MetadataEnricher;
use Prooph\EventStore\Metadata\MetadataEnricherAggregate;
use Prooph\EventStore\Metadata\MetadataEnricherPlugin;

class MetadataEnricherPluginFactory
{
    private $plugins;

    public function __construct(array $plugins)
    {
        Assertion::allImplementsInterface($plugins, MetadataEnricher::class);

        $this->plugins = $plugins;
    }

    public function createMetadataEnricherPlugin(): MetadataEnricherPlugin
    {
        return new MetadataEnricherPlugin(new MetadataEnricherAggregate($this->plugins));
    }
}
