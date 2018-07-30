<?php

namespace App\Utils;

use Prooph\EventMachine\Container\ContainerChain;
use Prooph\EventMachine\Container\EventMachineContainer;
use Prooph\EventMachine\EventMachine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventMachineFactory
{
    private static $instance;

    public static function create(array $descriptions, ContainerInterface $container): EventMachine
    {
        if (null === static::$instance) {
            static::$instance = new EventMachine();

            foreach ($descriptions as $desc) {
                static::$instance->load($desc);
            }

            $containerChain = new ContainerChain(
                $container,
                new EventMachineContainer(static::$instance)
            );

            static::$instance->initialize($containerChain);

            return static::$instance;
        }

        return static::$instance;
    }
}
