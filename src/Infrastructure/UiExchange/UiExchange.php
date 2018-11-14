<?php

declare(strict_types=1);

namespace App\Infrastructure\UiExchange;

use Prooph\EventMachine\Messaging\Message;
use Prooph\ServiceBus\Message\HumusAmqp\AmqpMessageProducer;

class UiExchange implements \App\Infrastructure\ServiceBus\UiExchange
{
    private $producer;

    public function __construct(AmqpMessageProducer $messageProducer)
    {
        $this->producer = $messageProducer;
    }

    public function __invoke(Message $event): void
    {
        $this->producer->__invoke($event);
    }
}
