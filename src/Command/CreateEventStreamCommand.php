<?php

declare(strict_types=1);

namespace App\Command;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEventStreamCommand extends Command
{
    private $eventStore;

    public function __construct(EventStore $eventStore, ?string $name = null)
    {
        parent::__construct($name);

        $this->eventStore = $eventStore;
    }

    protected function configure()
    {
        $this->setName('app:event-stream:create')
            ->setDescription('Create new Event-Stream')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of event-stream')
            ->setHelp('Drop the Event-Store -- only in development');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $streamName = new StreamName($input->getArgument('name'));
        $this->eventStore->create(new Stream($streamName, new \ArrayIterator([])));
        $output->writeln('<info>'.'[OK] Event-Stream "'.$input->getArgument('name').'" was created'.'</info>');
    }
}
