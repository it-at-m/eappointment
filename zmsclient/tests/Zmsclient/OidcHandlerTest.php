<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\Auth;
use BO\Zmsclient\Http;
use BO\Zmsclient\OidcHandler;
use BO\Zmsclient\Result;
use BO\Zmsentities\Collection\DepartmentList;
use BO\Zmsentities\Department;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
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

        $workstation = $this->createWorkstationEntity(0);

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

        $workstation = $this->createWorkstationEntity(3);

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

    public function testHandleCallbackThrowsWhenWorkstationEntityMissing(): void
    {
        $authKey = 'matching-state-token';
        $_COOKIE[Auth::getCookieName()] = $authKey;

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('getEntity')->willReturn(false);

        $this->httpMock
            ->expects($this->once())
            ->method('readGetResult')
            ->with('/workstation/', ['resolveReferences' => 2])
            ->willReturn($resultMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OIDC workstation lookup returned no entity');

        $this->handler->handleCallback($authKey, 'zmsadmin');
    }

    private function createWorkstationEntity(int $departmentCount): Workstation
    {
        $departmentList = new DepartmentList();
        for ($i = 0; $i < $departmentCount; $i++) {
            $departmentList->addEntity(new Department(['id' => $i + 1]));
        }
        $useraccount = new Useraccount([
            'id' => 'user-1',
            'departments' => $departmentList,
        ]);

        return new Workstation([
            'id' => 1,
            'useraccount' => $useraccount,
        ]);
    }
}
