<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\Auth;
use BO\Zmsclient\Http;
use BO\Zmsclient\OidcHandler;
use BO\Zmsclient\Result;
use PHPUnit\Framework\TestCase;

class OidcHandlerTest extends TestCase
{
    /** @var Http&\PHPUnit\Framework\MockObject\MockObject */
    private $httpMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $logMock;

    private OidcHandler $handler;

    private array $originalCookies = [];

    protected function setUp(): void
    {
        $this->originalCookies = $_COOKIE ?? [];
        $_COOKIE = [];

        $this->httpMock = $this->createMock(Http::class);
        $this->logMock = $this->createMock(\Monolog\Logger::class);
        $this->handler = new OidcHandler($this->httpMock, $this->logMock);
    }

    protected function tearDown(): void
    {
        $_COOKIE = $this->originalCookies;
    }

    public function testHandleCallbackThrowsOnMissingState(): void
    {
        $_COOKIE[Auth::getCookieName()] = 'expected-auth-key';

        $this->expectException(\BO\Slim\Exception\OAuthInvalid::class);

        $this->handler->handleCallback(null, 'zmsadmin');
    }

    public function testHandleCallbackThrowsOnEmptyState(): void
    {
        $_COOKIE[Auth::getCookieName()] = 'expected-auth-key';

        $this->expectException(\BO\Slim\Exception\OAuthInvalid::class);

        $this->handler->handleCallback('', 'zmsadmin');
    }

    public function testHandleCallbackThrowsWhenAuthKeyMissing(): void
    {
        $this->expectException(\BO\Slim\Exception\OAuthInvalid::class);

        $this->handler->handleCallback('some-state', 'zmsstatistic');
    }

    public function testHandleCallbackThrowsOnStateMismatch(): void
    {
        $_COOKIE[Auth::getCookieName()] = 'expected-auth-key';

        $this->expectException(\BO\Slim\Exception\OAuthInvalid::class);

        $this->handler->handleCallback('wrong-state', 'zmsadmin');
    }

    public function testHandleCallbackReturnsRedirectToIndexWhenNoDepartments(): void
    {
        $authKey = 'matching-state-token';
        $_COOKIE[Auth::getCookieName()] = $authKey;

        $workstation = $this->createWorkstationStub('user-1', 'ws-1', 0);

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('getEntity')->willReturn($workstation);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/workstation/', ['resolveReferences' => 2])
            ->willReturn($resultMock);

        $result = $this->handler->handleCallback($authKey, 'zmsadmin');

        $this->assertSame($workstation, $result['workstation']);
        $this->assertSame(0, $result['department_count']);
        $this->assertTrue($result['redirect_to_index']);
    }

    public function testHandleCallbackReturnsRedirectToWorkstationSelectWithDepartments(): void
    {
        $authKey = 'matching-state-token';
        $_COOKIE[Auth::getCookieName()] = $authKey;

        $workstation = $this->createWorkstationStub('user-1', 'ws-1', 3);

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('getEntity')->willReturn($workstation);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/workstation/', ['resolveReferences' => 2])
            ->willReturn($resultMock);

        $result = $this->handler->handleCallback($authKey, 'zmsstatistic');

        $this->assertSame(3, $result['department_count']);
        $this->assertFalse($result['redirect_to_index']);
    }

    public function testHandleCallbackPropagatesWorkstationLookupError(): void
    {
        $authKey = 'matching-state-token';
        $_COOKIE[Auth::getCookieName()] = $authKey;

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/workstation/', ['resolveReferences' => 2])
            ->willThrowException(new \RuntimeException('boom'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $this->handler->handleCallback($authKey, 'zmsadmin');
    }

    /**
     * State validation must use a constant-time comparison so length differences
     * do not produce false positives.
     */
    public function testHandleCallbackRejectsDifferentLengthStateMatchingPrefix(): void
    {
        $_COOKIE[Auth::getCookieName()] = 'abc123';

        $this->expectException(\BO\Slim\Exception\OAuthInvalid::class);

        $this->handler->handleCallback('abc1234', 'zmsadmin');
    }

    /**
     * Creates a stub object exposing the fluent shape used by OidcHandler.
     */
    private function createWorkstationStub(string $username, string $workstationId, int $departmentCount)
    {
        $departmentList = new class ($departmentCount) {
            private int $count;
            public function __construct(int $count)
            {
                $this->count = $count;
            }
            public function count(): int
            {
                return $this->count;
            }
        };

        $useraccount = new class ($username, $departmentList) {
            public string $id;
            private $departmentList;
            public function __construct(string $id, $departmentList)
            {
                $this->id = $id;
                $this->departmentList = $departmentList;
            }
            public function getDepartmentList()
            {
                return $this->departmentList;
            }
        };

        return new class ($useraccount, $workstationId) implements \ArrayAccess {
            public string $id;
            private $useraccount;
            public function __construct($useraccount, string $id)
            {
                $this->useraccount = $useraccount;
                $this->id = $id;
            }
            public function getUseraccount()
            {
                return $this->useraccount;
            }
            public function offsetExists($offset): bool
            {
                return $offset === 'authkey';
            }
            public function offsetGet($offset): mixed
            {
                return null;
            }
            public function offsetSet($offset, $value): void
            {
            }
            public function offsetUnset($offset): void
            {
            }
        };
    }
}
