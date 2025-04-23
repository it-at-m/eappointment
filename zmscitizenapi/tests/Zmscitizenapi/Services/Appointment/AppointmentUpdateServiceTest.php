<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use PHPUnit\Framework\TestCase;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Appointment\AppointmentUpdateService;

class AppointmentUpdateServiceTest extends TestCase
{
    private AppointmentUpdateService $service;
    private \ReflectionClass $reflector;
    private static $originalFacade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentUpdateService();
        $this->reflector = new \ReflectionClass(AppointmentUpdateService::class);
        
        $facadeReflection = new \ReflectionClass(ZmsApiFacadeService::class);
        $staticProperties = $facadeReflection->getStaticProperties();
        self::$originalFacade = reset($staticProperties);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $facadeReflection = new \ReflectionClass(ZmsApiFacadeService::class);
        $staticProperty = $facadeReflection->getProperties(\ReflectionProperty::IS_STATIC)[0];
        $staticProperty->setAccessible(true);
        $staticProperty->setValue(null, self::$originalFacade);
    }

    private function invokePrivateMethod(string $methodName, array $params = []): mixed
    {
        $method = $this->reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->service, $params);
    }

    public function testExtractClientDataWithValidInput(): void
    {
        $body = [
            'processId' => '12345',
            'authKey' => 'abc123',
            'familyName' => 'Doe',
            'email' => 'john@example.com',
            'telephone' => '1234567890',
            'customTextfield' => 'Custom Info'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('abc123', $result->authKey);
        $this->assertEquals('Doe', $result->familyName);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('1234567890', $result->telephone);
        $this->assertEquals('Custom Info', $result->customTextfield);
    }

    public function testExtractClientDataWithInvalidProcessId(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => 'abc123'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertNull($result->processId);
        $this->assertEquals('abc123', $result->authKey);
        $this->assertNull($result->familyName);
        $this->assertNull($result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
    }

    public function testExtractClientDataWithEmptyAuthKey(): void
    {
        $body = [
            'processId' => '12345',
            'authKey' => ''
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertNull($result->authKey);
    }

    public function testValidateClientDataWithValidData(): void
    {
        $processJson = [
            '$schema' => 'https://localhost/terminvereinbarung/api/2/',
            'meta' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/metaresult.json',
                'error' => false,
                'generated' => '2019-02-08T14:45:15+01:00',
                'server' => 'Zmsapi'
            ],
            'data' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/process.json',
                'id' => 101002,
                'authKey' => 'fb43',
                'status' => 'confirmed',
                'appointments' => [
                    [
                        'date' => 1724907600,
                        'scope' => [
                            'id' => '1063424'
                        ],
                        'slotCount' => '1'
                    ]
                ],
                'scope' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/scope.json',
                    'id' => '64',
                    'source' => 'dldb',
                    'provider' => [
                        'id' => '64',
                        'source' => 'dldb',
                        'name' => 'Test Provider'
                    ],
                    'preferences' => [
                        'client' => [
                            'customTextfieldActivated' => true,
                            'customTextfieldRequired' => true,
                            'customTextfieldLabel' => 'Test Label',
                            'telephoneActivated' => true,
                            'telephoneRequired' => true
                        ]
                    ]
                ],
                'clients' => [
                    [
                        'familyName' => 'Doe',
                        'email' => 'john@example.com',
                        'emailSendCount' => '0',
                        'notificationsSendCount' => '0',
                        'surveyAccepted' => 1,
                        'telephone' => '1234567890'
                    ]
                ],
                'requests' => [
                    [
                        'id' => '1063424',
                        'name' => 'Test Service',
                        'source' => 'dldb'
                    ]
                ]
            ]
        ];
        $sourceJson = [
            '$schema' => 'https://localhost/terminvereinbarung/api/2/',
            'meta' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/metaresult.json',
                'error' => false,
                'generated' => '2019-02-08T14:45:15+01:00',
                'server' => 'Zmsapi'
            ],
            'data' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/source.json',
                'source' => 'unittest',
                'providers' => [
                    [
                        'id' => '9999998',
                        'link' => 'https://www.berlinonline.de',
                        'name' => 'Unittest Source Dienstleister',
                        'source' => 'unittest',
                        'data' => [
                            'geo' => [
                                'lat' => '48.12750898398659',
                                'lon' => '11.604317899956524'
                            ],
                            'showAlternativeLocations' => false
                        ],
                        'contact' => [
                            'city' => 'Berlin',
                            'country' => 'Germany',
                            'name' => 'Unittest Source Dienstleister',
                            'postalCode' => '10178',
                            'region' => 'Berlin',
                            'street' => 'Alte Jakobstraße',
                            'streetNumber' => '105'
                        ]
                    ]
                ],
                'requests' => [
                    [
                        'id' => '1',
                        'name' => 'Unittest Source Dienstleistung',
                        'source' => 'unittest'
                    ]
                ],
                'scopes' => [
                    [
                        'id' => '1',
                        'provider' => [
                            'id' => '9999998',
                            'source' => 'unittest'
                        ],
                        'shortName' => 'Scope 1',
                        'preferences' => [
                            'client' => [
                                'emailFrom' => 'no-reply@muenchen.de',
                                'emailRequired' => '1',
                                'telephoneActivated' => '1',
                                'telephoneRequired' => '1',
                                'customTextfieldActivated' => '1',
                                'customTextfieldRequired' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    
        $processResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $processResponse->method('getBody')
            ->willReturn(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($processJson)));
        $processResponse->method('getStatusCode')
            ->willReturn(200);
    
        $sourceResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $sourceResponse->method('getBody')
            ->willReturn(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($sourceJson)));
        $sourceResponse->method('getStatusCode')
            ->willReturn(200);

        $mockHttpClient = $this->createMock(\BO\Zmsclient\Http::class);
        $mockHttpClient->expects($this->exactly(2))
            ->method('readGetResult')
            ->willReturnCallback(function($url, $params) use ($processResponse, $sourceResponse) {
                if (strpos($url, '/process/101002/fb43/') !== false) {
                    return new \BO\Zmsclient\Result($processResponse);
                }
                if (strpos($url, '/source/unittest/') !== false) {
                    return new \BO\Zmsclient\Result($sourceResponse);
                }
                throw new \RuntimeException("Unexpected URL: " . $url);
            });
    
        $originalHttp = \App::$http;
    
        try {
            \App::$http = $mockHttpClient;
    
            $data = (object)[
                'processId' => 101002,
                'authKey' => 'fb43',
                'familyName' => 'Doe',
                'email' => 'john@example.com',
                'telephone' => '1234567890',
                'customTextfield' => 'Custom Info'
            ];
    
            $result = $this->invokePrivateMethod('validateClientData', [$data]);
            $this->assertEquals(['errors' => []], $result);
        } finally {
            \App::$http = $originalHttp;
        }
    }

    public function testValidateClientDataWithInvalidData(): void
    {
        $data = (object)[
            'processId' => null,
            'authKey' => null,
            'familyName' => null,
            'email' => null,
            'telephone' => null,
            'customTextfield' => null
        ];

        $result = $this->invokePrivateMethod('validateClientData', [$data]);

        $this->assertArrayHasKey('errors', $result);
    }

    public function testUpdateProcessWithClientData(): void
    {
        $process = $this->createMock(ThinnedProcess::class);
        $process->familyName = 'Old Name';
        $process->email = 'old@example.com';
        
        $data = (object)[
            'familyName' => 'New Name',
            'email' => 'new@example.com',
            'telephone' => null,
            'customTextfield' => null
        ];

        $result = $this->invokePrivateMethod('updateProcessWithClientData', [$process, $data]);

        $this->assertEquals('New Name', $result->familyName);
        $this->assertEquals('new@example.com', $result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
    }

    public function testProcessUpdateWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processUpdate($body);

        $this->assertArrayHasKey('errors', $result);
    }
}