<?php

namespace App\Infrastructure\UiExchange;

use Humus\Amqp\Driver\AmqpExtension\Connection;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\HumusAmqp\AmqpMessageProducer;

class MessageProducerFactory
{
    public static function createMessageAmqpMessageProducer(Connection $connection, string $name): AmqpMessageProducer
    {
        $connection->connect();

        $channel = $connection->newChannel();

        $exchange = $channel->newExchange();

        $exchange->setName($name);

        $exchange->setType('fanout');

        $humusProducer = new \Humus\Amqp\JsonProducer($exchange);

        $messageProducer = new AmqpMessageProducer(
            $humusProducer,
            new NoOpMessageConverter()
        );

        return $messageProducer;
    }
}
