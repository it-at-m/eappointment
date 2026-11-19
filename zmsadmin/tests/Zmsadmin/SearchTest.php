<?php

namespace BO\Zmsadmin\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

class SearchTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'query' => 'Test%20BO'
    ];

    protected $classname = "Search";

    #[\Override]
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
        $this->assertStringContainsString('<input type="text" id="search-query" name="query" value="Test%20BO"', (string)$response->getBody());
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
        $this->assertStringNotContainsString('navigation-primary', (string) $response->getBody());
    }

    public function testAuditViewerKeepsHiddenNavigationAfterSearch()
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
        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('navigation-primary', $body);
        $this->assertStringNotContainsString('Arbeitsansichten', $body);
        $this->assertStringNotContainsString('Standort auswählen', $body);
        $this->assertStringContainsString('name="hideNavigation" value="1"', $body);
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
        $this->assertStringContainsString('data-processList-count="0"', (string)$response->getBody());
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

    public function testSearchWithoutResultsShowsZeroCount()
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
                        'query' => 'Niemand',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_processList_empty.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => 'Niemand'], []);
        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('Treffern angezeigt', $body);
        $this->assertStringContainsString('data-processList-count="0"', $body);
    }

    public function testRendersActiveAndHistoryAppointmentStatus()
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
                        'query' => 'status-test',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_active_history.json")
                ]
            ]
        );

        $response = $this->render(
            $this->arguments,
            ['query' => 'status-test'],
            []
        );

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringNotContainsString('id="search-status"', $body);
        $this->assertStringContainsString('Terminstatus', $body);

        $this->assertStringContainsString('Status: Geplant', $body);
        $this->assertStringContainsString('Status: Abgeschlossen', $body);
        $this->assertStringContainsString('Status: Nicht erschienen', $body);
        $this->assertStringContainsString('Status: Abgesagt durch Kunden', $body);
        $this->assertStringContainsString('Status: Abgesagt durch Sachbearbeitung', $body);

        $this->assertStringContainsString('selectedprocess=100504', $body);
        $this->assertStringContainsString('selectedprocess=101002', $body);

        $this->assertStringNotContainsString('selectedprocess=201001', $body);
        $this->assertStringNotContainsString('selectedprocess=201002', $body);
    }

    public function testRendersBookingAndCallTimes()
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
                        'query' => 'time-test',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_active_history.json")
                ]
            ]
        );

        $response = $this->render(
            $this->arguments,
            ['query' => 'time-test'],
            []
        );

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertSame(6, substr_count($body, 'Buchung:'));

        $this->assertSame(1, substr_count($body, 'Terminaufruf:'));

        $this->assertSame(2, substr_count($body, 'Stornierung:'));

        $this->assertMatchesRegularExpression(
            '/Buchung: \d{2}\.\d{2}\.\d{4}, \d{2}:\d{2}&nbsp;Uhr/',
            $body
        );

        $this->assertMatchesRegularExpression(
            '/Terminaufruf: \d{2}\.\d{2}\.\d{4}, \d{2}:\d{2}&nbsp;Uhr/',
            $body
        );
    }

    public function testUserWithLogsPermissionSeesUserActionFilter()
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
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString(
            'id="search-user-yes"',
            $body
        );
    }

    public function testUserWithoutLogsPermissionDoesNotSeeUserActionFilter()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ]
            ]
        );

        $response = $this->render($this->arguments, [], []);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringNotContainsString(
            'id="search-user-yes"',
            $body
        );
    }

    public static function paginationProvider(): array
    {
        return [
            'first page' => [1, [1, 2, 12]],
            'second page' => [2, [1, 2, 3, 12]],
            'middle page' => [6, [1, 5, 6, 7, 12]],
            'gap of one page shows the page' => [4, [1, 2, 3, 4, 5, 12]],
            'last page' => [12, [1, 11, 12]],
            'very large page number' => [100000000, [1, 99999999, 100000000]],
        ];
    }

    #[DataProvider('paginationProvider')]
    public function testRendersPagination(int $page, array $expectedPages)
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
                        'query' => 'Muster',
                        'page' => $page,
                        'limit' => 10,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_paged.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => 'Muster', 'page' => $page, 'perPage' => 10], []);
        $body = (string) $response->getBody();

        $pagination = substr($body, strpos($body, '<div class="pagination">'));
        preg_match_all('/<(?:a|span)[^>]*>(\d+)</', $pagination, $matches);
        $this->assertEquals($expectedPages, array_map('intval', $matches[1]));
        $this->assertStringContainsString('aria-current="page">' . $page . '<', $pagination);
        $this->assertStringContainsString('&hellip;', $pagination);
        $this->assertEquals($page > 1, str_contains($body, 'aria-label="Vorherige Seite"'));
        $this->assertEquals($page < 12, str_contains($body, 'aria-label="Nächste Seite"'));
    }

    public function testHidesPaginationForSinglePage()
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
                        'query' => 'status-test',
                        'page' => 1,
                        'limit' => 100,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_active_history.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['query' => 'status-test'], []);
        $this->assertStringNotContainsString('class="pagination"', (string) $response->getBody());
    }

    public function testClampsResultsPerPageToOne()
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
                        'query' => 'status-test',
                        'page' => 1,
                        'limit' => 1,
                        'scopeIds' => '380,1,141',
                    ],
                    'response' => $this->readFixture("GET_searchresult_active_history.json")
                ],
            ]
        );

        $response = $this->render($this->arguments, ['query' => 'status-test', 'perPage' => 0], []);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
