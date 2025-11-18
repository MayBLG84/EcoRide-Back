<?php

namespace App\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-mongo',
    description: 'Tests the connection to MongoDB and lists collections'
)]
class TestMongoCommand extends Command
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        parent::__construct();
        $this->dm = $dm;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $db = $this->dm->getClient()->selectDatabase($_ENV['MONGODB_DB']);
            $collections = $db->listCollections();

            foreach ($collections as $collection) {
                $output->writeln('Collection: ' . $collection->getName());
            }

            $output->writeln('<info>Successfully connected to MongoDB!</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to connect: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
