<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Combinable;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmsentities\Request;
use BO\Zmsentities\RequestRelation;
use PHPUnit\Framework\TestCase;
use BO\Zmscitizenapi\Services\Core\MapperService;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\ThinnedProvider;
use BO\Zmscitizenapi\Models\ThinnedContact;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Contact;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;

class MapperServiceTest extends TestCase
{
    private $originalFacade;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';
        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testMapScopeForProvider()
    {
        $provider = new ThinnedProvider(id: 1, name: "Test Provider");
        $scope = new ThinnedScope(id: 1, provider: $provider);
        $scopes = new ThinnedScopeList([$scope]);

        $result = MapperService::mapScopeForProvider(1, $scopes);
        $this->assertSame($scope, $result);

        $result = MapperService::mapScopeForProvider(2, $scopes);
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertNull($result->provider);

        $result = MapperService::mapScopeForProvider(1, null);
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertNull($result->provider);
    }

    public function testMapCombinable()
    {
        // Test with valid combinations
        $combinations = [
            '1' => [1, 2, 3],
            '2' => [4, 5, 6]
        ];
        $result = MapperService::mapCombinable($combinations);
        $this->assertNotNull($result);
        $this->assertEquals($combinations, $result->getCombinations());

        $result = MapperService::mapCombinable([]);
        $this->assertNull($result);
    }

    public function testContactToThinnedContact()
    {
        $contactArray = [
            'city' => 'Munich',
            'country' => 'Germany',
            'name' => 'Test Contact',
            'postalCode' => '80333',
            'region' => 'Bavaria',
            'street' => 'Test Street',
            'streetNumber' => '123'
        ];

        $result = MapperService::contactToThinnedContact($contactArray);
        $this->assertInstanceOf(ThinnedContact::class, $result);
        $this->assertEquals('Munich', $result->city);
        $this->assertEquals('Germany', $result->country);
        $this->assertEquals('Test Contact', $result->name);

        $contact = new Contact();
        $contact->city = 'Berlin';
        $contact->country = 'Germany';
        $contact->name = 'Test Contact 2';

        $result = MapperService::contactToThinnedContact($contact);
        $this->assertInstanceOf(ThinnedContact::class, $result);
        $this->assertEquals('Berlin', $result->city);
        $this->assertEquals('Germany', $result->country);
        $this->assertEquals('Test Contact 2', $result->name);

        $result = MapperService::contactToThinnedContact(new \stdClass());
        $this->assertInstanceOf(ThinnedContact::class, $result);
        $this->assertEquals('', $result->city);
        $this->assertEquals('', $result->country);
        $this->assertEquals('', $result->name);
    }

