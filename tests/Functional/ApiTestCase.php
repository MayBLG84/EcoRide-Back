<?php

namespace App\Tests\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

abstract class ApiTestCase extends WebTestCase
{
    protected \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        // Cria o client uma vez
        $this->client = static::createClient();

        // Reseta o banco usando o mesmo client
        $this->resetDatabase($this->client);

        // EntityManager do client já criado
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Reset DB using the client (do not create a new client!)
     */
    protected function resetDatabase(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($em);
        $purger->purge();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
        ]));
    }

    /**
     * Helper para criar um client extra se necessário
     */
    protected static function createApiClient(array $options = [], array $server = []): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        return static::createClient($options, $server);
    }
}
