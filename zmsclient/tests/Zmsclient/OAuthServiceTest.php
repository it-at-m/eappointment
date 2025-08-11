<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\OAuthService;
use BO\Zmsclient\Http;
use BO\Zmsentities\Config;
use BO\Zmsentities\Useraccount;
use PHPUnit\Framework\TestCase;

class OAuthServiceTest extends TestCase
{
    protected $httpMock;
    protected $oauthService;
    protected $configMock;

    protected function setUp(): void
    {
        $this->httpMock = $this->createMock(Http::class);
        $this->oauthService = new OAuthService($this->httpMock);
        $this->configMock = $this->createMock(Config::class);
    }

    public function testReadConfig()
    {
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn($this->configMock);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/config/', [], 'secure-token')
            ->willReturn($resultMock);

        $result = $this->oauthService->readConfig();
        $this->assertSame($this->configMock, $result);
    }

    public function testAuthenticateWorkstation()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $state = 'test-state';
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn(['success' => true]);

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, ['state' => $state])
            ->willReturn($resultMock);

        $result = $this->oauthService->authenticateWorkstation($ownerData, $state);
        $this->assertEquals(['success' => true], $result);
    }

    public function testAuthenticateWorkstationWithoutState()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn(['success' => true]);

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, [])
            ->willReturn($resultMock);

        $result = $this->oauthService->authenticateWorkstation($ownerData);
        $this->assertEquals(['success' => true], $result);
    }
}
