<?php

namespace BO\Zmsadmin\Tests;

class OverallCalendarClosureLoadDataTest extends Base
{
    protected $arguments  = [];
    protected $parameters = [];
    protected $classname  = "OverallCalendarClosureLoadData";

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

    public function testMissingParamsReturnsError()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds' => '58,59'
        ];

        $response = $this->render([], [], []);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertTrue($body['error']);
        $this->assertStringContainsString('dateFrom', $body['message']);
        $this->assertStringContainsString('dateUntil', $body['message']);
    }


    public function testValidRequest()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '58,59',
            'dateFrom'  => '2025-09-02',
            'dateUntil' => '2025-09-05',
        ];

        $this->setApiCalls([
            [
                'function'   => 'readGetResult',
                'url'        => '/closure/',
                'parameters' => [
                    'scopeIds'  => '58,59',
                    'dateFrom'  => '2025-09-02',
                    'dateUntil' => '2025-09-05',
                ],
                'response'   => $this->readFixture('GET_Closure_Data.json'),
            ],
        ]);

        $response = $this->render([], [], []);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('items', $body['data']);
        $this->assertIsArray($body['data']['items']);
        $this->assertNotEmpty($body['data']['items']);

        $first = $body['data']['items'][0];
        $this->assertArrayHasKey('scopeId', $first);
        $this->assertArrayHasKey('date', $first);
    }

    public function testResponseStructure()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '58,59',
            'dateFrom'  => '2025-09-02',
            'dateUntil' => '2025-09-05',
        ];

        $this->setApiCalls([
            [
                'function'   => 'readGetResult',
                'url'        => '/closure/',
                'parameters' => [
                    'scopeIds'  => '58,59',
                    'dateFrom'  => '2025-09-02',
                    'dateUntil' => '2025-09-05',
                ],
                'response'   => $this->readFixture('GET_Closure_Data.json'),
            ],
        ]);

        $response = $this->render([], [], []);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('$schema', $body);
        $this->assertArrayHasKey('meta', $body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('items', $body['data']);
    }
}
