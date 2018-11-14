<?php

declare(strict_types=1);

namespace App\Utils;

use Prooph\EventMachine\Container\ContainerChain;
use Prooph\EventMachine\Container\EventMachineContainer;
use Prooph\EventMachine\EventMachine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventMachineFactory
{
    /**
     * @var EventMachine
     */
    private static $instance;

    public static function create(array $descriptions, ContainerInterface $container, string $version, string $env): EventMachine
    {
        if (null === static::$instance || 'test' === $env) {
            static::$instance = new EventMachine();

            foreach ($descriptions as $desc) {
                static::$instance->load($desc);
            }

            $containerChain = new ContainerChain(
                $container,
                new EventMachineContainer(static::$instance)
            );

            static::$instance->initialize($containerChain, $version);

            if ('test' !== $env) {
                static::$instance->bootstrap($env, 'prod' !== $env);
            }

            return static::$instance;
        }

        return static::$instance;
    }
}
