<?php

namespace App\Tests;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MongoConnectionTest extends KernelTestCase
{
    public function testConnection(): void
    {
        self::bootKernel();

        /** @var DocumentManager $dm */
        $dm = self::getContainer()->get('doctrine_mongodb.odm.document_manager');

        $client = $dm->getClient();

        // Pegando o nome do DB configurado
        $dbName = $dm->getConfiguration()->getDefaultDB();
        $db = $client->selectDatabase($dbName);

        // Comando simples para verificar stats do banco
        $stats = $db->command(['dbStats' => 1])->toArray()[0];

        print_r($stats); // opcional, apenas para debug visual
        $this->assertArrayHasKey('db', $stats);
    }
}