    public function testProviderToThinnedProvider()
    {
        $provider = new Provider();
        $provider->id = 1;
        $provider->name = 'Test Provider';
        $provider->source = 'unittest';
        $provider->data = [
            'geo' => [
                'lat' => 48.137154,
                'lon' => 11.576124
            ]
        ];
        $provider->contact = new Contact();
        $provider->contact->city = 'Munich';

        $result = MapperService::providerToThinnedProvider($provider);
        $this->assertInstanceOf(ThinnedProvider::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Provider', $result->name);
        $this->assertEquals('unittest', $result->source);
        $this->assertEquals(48.137154, $result->lat);
        $this->assertEquals(11.576124, $result->lon);
        $this->assertInstanceOf(ThinnedContact::class, $result->contact);


    }

    public function testMinimalProviderToThinnedProvider()
    {
        $provider = new Provider();
        $result = MapperService::providerToThinnedProvider($provider);
        $this->assertInstanceOf(ThinnedProvider::class, $result);
        $this->assertNull($result->id);
        $this->assertNull($result->name);
        $this->assertNull($result->source);
        $this->assertNull($result->lat);
        $this->assertNull($result->lon);
        $this->assertNull($result->contact);
    }

    public function testProcessToThinnedProcess()
    {
        // Test with full process data
        $process = new Process();
        $process->id = 1;
        $process->authKey = 'test-key';
        $process->appointments = [
            (object) ['date' => '1724907600']
        ];
        $process->clients = [
            (object) [
                'familyName' => 'Doe',
                'email' => 'john@example.com',
                'telephone' => '123456789'
            ]
        ];
        $process->customTextfield = 'Custom Text';
        $process->scope = new Scope();
        $process->scope->contact = new Contact();
        $process->scope->contact->name = 'Test Office';
        $process->scope->provider = new Provider();
        $process->scope->provider->id = 100;
        $process->queue = (object) ['status' => 'confirmed'];

        $result = MapperService::processToThinnedProcess($process);
        $this->assertInstanceOf(ThinnedProcess::class, $result);
        $this->assertEquals(1, $result->processId);
        $this->assertEquals('1724907600', $result->timestamp);
        $this->assertEquals('test-key', $result->authKey);
        $this->assertEquals('Doe', $result->familyName);
        $this->assertEquals('Custom Text', $result->customTextfield);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('123456789', $result->telephone);
        $this->assertEquals('Test Office', $result->officeName);
        $this->assertEquals(100, $result->officeId);
        $this->assertEquals('confirmed', $result->status);
    }

    public function testMinimalProcessToThinnedProcess()
    {
        $expectedResponse = [
            'processId' => 0,
            'timestamp' => null,
            'authKey' => '',
            'familyName' => null,
            'customTextfield' => '',
            'email' => null,
            'telephone' => null,
            'officeName' => null,
            'officeId' => 0,
            'scope' => [
                'id' => 0,
                'provider' => new ThinnedProvider(),
                'shortName' => null,
                'telephoneActivated' => null,
                'telephoneRequired' => null,
                'customTextfieldActivated' => null,
                'customTextfieldRequired' => null,
                'customTextfieldLabel' => null,
                'captchaActivatedRequired' => null,
                'displayInfo' => null
            ],
            'subRequestCounts' => [],
            'serviceId' => 0,
            'serviceCount' => 0,
            'status' => null
        ];

        $process = new Process();
        
        $result = MapperService::processToThinnedProcess($process);

        $this->assertEquals($expectedResponse['processId'], $result->processId);
        $this->assertEquals($expectedResponse['timestamp'], $result->timestamp);
        $this->assertEquals($expectedResponse['authKey'], $result->authKey);
        $this->assertEquals($expectedResponse['familyName'], $result->familyName);
        $this->assertEquals($expectedResponse['customTextfield'], $result->customTextfield);
        $this->assertEquals($expectedResponse['email'], $result->email);
        $this->assertEquals($expectedResponse['telephone'], $result->telephone);
        $this->assertEquals($expectedResponse['officeName'], $result->officeName);
        $this->assertEquals($expectedResponse['officeId'], $result->officeId);
    
        $scope = $expectedResponse['scope'];
        $this->assertEquals($scope['id'], $result->scope->id);
        $this->assertInstanceOf(ThinnedProvider::class, $result->scope->provider);
        $this->assertEquals($scope['shortName'], $result->scope->shortName);
        $this->assertEquals($scope['telephoneActivated'], $result->scope->telephoneActivated);
        $this->assertEquals($scope['telephoneRequired'], $result->scope->telephoneRequired);
        $this->assertEquals($scope['customTextfieldActivated'], $result->scope->customTextfieldActivated);
        $this->assertEquals($scope['customTextfieldRequired'], $result->scope->customTextfieldRequired);
        $this->assertEquals($scope['customTextfieldLabel'], $result->scope->customTextfieldLabel);
        $this->assertEquals($scope['captchaActivatedRequired'], $result->scope->captchaActivatedRequired);
        $this->assertEquals($scope['displayInfo'], $result->scope->displayInfo);
        $this->assertEquals($expectedResponse['subRequestCounts'], $result->subRequestCounts);
        $this->assertEquals($expectedResponse['serviceId'], $result->serviceId);
        $this->assertEquals($expectedResponse['serviceCount'], $result->serviceCount);
        $this->assertEquals($expectedResponse['status'], $result->status);
    }
    

    public function testThinnedProcessToProcess()
    {
        $thinnedProcess = new ThinnedProcess(
            processId: 1,
            timestamp: '1724907600',
            authKey: 'test-key',
            familyName: 'Doe',
            customTextfield: 'Custom Text',
            email: 'john@example.com',
            telephone: '123456789',
            officeName: 'Test Office',
            officeId: 100,
            status: 'confirmed',
            serviceId: 200,
            serviceCount: 2
        );

        $result = MapperService::thinnedProcessToProcess($thinnedProcess);
        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test-key', $result->authKey);
        $this->assertEquals('Doe', $result->clients[0]->familyName);
        $this->assertEquals('john@example.com', $result->clients[0]->email);
        $this->assertEquals('123456789', $result->clients[0]->telephone);
        $this->assertEquals('Custom Text', $result->customTextfield);
        $this->assertEquals('1724907600', $result->appointments[0]->date);
        $this->assertEquals('Test Office', $result->scope->contact->name);
        $this->assertEquals(100, $result->scope->provider->id);
        $this->assertEquals('confirmed', $result->queue->status);
        $this->assertCount(2, $result->requests);
        $this->assertEquals(200, $result->requests[0]->id);

    }

    public function testMinimalThinnedProcessToProcess()
    {
        $expectedResponse = [
            '$schema' => 'https://schema.berlin.de/queuemanagement/process.json',
            'amendment' => '',
            'customTextfield' => '',
            'apiclient' => [
                'shortname' => 'default'
            ],
            'authKey' => '',
            'createIP' => '',
            'createTimestamp' => 1736678411,
            'id' => 0,
            'archiveId' => 0,
            'queue' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/queue.json',
                'arrivalTime' => 0,
                'callCount' => 0,
                'callTime' => 0,
                'number' => 0,
                'waitingTimeEstimate' => 0,
                'waitingTimeOptimistic' => 0,
                'waitingTime' => 0,
                'wayTime' => 0
            ],
            'reminderTimestamp' => 0,
            'scope' => [
                'id' => 0,
                'source' => 'dldb'
            ],
            'status' => 'free',
            'lastChange' => 1736678411
        ];

        $thinnedProcess = new ThinnedProcess();
        $result = MapperService::thinnedProcessToProcess($thinnedProcess);

        $this->assertEquals($expectedResponse['amendment'], $result->amendment);
        $this->assertEquals($expectedResponse['customTextfield'], $result->customTextfield);
        $this->assertEquals($expectedResponse['apiclient']['shortname'], $result->apiclient->shortname);
        $this->assertEquals($expectedResponse['authKey'], $result->authKey);
        $this->assertEquals($expectedResponse['createIP'], $result->createIP);
        $this->assertEquals($expectedResponse['id'], $result->id);
        $this->assertEquals($expectedResponse['archiveId'], $result->archiveId);

        $queue = $expectedResponse['queue'];
        $this->assertEquals($queue['arrivalTime'], $result->queue->arrivalTime);
        $this->assertEquals($queue['callCount'], $result->queue->callCount);
        $this->assertEquals($queue['callTime'], $result->queue->callTime);
        $this->assertEquals($queue['number'], $result->queue->number);
        $this->assertEquals($queue['waitingTimeEstimate'], $result->queue->waitingTimeEstimate);
        $this->assertEquals($queue['waitingTimeOptimistic'], $result->queue->waitingTimeOptimistic);
        $this->assertEquals($queue['waitingTime'], $result->queue->waitingTime);
        $this->assertEquals($queue['wayTime'], $result->queue->wayTime);
        $this->assertEquals($expectedResponse['reminderTimestamp'], $result->reminderTimestamp);
        $scope = $expectedResponse['scope'];
        $this->assertEquals($scope['id'], $result->scope->id);
        $this->assertEquals($scope['source'], $result->scope->source);
        $this->assertEquals($expectedResponse['status'], $result->status);

    }

