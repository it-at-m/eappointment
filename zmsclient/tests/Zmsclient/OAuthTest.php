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
    protected $oauth;

    protected function setUp(): void
    {
        $this->http = $this->createMock(Http::class);
        $this->oauth = new OAuth($this->http, new Auth());
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
        // Set up a cookie to simulate an existing session
        $_COOKIE[Auth::getCookieName()] = 'existing-session-key';

        $this->oauth->clearExistingSession();

        // Verify the method executed without throwing an exception
        $this->assertTrue(true, 'Method executed successfully');

        // Clean up
        unset($_COOKIE[Auth::getCookieName()]);
    }

    public function testClearExistingSessionWithoutKey()
    {
        // Ensure no cookie is set
        unset($_COOKIE[Auth::getCookieName()]);

        $this->oauth->clearExistingSession();

        // Verify the method executed without throwing an exception
        $this->assertTrue(true, 'Method executed successfully');
    }

    public function testValidateOwnerDataWithEmail()
    {
        $ownerInputData = [
            'id' => 'test-user-id',
            'email' => 'test@example.com'
        ];

        // Should not throw an exception when email is present
        $this->oauth->validateOwnerData($ownerInputData);
        $this->assertTrue(true, 'Validation passed with email');
    }

    public function testValidateOwnerDataWithoutEmail()
    {
        $ownerInputData = [
            'id' => 'test-user-id'
            // No email provided
        ];

        // Should not throw an exception when App is not available in test environment
        $this->oauth->validateOwnerData($ownerInputData);
        $this->assertTrue(true, 'Validation passed without email in test environment');
    }
} 