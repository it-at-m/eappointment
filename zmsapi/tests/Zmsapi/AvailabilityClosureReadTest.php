<?php

namespace BO\Zmsapi\Tests;

class AvailabilityClosureReadTest extends Base
{
    protected $arguments  = [];
    protected $parameters = [];
    protected $classname  = "AvailabilityClosureRead";

    public function testValidRequestReturnsItems()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';

        $_GET = [
            'scopeIds'  => '58,59',
            'dateFrom'  => '2025-09-01',
            'dateUntil' => '2025-09-10',
        ];

        $response = $this->render([], [], []);

        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('items', $body['data']);
        $this->assertIsArray($body['data']['items']);

        $have58 = false;
        $have59 = false;
        foreach ($body['data']['items'] as $it) {
            if ((int)$it['scopeId'] === 58 && $it['date'] === '2025-09-03') $have58 = true;
            if ( (int)$it['scopeId'] === 59 && $it['date'] === '2025-09-04') $have59 = true;
        }
        $this->assertTrue($have58, 'expected closure for scope 58 on 2025-09-03');
        $this->assertTrue($have59, 'expected closure for scope 59 on 2025-09-04');
    }

    public function testFromAfterUntilReturns400()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '58',
            'dateFrom'  => '2025-09-10',
            'dateUntil' => '2025-09-01',
        ];

        $response = $this->render([], [], []);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertTrue($body['error']);
    }

    public function testInvalidScopeIdsFormatReturns400()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer validtoken';
        $_GET = [
            'scopeIds'  => '58,foo,59',
            'dateFrom'  => '2025-09-01',
            'dateUntil' => '2025-09-10',
        ];

        $response = $this->render([], [], []);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
