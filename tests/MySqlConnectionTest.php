<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\DBAL\Driver\Connection;

class MySQLConnectionTest extends KernelTestCase
{
    public function testConnection(): void
    {
        self::bootKernel();

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = self::getContainer()->get('doctrine.dbal.default_connection');

        // Executa uma query simples para garantir que o DB existe e podemos ler dele
        $dbName = $conn->executeQuery('SELECT DATABASE()')->fetchOne();

        // Verifica se é o banco de testes
        $this->assertEquals('ecoride_test', $dbName);
    }
}
