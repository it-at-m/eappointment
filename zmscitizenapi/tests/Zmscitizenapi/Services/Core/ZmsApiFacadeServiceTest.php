<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\App;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsclient\Http;
use BO\Zmsclient\Result;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Request;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Source;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class ZmsApiFacadeServiceTest extends TestCase
{
    private $httpMock;
    private $cacheMock;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        
        // Mock HTTP client
        $this->httpMock = $this->createMock(Http::class);
        \App::$http = $this->httpMock;
        
        // Mock cache
        $this->cacheMock = $this->createMock(CacheInterface::class);
        \App::$cache = $this->cacheMock;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \App::$http = null;
        \App::$cache = null;
    }

    public function testGetOfficesSuccess(): void
    {
        $provider = new Provider();
        $provider->id = 1;
        $provider->name = 'Test Provider';
        $provider->source = 'unittest';
        $provider->data = [
            'address' => [
                'street' => 'Test Street',
                'number' => '123',
                'city' => 'Test City',
                'postal_code' => '12345'
            ],
            'geo' => ['lat' => 48.137154, 'lon' => 11.576124]
        ];
    
        $scope = new Scope();
        $scope->id = 1;
        $scope->provider = $provider;
    
        $source = new Source();
        $source->providers = new ProviderList([$provider]);
        $source->scopes = new ScopeList([$scope]);
    
        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
            ->method('readGetResult')
            ->willReturn($result);
    
        $result = ZmsApiFacadeService::getOffices();
        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertCount(1, $result->offices);
        
        $office = $result->offices[0];
        $this->assertEquals(1, $office->id);
        $this->assertEquals('Test Provider', $office->name);
        $this->assertEquals([
            'street' => 'Test Street',
            'number' => '123',
            'city' => 'Test City',
            'postal_code' => '12345'
        ], $office->address);
        $this->assertEquals(['lat' => 48.137154, 'lon' => 11.576124], $office->geo);
        $this->assertInstanceOf(ThinnedScope::class, $office->scope);
    }

    public function testGetOfficesEmpty(): void
    {
        $source = new Source();
        $source->providers = new ProviderList();
        $source->scopes = new ScopeList();

        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
        ->method('readGetResult')
        ->willReturn($result);

        $result = ZmsApiFacadeService::getOffices();
        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertEmpty($result->offices);
    }

    public function testGetScopesSuccess(): void
    {
        $provider = new Provider();
        $provider->id = 1;
        $provider->name = 'Test Provider';
        $provider->source = 'unittest';
    
        $scope = new Scope();
        $scope->id = 1;
        $scope->provider = $provider;
        $scope->preferences = [
            'client' => [
                'telephoneActivated' => '1',
                'telephoneRequired' => '0',
                'customTextfieldActivated' => '1',
                'customTextfieldRequired' => '0',
                'customTextfieldLabel' => 'Test Label',
                'captchaActivatedRequired' => '1'
            ]
        ];
    
        $source = new Source();
        $source->providers = new ProviderList([$provider]);
        $source->scopes = new ScopeList([$scope]);
    
        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
            ->method('readGetResult')
            ->willReturn($result);
    
        $result = ZmsApiFacadeService::getScopes();
        $this->assertInstanceOf(ThinnedScopeList::class, $result);
        $this->assertCount(1, $result->getScopes());
        
        $thinnedScope = $result->getScopes()[0];
        $this->assertEquals(1, $thinnedScope->id);
        $this->assertTrue($thinnedScope->telephoneActivated);
        $this->assertFalse($thinnedScope->telephoneRequired);
        $this->assertTrue($thinnedScope->customTextfieldActivated);
        $this->assertFalse($thinnedScope->customTextfieldRequired);
        $this->assertEquals('Test Label', $thinnedScope->customTextfieldLabel);
        $this->assertTrue($thinnedScope->captchaActivatedRequired);
    }
    
    public function testGetScopesEmpty(): void
    {
        $source = new Source();
        $source->providers = new ProviderList();
        $source->scopes = new ScopeList();
    
        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
            ->method('readGetResult')
            ->willReturn($result);
    
        $result = ZmsApiFacadeService::getScopes();
        $this->assertInstanceOf(ThinnedScopeList::class, $result);
        $this->assertEmpty($result->getScopes());
    }
    
    public function testGetServicesSuccess(): void
    {
        $request = new Request();
        $request->id = 1;
        $request->name = 'Test Service';
        $request->data = [
            'additionalData' => [
                'maxQuantity' => 1
            ]
        ];
    
        $source = new Source();
        $source->requests = new RequestList([$request]);
    
        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
            ->method('readGetResult')
            ->willReturn($result);
    
        $result = ZmsApiFacadeService::getServices();
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertCount(1, $result->services);
        
        $service = $result->services[0];
        $this->assertEquals(1, $service->id);
        $this->assertEquals('Test Service', $service->name);
        $this->assertEquals(1, $service->maxQuantity);
    }
    
    public function testGetServicesEmpty(): void
    {
        $source = new Source();
        $source->requests = new RequestList();
    
        // Mock cache miss
        $this->cacheMock->method('get')->willReturn(null);
        
        // Mock HTTP response
        $result = $this->createMock(Result::class);
        $result->method('getEntity')->willReturn($source);
        
        $this->httpMock->expects($this->any())
            ->method('readGetResult')
            ->willReturn($result);
    
        $result = ZmsApiFacadeService::getServices();
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertEmpty($result->services);
    }
}