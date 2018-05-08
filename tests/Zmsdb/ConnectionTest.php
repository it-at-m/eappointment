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

    public function testCommit()
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $writer = \BO\Zmsdb\Connection\Select::getWriteConnection();
        $sql = 'INSERT INTO `sessiondata` SET sessionid="unittest", sessionname="unittest"';
        $writer->exec($sql);
        $reader = \BO\Zmsdb\Connection\Select::getReadConnection();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        \BO\Zmsdb\Connection\Select::writeCommit();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        $writer->exec('DELETE FROM `sessiondata` WHERE sessionid="unittest"');
        \BO\Zmsdb\Connection\Select::writeCommit();
    }

    public function testRollback()
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        $writer = \BO\Zmsdb\Connection\Select::getWriteConnection();
        $sql = 'INSERT INTO `sessiondata` SET sessionid="unittest", sessionname="unittest"';
        $writer->exec($sql);
        $reader = \BO\Zmsdb\Connection\Select::getReadConnection();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertEquals('unittest', $insert['sessionname']);
        \BO\Zmsdb\Connection\Select::writeRollback();
        $insert = $reader->fetchOne('SELECT * FROM `sessiondata` WHERE sessionname="unittest"');
        $this->assertFalse($insert);
    }
}
