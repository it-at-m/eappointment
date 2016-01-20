<?php

namespace BO\Zmsdb\Tests;

class ConnectionTest extends Base
{
    public function testBasic()
    {
        $connection = \BO\Zmsdb\Connection\Select::getReadConnection();
        $this->assertInstanceOf("\\BO\\Zmsdb\\Connection\\PdoInterface", $connection);
        $this->assertInstanceOf("\\PDO", $connection);
        $pdo = $connection->getPdo();
        $statement = $pdo->prepare('SHOW STATUS LIKE "Threads_connected";');
        $this->assertTrue($statement->execute(), "Could not connect to DB");
        $result = $statement->fetchAll();
        $this->assertEquals('Threads_connected', $result[0]['Variable_name']);
    }
}