    public function testMapServicesWithCombinations()
    {
        $request1 = new Request();
        $request1->id = 1;
        $request1->name = 'Service 1';
        $request1->additionalData = ['maxQuantity' => 2];
    
        $request2 = new Request();
        $request2->id = 2;
        $request2->name = 'Service 2';
        $request2->additionalData = ['maxQuantity' => 1];
    
        $requestList = new RequestList([$request1, $request2]);
    
        $provider = new Provider();
        $provider->id = 100;
    
        $relation1 = new RequestRelation();
        $relation1->request = $request1;
        $relation1->provider = $provider;
        $relation1->slots = 5;
    
        $relation2 = new RequestRelation();
        $relation2->request = $request2;
        $relation2->provider = $provider;
        $relation2->slots = 3;
    
        $expectedResponse = [
            "services" => [
                [
                    "id" => 1,
                    "name" => "Service 1",
                    "maxQuantity" => 1,
                    "combinable" => new Combinable(),
                ],
                [
                    "id" => 2,
                    "name" => "Service 2",
                    "maxQuantity" => 1,
                    "combinable" => new Combinable(),
                ]
            ]
        ];
    
        $relationList = new RequestRelationList([$relation1, $relation2]);
        $result = MapperService::mapServicesWithCombinations($requestList, $relationList);
        $resultArray = $result->toArray();
    
        $this->assertEquals($expectedResponse, $resultArray);
    }

