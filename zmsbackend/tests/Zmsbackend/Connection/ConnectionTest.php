<?php

namespace BO\Zmsbackend\Tests\Connection;

class ConnectionTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $connection = \BO\Zmsbackend\Connection\Select::getReadConnection();
        $this->assertInstanceOf("\\BO\\Zmsbackend\\Connection\\PdoInterface", $connection);
        $this->assertInstanceOf("\\PDO", $connection);
        $pdo = $connection->getPdo();
        $statement = $pdo->prepare('SHOW STATUS LIKE "Threads_connected";');
        $this->assertTrue($statement->execute(), "Could not connect to DB");
        $result = $statement->fetchAll();
        $this->assertEquals('Threads_connected', $result[0]['Variable_name']);
    }

    public function testCommit()
    {
        \BO\Zmsbackend\Connection\Select::setTransaction();
        $writer = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $sql = 'INSERT INTO `sessiondata` SET sessionid="unittest", sessionname="unittest"';
        $writer->exec($sql);
        $reader = \BO\Zmsbackend\Connection\Select::getReadConnection();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        \BO\Zmsbackend\Connection\Select::writeCommit();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        $writer->exec('DELETE FROM `sessiondata` WHERE sessionid="unittest"');
        \BO\Zmsbackend\Connection\Select::writeCommit();
    }

    public function testRollback()
    {
        \BO\Zmsbackend\Connection\Select::setTransaction();
        $writer = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $sql = 'INSERT INTO `sessiondata` SET sessionid="unittest", sessionname="unittest"';
        $writer->exec($sql);
        $reader = \BO\Zmsbackend\Connection\Select::getReadConnection();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        \BO\Zmsbackend\Connection\Select::writeRollback();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertFalse($insert);
    }
}
