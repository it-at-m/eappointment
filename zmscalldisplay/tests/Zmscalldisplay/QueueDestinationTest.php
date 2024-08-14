<?php

namespace BO\Zmscalldisplay\Tests;

class QueueDestinationTest extends Base
{
    protected $classname = "Queue";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readPostResult',
                'url' => '/calldisplay/',
                'response' => $this->readFixture("GET_calldisplay.json")
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/',
                'parameters' => [
                    'keepLessData' => [
                        'status'
                    ]
                ],
                'response' => $this->readFixture("GET_scope_141.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/calldisplay/queue/',
                'parameters' => [
                    'statusList' => ['called', 'pickup']
                ],
                'response' => $this->readFixture("GET_queue_multipleDestination.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '141,140'
            ],
            'tableLayout' => [
                "multiColumns"  => 2,
                "maxResults"    => 8,
                "head" => [
                    "left"  =>  "Nummer",
                    "right" =>  "Platz"
                ]
            ]
        ], [ ]);
        $this->assertStringContainsString('31316', (string) $response->getBody());
        $this->assertStringContainsString('52230', (string) $response->getBody());
        $this->assertStringContainsString('data="10"', (string) $response->getBody());
        $this->assertStringContainsString('data="12"', (string) $response->getBody());
    }
}
