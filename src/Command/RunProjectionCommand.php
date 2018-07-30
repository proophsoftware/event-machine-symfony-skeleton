<?php

namespace App\Command;

use Prooph\EventMachine\EventMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunProjectionCommand extends Command
{
    private $eventMachine;
    private $env;

    public function __construct(EventMachine $eventMachine, string $env, ?string $name = null)
    {
        parent::__construct($name);

        $this->eventMachine = $eventMachine;
        $this->env = $env;
    }

    protected function configure()
    {
        $this->setName('app:projection:run')
            ->setDescription('run registered projections');
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

        $iterations = 0;

        while (true) {
            $this->eventMachine->runProjections(true);

            ++$iterations;

            if ($iterations > 100) {
                //force reload in dev mode by exiting with error so docker restarts the container
                exit(1);
            }

            \usleep(100);
        }
    }
}
