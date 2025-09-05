<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class AvailabilityClosureReadTest extends Base
{
    protected $arguments  = [];
    protected $parameters = [];
    protected $classname  = "AvailabilityClosureRead";

    private function auth()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('useraccount');
    }

    /**
     * Override des ggf. geerbten testRendering(),
     * damit Rechte gesetzt sind und wir eine valide Anfrage machen.
     */
    public function testRendering()
    {
        $this->auth();

        // valide Anfrage über render()-Parameter, NICHT via $_GET
        $response = $this->render(
            [], // route-args
            [   // query/body params
                'scopeIds'  => '58,59',
                'dateFrom'  => '2025-09-01',
                'dateUntil' => '2025-09-10',
            ],
            []  // headers
        );

        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testValidRequestReturnsItems()
    {
        $this->auth();

        $response = $this->render(
            [],
            [
                'scopeIds'  => '58,59',
                'dateFrom'  => '2025-09-01',
                'dateUntil' => '2025-09-10',
            ],
            []
        );

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
            if ((int)$it['scopeId'] === 59 && $it['date'] === '2025-09-04') $have59 = true;
        }
        $this->assertTrue($have58, 'expected closure for scope 58 on 2025-09-03');
        $this->assertTrue($have59, 'expected closure for scope 59 on 2025-09-04');
    }

    public function testFromAfterUntilReturns400()
    {
        $this->auth();

        $response = $this->render(
            [],
            [
                'scopeIds'  => '58',
                'dateFrom'  => '2025-09-10',
                'dateUntil' => '2025-09-01',
            ],
            []
        );

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertTrue($body['error']);
    }

    public function testInvalidScopeIdsFormatReturns400()
    {
        $this->auth();

        $response = $this->render(
            [],
            [
                'scopeIds'  => '58,foo,59', // invalid
                'dateFrom'  => '2025-09-01',
                'dateUntil' => '2025-09-10',
            ],
            []
        );

        $this->assertEquals(400, $response->getStatusCode());
    }
}
