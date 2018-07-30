<?php

namespace App\Command;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\Projecting\ProjectionRunner;
use Prooph\EventStore\Projection\ProjectionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetProjectionCommand extends Command
{
    private $eventMachine;
    private $env;
    private $projectionManager;

    public function __construct(EventMachine $eventMachine, ProjectionManager $projectionManager, string $env, ?string $name = null)
    {
        parent::__construct($name);

        $this->eventMachine = $eventMachine;
        $this->env = $env;
        $this->projectionManager = $projectionManager;
    }

    protected function configure()
    {
        $this->setName('app:projection:reset')
            ->setDescription('reset registered projections');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $devMode = EventMachine::ENV_DEV === $this->env;
        $this->eventMachine->bootstrap($this->env, $devMode);

        $output->writeln('<info>'.'[OK] Resetting '.ProjectionRunner::eventMachineProjectionName($this->eventMachine->appVersion()).'</info>');
        $this->projectionManager->resetProjection(ProjectionRunner::eventMachineProjectionName($this->eventMachine->appVersion()));
    }
}
