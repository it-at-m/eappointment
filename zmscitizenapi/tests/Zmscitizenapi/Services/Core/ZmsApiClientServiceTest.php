<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\Services\Core\ZmsApiClientService;
use BO\Zmsclient\Http;
use BO\Zmsclient\Result;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Process;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Source;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Collection\ScopeList;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class ZmsApiClientServiceTest extends TestCase
{
    private $httpMock;
    private $cacheMock;
    private $source;

    protected function setUp(): void
    {
        parent::setUp();
        Application::$source_name = 'unittest';

        // Mock HTTP client
        $this->httpMock = $this->createMock(Http::class);
        Application::$http = $this->httpMock;

        // Mock cache
        $this->cacheMock = $this->createMock(CacheInterface::class);
        Application::$cache = $this->cacheMock;

        // Setup test source
        $this->source = new Source();
        $this->source->providers = new ProviderList();
        $this->source->scopes = new ScopeList();
        $this->source->requests = new RequestList();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Application::$http = null;
        Application::$cache = null;
    }

    public function testGetOfficesCacheHit(): void
    {
        $this->cacheMock->method('get')
            ->with('source_unittest')
            ->willReturn($this->source);

        $this->httpMock->expects($this->never())
            ->method('readGetResult');

        $result = ZmsApiClientService::getOffices();
        $this->assertInstanceOf(ProviderList::class, $result);
    }

    public function testGetOfficesCacheMiss(): void
    {
        $sourceCacheSet = false;

        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($this->source);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->with('/source/unittest/', ['resolveReferences' => 2])
            ->willReturn($result);

        $this->cacheMock->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$sourceCacheSet) {
                if ($key === 'source_unittest' && $value === $this->source && $ttl === Application::$SOURCE_CACHE_TTL) {
                    $sourceCacheSet = true;
                }
                return true;
            });

        $result = ZmsApiClientService::getOffices();
        $this->assertInstanceOf(ProviderList::class, $result);
        $this->assertTrue($sourceCacheSet, 'Source was not cached');
    }

    public function testGetOfficesInvalidResponse(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getOffices();
        $this->assertInstanceOf(ProviderList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetOfficesException(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getOffices();
    }

    public function testGetScopesCacheHit(): void
    {
        $this->cacheMock->method('get')
            ->with('source_unittest')
            ->willReturn($this->source);

        $this->httpMock->expects($this->never())
            ->method('readGetResult');

        $result = ZmsApiClientService::getScopes();
        $this->assertInstanceOf(ScopeList::class, $result);
    }

    public function testGetScopesCacheMiss(): void
    {
        $sourceCacheSet = false;

        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($this->source);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->with('/source/unittest/', ['resolveReferences' => 2])
            ->willReturn($result);

        $this->cacheMock->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$sourceCacheSet) {
                if ($key === 'source_unittest' && $value === $this->source && $ttl === Application::$SOURCE_CACHE_TTL) {
                    $sourceCacheSet = true;
                }
                return true;
            });

        $result = ZmsApiClientService::getScopes();
        $this->assertInstanceOf(ScopeList::class, $result);
        $this->assertTrue($sourceCacheSet, 'Source was not cached');
    }

    public function testGetScopesInvalidResponse(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $emptySource = new Source();
        $emptySource->providers = new ProviderList();
        $emptySource->scopes = new ScopeList();
        $emptySource->requests = new RequestList();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($emptySource);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getScopes();
        $this->assertInstanceOf(ScopeList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetScopesException(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getScopes();
    }

    public function testGetServicesCacheHit(): void
    {
        $this->cacheMock->method('get')
            ->with('source_unittest')
            ->willReturn($this->source);

        $this->httpMock->expects($this->never())
            ->method('readGetResult');

        $result = ZmsApiClientService::getServices();
        $this->assertInstanceOf(RequestList::class, $result);
    }

    public function testGetServicesCacheMiss(): void
    {
        $sourceCacheSet = false;

        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($this->source);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->with('/source/unittest/', ['resolveReferences' => 2])
            ->willReturn($result);

        $this->cacheMock->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$sourceCacheSet) {
                if ($key === 'source_unittest' && $value === $this->source && $ttl === Application::$SOURCE_CACHE_TTL) {
                    $sourceCacheSet = true;
                }
                return true;
            });

        $result = ZmsApiClientService::getServices();
        $this->assertInstanceOf(RequestList::class, $result);
        $this->assertTrue($sourceCacheSet, 'Source was not cached');
    }

    public function testGetServicesInvalidResponse(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getServices();
        $this->assertInstanceOf(RequestList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetServicesException(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getServices();
    }

    // Methods using source cache
    public function testGetRequestRelationListCacheHit(): void
    {
        $this->cacheMock->method('get')
            ->with('source_unittest')
            ->willReturn($this->source);

        $this->httpMock->expects($this->never())
            ->method('readGetResult');

        $result = ZmsApiClientService::getRequestRelationList();
        $this->assertInstanceOf(RequestRelationList::class, $result);
    }

    public function testGetRequestRelationListCacheMiss(): void
    {
        $sourceCacheSet = false;

        $this->cacheMock->method('get')->willReturn(null);

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($this->source);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->with('/source/unittest/', ['resolveReferences' => 2])
            ->willReturn($result);

        $this->cacheMock->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$sourceCacheSet) {
                if ($key === 'source_unittest' && $value === $this->source && $ttl === Application::$SOURCE_CACHE_TTL) {
                    $sourceCacheSet = true;
                }
                return true;
            });

        $result = ZmsApiClientService::getRequestRelationList();
        $this->assertInstanceOf(RequestRelationList::class, $result);
        $this->assertTrue($sourceCacheSet, 'Source was not cached');
    }

    public function testGetRequestRelationListInvalidResponse(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $emptySource = new Source();
        $emptySource->providers = new ProviderList();
        $emptySource->scopes = new ScopeList();
        $emptySource->requests = new RequestList();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($emptySource);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getRequestRelationList();
        $this->assertInstanceOf(RequestRelationList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetRequestRelationListException(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getRequestRelationList();
    }

    public function testGetScopesByProviderIdCacheHit(): void
    {
        $scope = new Scope();
        $scope->id = 1;
        $scope->provider = new Provider();
        $scope->provider->id = 100;
        $scope->provider->source = 'unittest';

        $this->source->scopes = new ScopeList([$scope]);

        $this->cacheMock->method('get')
            ->with('source_unittest')
            ->willReturn($this->source);

        $this->httpMock->expects($this->never())
            ->method('readGetResult');

        $result = ZmsApiClientService::getScopesByProviderId('unittest', 100);
        $this->assertInstanceOf(ScopeList::class, $result);
        $this->assertCount(1, $result);
    }

    public function testGetScopesByProviderIdInvalidResponse(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $emptySource = new Source();
        $emptySource->providers = new ProviderList();
        $emptySource->scopes = new ScopeList();
        $emptySource->requests = new RequestList();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($emptySource);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getScopesByProviderId('unittest', 100);
        $this->assertInstanceOf(ScopeList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetScopesByProviderIdException(): void
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getScopesByProviderId('unittest', 100);
    }

    // Methods making direct API calls
    public function testGetFreeDaysSuccess(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $calendar = new Calendar();
        $calendar->days = [
            (object) ['year' => 2025, 'month' => 1, 'day' => 15]
        ];

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($calendar);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/calendar/', $this->isInstanceOf(Calendar::class))
            ->willReturn($result);

        $result = ZmsApiClientService::getFreeDays($providers, $requests, $firstDay, $lastDay);
        $this->assertInstanceOf(Calendar::class, $result);
        $this->assertCount(1, $result->days);
    }

    public function testGetFreeDaysInvalidResponse(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getFreeDays($providers, $requests, $firstDay, $lastDay);
        $this->assertInstanceOf(Calendar::class, $result);
        $this->assertEmpty($result->days);
    }

    public function testGetFreeDaysException(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getFreeDays($providers, $requests, $firstDay, $lastDay);
    }

    public function testGetFreeTimeslotsSuccess(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $processList = new ProcessList([new Process()]);

        $result = $this->createMock(Result::class);
        $result->method('getCollection')->willReturn($processList);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/status/free/', $this->isInstanceOf(Calendar::class))
            ->willReturn($result);

        $result = ZmsApiClientService::getFreeTimeslots($providers, $requests, $firstDay, $lastDay);
        $this->assertInstanceOf(ProcessList::class, $result);
        $this->assertCount(1, $result);
    }

    public function testGetFreeTimeslotsInvalidResponse(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $result = $this->createMock(Result::class);
        $result->method('getCollection')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getFreeTimeslots($providers, $requests, $firstDay, $lastDay);
        $this->assertInstanceOf(ProcessList::class, $result);
        $this->assertEmpty($result);
    }

    public function testGetFreeTimeslotsException(): void
    {
        $providers = new ProviderList([['id' => 1]]);
        $requests = new RequestList([['id' => 1]]);
        $firstDay = ['year' => 2025, 'month' => 1, 'day' => 1];
        $lastDay = ['year' => 2025, 'month' => 1, 'day' => 31];

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getFreeTimeslots($providers, $requests, $firstDay, $lastDay);
    }

    public function testReserveTimeslotSuccess(): void
    {
        $process = new Process();
        $process->appointments = [(object) ['date' => '1724907600']];
        $process->scope = new Scope();
        $process->scope->id = 1;

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/status/reserved/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::reserveTimeslot($process, [1], [1]);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals('1724907600', $result->appointments[0]->date);
    }

    public function testReserveTimeslotInvalidResponse(): void
    {
        $process = new Process();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::reserveTimeslot($process, [1], [1]);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->appointments);
    }

    public function testReserveTimeslotException(): void
    {
        $process = new Process();

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::reserveTimeslot($process, [1], [1]);
    }

    public function testSubmitClientDataSuccess(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/1/test/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::submitClientData($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testSubmitClientDataInvalidResponse(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::submitClientData($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testSubmitClientDataException(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::submitClientData($process);
    }

    public function testPreconfirmProcessSuccess(): void
    {
        $process = new Process();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/status/preconfirmed/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::preconfirmProcess($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testPreconfirmProcessInvalidResponse(): void
    {
        $process = new Process();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::preconfirmProcess($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testPreconfirmProcessException(): void
    {
        $process = new Process();

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::preconfirmProcess($process);
    }

    public function testConfirmProcessSuccess(): void
    {
        $process = new Process();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/status/confirmed/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::confirmProcess($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testConfirmProcessInvalidResponse(): void
    {
        $process = new Process();

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::confirmProcess($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testConfirmProcessException(): void
    {
        $process = new Process();

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::confirmProcess($process);
    }

    public function testCancelAppointmentSuccess(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readDeleteResult')
            ->with('/process/1/test/', [])
            ->willReturn($result);

        $result = ZmsApiClientService::cancelAppointment($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testCancelAppointmentInvalidResponse(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readDeleteResult')
            ->willReturn($result);

        $result = ZmsApiClientService::cancelAppointment($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testCancelAppointmentException(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $this->httpMock->method('readDeleteResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::cancelAppointment($process);
    }

    public function testSendConfirmationEmailSuccess(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/1/test/confirmation/mail/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::sendConfirmationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testSendConfirmationEmailInvalidResponse(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::sendConfirmationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testSendConfirmationEmailException(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::sendConfirmationEmail($process);
    }

    public function testSendPreconfirmationEmailSuccess(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/1/test/preconfirmation/mail/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::sendPreconfirmationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testSendPreconfirmationEmailInvalidResponse(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::sendPreconfirmationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testSendPreconfirmationEmailException(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::sendPreconfirmationEmail($process);
    }

    public function testSendCancelationEmailSuccess(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->with('/process/1/test/delete/mail/', $this->isInstanceOf(Process::class))
            ->willReturn($result);

        $result = ZmsApiClientService::sendCancelationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
    }

    public function testSendCancelationEmailInvalidResponse(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readPostResult')
            ->willReturn($result);

        $result = ZmsApiClientService::sendCancelationEmail($process);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testSendCancelationEmailException(): void
    {
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test';

        $this->httpMock->method('readPostResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::sendCancelationEmail($process);
    }

    public function testGetProcessByIdSuccess(): void
    {
        $process = new Process();
        $process->id = 1;

        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($process);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->with('/process/1/test/', ['resolveReferences' => 2])
            ->willReturn($result);

        $result = ZmsApiClientService::getProcessById(1, 'test');
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testGetProcessByIdInvalidResponse(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn(null);

        $this->httpMock->expects($this->once())
            ->method('readGetResult')
            ->willReturn($result);

        $result = ZmsApiClientService::getProcessById(1, 'test');
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEmpty($result->id);
    }

    public function testGetProcessByIdException(): void
    {
        $this->httpMock->method('readGetResult')
            ->willThrowException(new \Exception('Test error'));

        $this->expectException(\RuntimeException::class);
        ZmsApiClientService::getProcessById(1, 'test');
    }
}