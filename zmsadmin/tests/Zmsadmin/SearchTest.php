<?php

namespace BO\Zmsadmin\Tests;

class SearchTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'query' => 'Test%20BO'
    ];

    protected $classname = "Search";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_audit_viewer.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => 'Test%2520BO',
                        'page' => 1,
                        'perPage' => 100,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null,
                        'scopeIds' => '380,1,141,140,142',
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('<input type="text" name="query" value="Test%20BO"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithProcessId()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_audit_viewer.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => '100005',
                        'page' => 2,
                        'perPage' => 20,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null,
                        'scopeIds' => '380,1,141,140,142',
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'query' => '100005',
            'page' => 2,
            'perPage' => 20
        ], []);
        $this->assertStringContainsString('Log-Ergebnisse', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoAuditAccount()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => 'Test%20BO',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('data-processList-count="5"', (string)$response->getBody());
        $this->assertStringContainsString('data-processListOther-count="0"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAuditAccountWithoutSearchRequestDoesNotLoadProcessOrLogResults()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_audit_viewer.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringNotContainsString('Log-Ergebnisse', (string)$response->getBody());
    }

    public function testRenderingWithHiddenNavigation()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_audit_viewer.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'hideNavigation' => 1
        ], []);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAuditAccountCanSearchLogsByServiceWithoutProcessQuery()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_audit_viewer.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'page' => 1,
                        'limit' => 100,
                        'service' => 'testservice',
                        'scopeIds' => '380,1,141,140,142',
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => '',
                        'page' => 1,
                        'perPage' => 100,
                        'service' => 'testservice',
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null,
                        'scopeIds' => '380,1,141,140,142',
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );

        $response = $this->render($this->arguments, [
            'service' => 'testservice'
        ], []);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Log-Ergebnisse', (string)$response->getBody());
        $this->assertStringContainsString('data-processList-count="5"', (string)$response->getBody());
    }

    public function testCustomerSearchByProviderWithoutTextQuery()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'page' => 1,
                        'limit' => 100,
                        'provider' => 'Bürgerbüro',
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ],
            ]
        );

        $response = $this->render($this->arguments, [
            'provider' => 'Bürgerbüro',
        ], []);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('data-processList-count="5"', (string)$response->getBody());
    }

    public function testQuotedSearchQuery()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => '"Muster"',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => '"Muster"'], []);
        $this->assertStringContainsString('name="query" value="&quot;Muster&quot;"', (string) $response->getBody());
        $this->assertStringNotContainsString('&#34;Muster&#34;', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmptyQueryShowsLatestLogsForSuperuser()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json'),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => '',
                        'page' => 1,
                        'perPage' => 100,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null,
                    ],
                    'response' => $this->readFixture('GET_loglist_out_of_scope.json'),
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => ''], []);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Log-Ergebnisse', $body);
        $this->assertStringContainsString('OutOfScope Log Entry', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testZeroQueryTriggersProcessSearch()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => '0',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => '0'], []);
        $this->assertStringContainsString('name="query" value="0"', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
