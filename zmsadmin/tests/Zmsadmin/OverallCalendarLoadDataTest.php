<?php

namespace BO\Zmsadmin\Tests;

class OverallCalendarLoadDataTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];
    protected $classname = "OverallCalendarLoadData";

    public function testRendering()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [];

        $response = $this->render([], [], []);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertEmpty($body);
    }

    public function testValidRequest()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '141,142,143',
            'dateFrom'  => '2025-01-20',
            'dateUntil' => '2025-01-24'
        ];

        $this->setApiCalls([
            [
                'function'   => 'readGetResult',
                'url'        => '/overallcalendar/',
                'parameters' => [
                    'scopeIds'  => '141,142,143',
                    'dateFrom'  => '2025-01-20',
                    'dateUntil' => '2025-01-24'
                ],
                'response'   => $this->readFixture("GET_OverallCalendar_Data.json")
            ]
        ]);

        $response = $this->render([], [], []);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
    }

    public function testWithUpdateAfter()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'    => '141,142,143',
            'dateFrom'    => '2025-01-20',
            'dateUntil'   => '2025-01-24',
            'updateAfter' => '2025-01-20 10:00:00'
        ];

        $this->setApiCalls([
            [
                'function'   => 'readGetResult',
                'url'        => '/overallcalendar/',
                'parameters' => [
                    'scopeIds'    => '141,142,143',
                    'dateFrom'    => '2025-01-20',
                    'dateUntil'   => '2025-01-24',
                    'updateAfter' => '2025-01-20 10:00:00'
                ],
                'response'   => $this->readFixture("GET_OverallCalendar_Incremental.json")
            ]
        ]);

        $response = $this->render([], [], []);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testResponseStructure()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '141,142',
            'dateFrom'  => '2025-01-20',
            'dateUntil' => '2025-01-22'
        ];

        $this->setApiCalls([
            [
                'function'   => 'readGetResult',
                'url'        => '/overallcalendar/',
                'parameters' => [
                    'scopeIds'  => '141,142',
                    'dateFrom'  => '2025-01-20',
                    'dateUntil' => '2025-01-22'
                ],
                'response'   => $this->readFixture("GET_OverallCalendar_Data.json")
            ]
        ]);

        $response = $this->render([], [], []);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
    }
    public function testMissingDateFromAndUntilReturnsError()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds' => '141,142,143'
        ];

        $response = $this->render([], [], []);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertTrue($body['error']);
        $this->assertStringContainsString('dateFrom', $body['message']);
        $this->assertStringContainsString('dateUntil', $body['message']);
    }
}