    public function testDontReturnNotPublicServices()
    {
        var_dump('here');
        $request1 = new Request();
        $request1->id = 1;
        $request1->name = 'Service 111';
        $request1->additionalData = [
            'maxQuantity' => 22,
            'public' => false
        ];

        $request2 = new Request();
        $request2->id = 2;
        $request2->name = 'Service 2';
        $request2->additionalData = ['maxQuantity' => 1];

        $requestList = new RequestList([$request1, $request2]);

        $provider = new Provider();
        $provider->id = 100;

        $relation1 = new RequestRelation();
        $relation1->request = $request1;
        $relation1->provider = $provider;
        $relation1->slots = 5;

        $relation2 = new RequestRelation();
        $relation2->request = $request2;
        $relation2->provider = $provider;
        $relation2->slots = 3;

        $expectedResponse = [
            "services" => [
                [
                    "id" => 2,
                    "name" => "Service 2",
                    "maxQuantity" => 1,
                    "combinable" => new Combinable(),
                ]
            ]
        ];

        $relationList = new RequestRelationList([$relation1, $relation2]);
        $result = MapperService::mapServicesWithCombinations($requestList, $relationList);
        $resultArray = $result->toArray();

        $this->assertEquals($expectedResponse, $resultArray);
    }

    public function testScopeToThinnedScope()
    {
        // Create provider with contact
        $provider = new Provider();
        $provider->id = 1;
        $provider->name = 'Test Provider';
        $provider->source = 'unittest';

        $contact = new Contact();
        $contact->name = 'Test Contact';
        $contact->city = 'Munich';
        $provider->contact = $contact;

        // Create scope with all required properties
        $scope = new Scope();
        $scope->id = 1;
        $scope->shortName = 'Test Scope';
        $scope->provider = $provider;
        $scope->contact = $contact;
        $scope->data = [
            'telephoneActivated' => true,
            'telephoneRequired' => false,
            'customTextfieldActivated' => true,
            'customTextfieldRequired' => false,
            'customTextfieldLabel' => 'Test Label',
            'captchaActivatedRequired' => false
        ];

        $result = MapperService::scopeToThinnedScope($scope);

        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Scope', $result->shortName);
        $this->assertTrue($result->telephoneActivated);
        $this->assertFalse($result->telephoneRequired);
        $this->assertNotNull($result->provider);
        $this->assertEquals(1, $result->provider->id);
        $this->assertEquals('Test Provider', $result->provider->name);
        $this->assertNotNull($result->provider->contact);
        $this->assertEquals('Test Contact', $result->provider->contact->name);
    }

