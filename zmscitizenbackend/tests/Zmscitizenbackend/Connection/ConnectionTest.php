<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Connection;

use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Connection\Exception\PDOFailed;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Select::closeReadConnection();
        Select::closeWriteConnection();
        Select::setTransaction(false);
        Select::setProfiling(false);
        Select::setQueryCache(true);
        parent::tearDown();
    }

    public function testBootstrapWiresSelectFromApp(): void
    {
        $this->assertSame(\App::DB_DSN_READONLY, Select::$readSourceName);
        $this->assertSame(\App::DB_DSN_READWRITE, Select::$writeSourceName);
        $this->assertSame(\App::DB_USERNAME, Select::$username);
        $this->assertSame(\App::DB_PASSWORD, Select::$password);
        $this->assertFalse(Select::$galeraConnection);
        $this->assertFalse(Select::hasReadConnection());
        $this->assertFalse(Select::hasWriteConnection());
    }

    public function testGetReadConnectionFailsWithUnreachableDsn(): void
    {
        Select::closeReadConnection();
        Select::$readSourceName = 'mysql:host=127.0.0.1;port=1;dbname=nope';
        Select::$username = 'nope';
        Select::$password = 'nope';

        $this->expectException(PDOFailed::class);
        Select::getReadConnection();
    }
}
