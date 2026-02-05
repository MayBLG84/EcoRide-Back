<?php

namespace App\Tests\Database;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        if ($_ENV['APP_ENV'] !== 'test') {
            throw new \RuntimeException('DatabaseTestCase can only run in APP_ENV=test');
        }

        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->resetDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->em)) {
            $this->em->close();
        }
    }

    protected function resetDatabase(): void
    {
        $commands = [
            ['php', 'bin/console', 'doctrine:database:drop', '--force', '--if-exists'],
            ['php', 'bin/console', 'doctrine:database:create'],
            ['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction'],
        ];

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(
                    sprintf(
                        "Error to execute command:\n%s\n\n%s",
                        implode(' ', $command),
                        $process->getErrorOutput()
                    )
                );
            }
        }
    }
}
