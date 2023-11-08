<?php

namespace BO\Zmsadmin\Tests;

class PickupQueueTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "PickupQueue";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/process/pickup/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'selectedScope' => 141,
                        'limit' => 500,
                        'offset' => 0,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getPickup()
                    ],
                    'response' => $this->readFixture("GET_pickupqueue_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/process/pickup/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'selectedScope' => 141,
                        'limit' => 500,
                        'offset' => 500,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getPickup()
                    ],
                    'response' => $this->readFixture("GET_pickupqueue_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'resolveReferences' => 2
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('pickup-table', (string)$response->getBody());
        $this->assertStringContainsString('change-scope', (string)$response->getBody());
        $this->assertStringContainsString('data-limit="500" data-offset="500"', (string)$response->getBody());
        $this->assertStringContainsString('Nächste 100 Abholer anzeigen', (string)$response->getBody());
        $this->assertStringNotContainsString('Vorherige 500 Abholer anzeigen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
