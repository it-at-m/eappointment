<?php

namespace BO\Slim\Tests\Middleware\OAuth;

use BO\Slim\Middleware\OAuth\KeycloakInstance;
use BO\Zmsclient\OAuth;
use BO\Zmsclient\Auth;
use BO\Zmsclient\Http;
use BO\Zmsclient\Result;
use BO\Zmsclient\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class KeycloakInstanceTest extends TestCase
{
    protected $instance;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        $this->instance = $this->getMockBuilder(KeycloakInstance::class)
            ->onlyMethods(['getAccessToken', 'testAccess', 'testOwnerData', 'writeTokenToSession', 'writeDeleteSession'])
            ->getMock();

        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    public function testDoLoginUsesZmsclientOAuth()
    {
        $ownerInputData = [
            'id' => 'test-user-id',
            'email' => 'test@example.com'
        ];

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('code')
            ->willReturn('test-code');

        $this->instance
            ->expects($this->once())
            ->method('getAccessToken')
            ->with('test-code')
            ->willReturn($this->createMock(\League\OAuth2\Client\Token\AccessToken::class));

        $this->instance
            ->expects($this->once())
            ->method('testAccess');

        $this->instance
            ->expects($this->once())
            ->method('testOwnerData')
            ->with($ownerInputData);

        $this->instance
            ->expects($this->once())
            ->method('writeTokenToSession');

        // Mock the provider to return owner data
        $provider = $this->createMock(\BO\Slim\Middleware\OAuth\Keycloak\Provider::class);
        $provider->method('getResourceOwnerData')->willReturn($ownerInputData);
        $this->instance->method('getProvider')->willReturn($provider);

        // Mock Auth to return a key
        $authMock = $this->createMock(Auth::class);
        $authMock->method('getKey')->willReturn('test-state-key');
        
        // Mock the OAuth class
        $oauthMock = $this->createMock(OAuth::class);
        $oauthMock->expects($this->once())->method('clearExistingSession');
        $oauthMock->expects($this->once())->method('processOAuthLogin')
            ->with($ownerInputData, 'test-state-key');

        // We need to mock the global App::$http
        $httpMock = $this->createMock(Http::class);
        $httpMock->method('readGetResult')->willReturn($this->createMock(Result::class));
        
        // Mock the global App class
        $appMock = $this->createMock(\stdClass::class);
        $appMock->http = $httpMock;
        $appMock->log = $this->createMock(\stdClass::class);
        $appMock->log->method('info');
        $appMock->log->method('error');
        
        // Use reflection to set the mock
        $reflection = new \ReflectionClass($this->instance);
        $property = $reflection->getProperty('provider');
        $property->setAccessible(true);
        $property->setValue($this->instance, $provider);

        $this->instance->doLogin($this->request, $this->response);
    }

    public function testDoLoginHandlesException()
    {
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('code')
            ->willReturn('test-code');

        $exception = new Exception('OAuth failed');

        $this->instance
            ->expects($this->once())
            ->method('getAccessToken')
            ->willThrowException($exception);

        $this->instance
            ->expects($this->once())
            ->method('writeDeleteSession');

        $this->expectException(Exception::class);

        $this->instance->doLogin($this->request, $this->response);
    }
} 