<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);
        parent::tearDown();
    }

    public function testReturnsRemoteAddrWhenSet(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $this->assertSame('203.0.113.10', ClientIp::getClientIp());
    }

    public function testReturnsFirstForwardedIp(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 203.0.113.10';
        $this->assertSame('198.51.100.1', ClientIp::getClientIp());
    }

    public function testFallbackWhenNoHeaders(): void
    {
        $this->assertSame('127.0.0.1', ClientIp::getClientIp());
    }
}
