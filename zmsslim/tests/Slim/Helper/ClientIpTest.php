<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        unset(
            $_SERVER['HTTP_CLIENT_IP'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['HTTP_X_FORWARDED'],
            $_SERVER['REMOTE_ADDR']
        );
        putenv('TRUSTED_PROXIES');
        parent::tearDown();
    }

    public function testReturnsRemoteAddrWhenThePeerIsNotATrustedProxy(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $_SERVER['HTTP_CLIENT_IP'] = '198.51.100.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 203.0.113.50';

        $this->assertSame('203.0.113.10', ClientIp::getClientIp());
    }

    public function testUsesTheAddressAppendedByATrustedGateway(): void
    {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 203.0.113.10';

        $this->assertSame('203.0.113.10', ClientIp::getClientIp());
    }

    public function testIgnoresAForwardedListWhoseLastValueIsNotAnAddress(): void
    {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, not-an-ip';

        $this->assertSame('10.0.0.5', ClientIp::getClientIp());
    }

    public function testFallsBackToRemoteAddrWhenForwardedHeaderIsInvalid(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';

        $this->assertSame('203.0.113.10', ClientIp::getClientIp());
    }

    public function testFallbackWhenNoHeaders(): void
    {
        $this->assertSame('127.0.0.1', ClientIp::getClientIp());
    }

    public function testTrustsAConfiguredPublicProxy(): void
    {
        putenv('TRUSTED_PROXIES=203.0.113.0/24');
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 192.0.2.20';

        $this->assertSame('192.0.2.20', ClientIp::getClientIp());
    }
}