    public function testMapRelations()
    {
        // Test with valid relations
        $provider1 = new Provider();
        $provider1->id = 100;
        
        $provider2 = new Provider();
        $provider2->id = 200;
        
        $request1 = new Request();
        $request1->id = 1;
        
        $request2 = new Request();
        $request2->id = 2;
        
        $relation1 = new RequestRelation();
        $relation1->provider = $provider1;
        $relation1->request = $request1;
        $relation1->slots = 5;
        
        $relation2 = new RequestRelation();
        $relation2->provider = $provider2;
        $relation2->request = $request2;
        $relation2->slots = 3;
        
        $relationList = new RequestRelationList([$relation1, $relation2]);
        
        $result = MapperService::mapRelations($relationList);
        $this->assertInstanceOf(OfficeServiceRelationList::class, $result);
        
        $relations = $result->toArray()['relations'];
        $this->assertCount(2, $relations);
        
        $this->assertEquals(100, $relations[0]['officeId']);
        $this->assertEquals(1, $relations[0]['serviceId']);
        $this->assertEquals(5, $relations[0]['slots']);
        
        $this->assertEquals(200, $relations[1]['officeId']);
        $this->assertEquals(2, $relations[1]['serviceId']);
        $this->assertEquals(3, $relations[1]['slots']);
    }
    
    public function testMapRelationsWithEmptyList()
    {
        $result = MapperService::mapRelations(new RequestRelationList());
        $this->assertInstanceOf(OfficeServiceRelationList::class, $result);
        $this->assertEmpty($result->toArray()['relations']);
    }
    
    public function testMapServicesWithCombinationsEmpty()
    {
        $result = MapperService::mapServicesWithCombinations(
            new RequestList(),
            new RequestRelationList()
        );
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertEmpty($result->toArray()['services']);
    }
    
    public function testMapServicesWithCombinationsActual()
    {
        $request1 = new Request();
        $request1->id = 1;
        $request1->name = 'Service 1';
        $request1->data = [
            'maxQuantity' => 2,
            'combinable' => [2]
        ];
    
        $request2 = new Request();
        $request2->id = 2;
        $request2->name = 'Service 2';
        $request2->data = [
            'maxQuantity' => 1,
            'combinable' => [1]
        ];
    
        $requestList = new RequestList([$request1, $request2]);
    
        $provider = new Provider();
        $provider->id = 100;
    
        $relation1 = new RequestRelation();
        $relation1->request = $request1;
        $relation1->provider = $provider;
        $relation1->slots = 5;
    
        $relation2 = new RequestRelation();
        $relation2->request = $request2;
        $relation2->provider = $provider;
        $relation2->slots = 3;
    
        $relationList = new RequestRelationList([$relation1, $relation2]);
        $result = MapperService::mapServicesWithCombinations($requestList, $relationList);
        
        $services = $result->toArray()['services'];
        $this->assertCount(2, $services);
        
        $this->assertEquals(1, $services[0]['id']);
        $this->assertEquals('Service 1', $services[0]['name']);
        $this->assertEquals(2, $services[0]['maxQuantity']);
        $this->assertEquals([2 => [100]], $services[0]['combinable']->getCombinations());
        
        $this->assertEquals(2, $services[1]['id']);
        $this->assertEquals('Service 2', $services[1]['name']);
        $this->assertEquals(1, $services[1]['maxQuantity']);
        $this->assertEquals([1 => [100]], $services[1]['combinable']->getCombinations());
    }
    
    public function testScopeToThinnedScopeWithMissingProvider()
    {
        $scope = new Scope();
        $scope->id = 1;
        $scope->shortName = 'Test Scope';
        $scope->data = [
            'telephoneActivated' => true,
            'telephoneRequired' => false
        ];
        
        $scope->provider = null;
        
        $result = MapperService::scopeToThinnedScope($scope);
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Scope', $result->shortName);
        $this->assertTrue($result->telephoneActivated);
        $this->assertFalse($result->telephoneRequired);
        $this->assertNull($result->provider);
    }
    
    public function testScopeToThinnedScopeWithEmptyData()
    {
        $scope = new Scope();
        $scope->id = 1;
        $scope->data = [];
        
        $result = MapperService::scopeToThinnedScope($scope);
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertNull($result->telephoneActivated);
        $this->assertNull($result->telephoneRequired);
        $this->assertNull($result->customTextfieldActivated);
        $this->assertNull($result->customTextfieldRequired);
        $this->assertNull($result->customTextfieldLabel);
        $this->assertNull($result->captchaActivatedRequired);
        $this->assertNull($result->displayInfo);
    }

}