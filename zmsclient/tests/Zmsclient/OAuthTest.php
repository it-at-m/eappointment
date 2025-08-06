<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\OAuth;
use BO\Zmsclient\Http;
use BO\Zmsclient\Auth;
use BO\Zmsclient\Result;
use BO\Zmsclient\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class OAuthTest extends TestCase
{
    protected $http;
    protected $auth;
    protected $oauth;

    protected function setUp(): void
    {
        $this->http = $this->createMock(Http::class);
        $this->auth = $this->createMock(Auth::class);
        $this->oauth = new OAuth($this->http, $this->auth);
    }

    public function testProcessOAuthLoginSuccess()
    {
        $ownerInputData = [
            'id' => 'test-user-id',
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];
        $state = 'test-state-key';

        $expectedResult = $this->createMock(Result::class);

        $this->http
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerInputData, ['state' => $state])
            ->willReturn($expectedResult);

        $result = $this->oauth->processOAuthLogin($ownerInputData, $state);

        $this->assertSame($expectedResult, $result);
    }

    public function testProcessOAuthLoginFailure()
    {
        $ownerInputData = [
            'id' => 'test-user-id',
            'email' => 'test@example.com'
        ];
        $state = 'test-state-key';

        $exception = new Exception('OAuth login failed');

        $this->http
            ->expects($this->once())
            ->method('readPostResult')
            ->with('/workstation/oauth/', $ownerInputData, ['state' => $state])
            ->willThrowException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('OAuth login failed');

        $this->oauth->processOAuthLogin($ownerInputData, $state);
    }

    public function testClearExistingSessionWithKey()
    {
        $this->auth
            ->expects($this->once())
            ->method('getKey')
            ->willReturn('existing-session-key');

        $this->oauth->clearExistingSession();
    }

    public function testClearExistingSessionWithoutKey()
    {
        $this->auth
            ->expects($this->once())
            ->method('getKey')
            ->willReturn(null);

        $this->oauth->clearExistingSession();
    }
} 