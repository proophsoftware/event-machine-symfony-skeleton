<?php

declare(strict_types=1);

namespace App\Command;

use Prooph\EventMachine\EventMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunProjectionCommand extends Command
{
    private $eventMachine;

    public function __construct(EventMachine $eventMachine, ?string $name = null)
    {
        parent::__construct($name);

        $this->eventMachine = $eventMachine;
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
