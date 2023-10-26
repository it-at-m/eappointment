<?php

namespace BO\Zmscalldisplay\Tests;

class QueueTest extends Base
{
    protected $classname = "Queue";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'parameters' => [
                        'statusList' => ['called', 'pickup']
                    ],
                    'response' => $this->readFixture("GET_queue.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => ["keepLessData" => ["status"]],
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '141',
                'clusterlist' => '110'
            ],
            'tableLayout' => [
                "multiColumns"  => 1,
                "maxResults"    => 5,
                "head" => [
                    "left"  =>  "Nummer",
                    "right" =>  "Platz"
                ]
            ]
        ], [ ]);
        $this->assertStringContainsString('Ausgabe', (string) $response->getBody());
        $this->assertStringContainsString('31316', (string) $response->getBody());
    }
}
