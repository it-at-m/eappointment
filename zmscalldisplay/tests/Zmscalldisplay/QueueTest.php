<?php

namespace BO\Zmscalldisplay\Tests;

class QueueTest extends Base
{
    protected $classname = "Queue";

    protected $arguments = [ ];

    protected $parameters = [ ];

    #[\Override]
    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/',
                    'response' => $this->readFixture("GET_calldisplay.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'parameters' => [
                        'statusList' => ['called']
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

    /**
     * ZMS-3213 / ZMSALT-3250: newest callTime is rendered first (top-left).
     */
    public function testRenderingOrdersByCallTimeDescending()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/',
                    'response' => $this->readFixture("GET_calldisplay.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'parameters' => [
                        'statusList' => ['called']
                    ],
                    'response' => $this->readFixture("GET_queue_callTime_order.json")
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
        $body = (string) $response->getBody();
        $posNewest = strpos($body, 'data-appointment="10003"');
        $posMiddle = strpos($body, 'data-appointment="10002"');
        $posOldest = strpos($body, 'data-appointment="10001"');
        $this->assertNotFalse($posNewest);
        $this->assertNotFalse($posMiddle);
        $this->assertNotFalse($posOldest);
        $this->assertLessThan($posMiddle, $posNewest);
        $this->assertLessThan($posOldest, $posMiddle);
    }
}
