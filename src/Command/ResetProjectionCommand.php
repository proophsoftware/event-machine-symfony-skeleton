<?php

declare(strict_types=1);

namespace App\Command;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\Projecting\ProjectionRunner;
use Prooph\EventStore\Projection\ProjectionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetProjectionCommand extends Command
{
    private $eventMachine;
    private $projectionManager;

    public function __construct(EventMachine $eventMachine, ProjectionManager $projectionManager, ?string $name = null)
    {
        parent::__construct($name);

        $this->eventMachine = $eventMachine;
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
        $outputStyle = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("You are going to reset the event-machine-projection. Are you sure ? \n (y|n)", false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $outputStyle->title('Projektionen :');
        $outputStyle->listing($this->projectionManager->fetchProjectionNames(null));
        $outputStyle->success('Resetting '.ProjectionRunner::eventMachineProjectionName($this->eventMachine->appVersion()).' ...');
        $this->projectionManager->resetProjection(ProjectionRunner::eventMachineProjectionName($this->eventMachine->appVersion()));
    }
}
