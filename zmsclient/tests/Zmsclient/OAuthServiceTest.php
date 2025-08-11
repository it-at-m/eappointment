<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\OAuthService;
use BO\Zmsclient\Http;
use BO\Zmsentities\Config;
use BO\Zmsentities\Useraccount;
use BO\Zmsclient\Exception;
use PHPUnit\Framework\TestCase;

class OAuthServiceTest extends TestCase
{
    protected $httpMock;
    protected $oauthService;
    protected $configMock;

    protected function setUp(): void
    {
        $this->httpMock = $this->createMock(Http::class);
        $this->oauthService = new OAuthService($this->httpMock, 'secure-token');
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

    public function testReadConfigWithHttpError()
    {
        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/config/', [], 'secure-token')
            ->willThrowException(new Exception('HTTP request failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('HTTP request failed');
        
        $this->oauthService->readConfig();
    }

    public function testReadConfigWithInvalidResponse()
    {
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn(null);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/config/', [], 'secure-token')
            ->willReturn($resultMock);

        // The method expects to return Config, so we should test the actual behavior
        // If it returns null, that's an error condition that should throw an exception
        $this->expectException(\TypeError::class);
        $this->oauthService->readConfig();
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

    public function testAuthenticateWorkstationWithHttpError()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $state = 'test-state';

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, ['state' => $state])
            ->willThrowException(new Exception('Authentication failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Authentication failed');
        
        $this->oauthService->authenticateWorkstation($ownerData, $state);
    }

    public function testAuthenticateWorkstationWithEmptyResponse()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn([]);

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, [])
            ->willReturn($resultMock);

        $result = $this->oauthService->authenticateWorkstation($ownerData);
        $this->assertEquals([], $result);
    }

    public function testAuthenticateWorkstationWithNullResponse()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn(null);

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, [])
            ->willReturn($resultMock);

        $result = $this->oauthService->authenticateWorkstation($ownerData);
        $this->assertNull($result);
    }

    public function testAuthenticateWorkstationWithDifferentUserData()
    {
        $ownerData = new Useraccount([
            'username' => 'admin@keycloak', 
            'email' => 'admin@example.com',
            'role' => 'administrator'
        ]);
        $state = 'admin-state';
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn(['success' => true, 'role' => 'administrator']);

        $this->httpMock
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerData, ['state' => $state])
            ->willReturn($resultMock);

        $result = $this->oauthService->authenticateWorkstation($ownerData, $state);
        $this->assertEquals(['success' => true, 'role' => 'administrator'], $result);
    }

    public function testAuthenticateWorkstationWithSpecialCharactersInState()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $state = 'test-state-with-special-chars!@#$%^&*()';
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

    public function testAuthenticateWorkstationWithLongState()
    {
        $ownerData = new Useraccount(['username' => 'test@keycloak', 'email' => 'test@example.com']);
        $state = str_repeat('a', 1000); // Very long state
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

    public function testConstructorWithDifferentSecureTokens()
    {
        $oauthService1 = new OAuthService($this->httpMock, 'token1');
        $oauthService2 = new OAuthService($this->httpMock, 'token2');
        
        $this->assertInstanceOf(OAuthService::class, $oauthService1);
        $this->assertInstanceOf(OAuthService::class, $oauthService2);
        $this->assertNotSame($oauthService1, $oauthService2);
    }

    public function testConstructorWithEmptySecureToken()
    {
        $oauthService = new OAuthService($this->httpMock, '');
        $this->assertInstanceOf(OAuthService::class, $oauthService);
    }

    public function testConstructorWithNullSecureToken()
    {
        $this->expectException(\TypeError::class);
        new OAuthService($this->httpMock, null);
    }

    public function testReadConfigWithDifferentSecureTokens()
    {
        $oauthService = new OAuthService($this->httpMock, 'custom-token');
        $resultMock = $this->createMock(\BO\Zmsclient\Result::class);
        $resultMock->method('getEntity')->willReturn($this->configMock);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/config/', [], 'custom-token')
            ->willReturn($resultMock);

        $result = $oauthService->readConfig();
        $this->assertSame($this->configMock, $result);
    }
}
