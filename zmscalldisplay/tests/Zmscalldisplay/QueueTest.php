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

    public function testSkipsRefreshWhenQueueRequestFails()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'parameters' => [
                        'statusList' => ['called']
                    ],
                    'exception' => new \BO\Zmsclient\Exception\ApiFailed(
                        'API-Call failed, JSON parsing with error: malformed JSON'
                    )
                ]
            ]
        );
        $response = $this->render([], [
            'collections' => [
                'scopelist' => '141'
            ]
        ], []);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testSkipsRefreshWhenQueueRequestTimesOut()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'parameters' => [
                        'statusList' => ['called']
                    ],
                    'exception' => new \BO\Zmsclient\Psr7\RequestException(
                        'Operation timed out after 9002 milliseconds with 0 bytes received'
                    )
                ]
            ]
        );
        $response = $this->render([], [
            'collections' => [
                'scopelist' => '141'
            ]
        ], []);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testSkipsRefreshWhenScopeRequestTimesOut()
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
                    'exception' => new \BO\Zmsclient\Psr7\RequestException(
                        'Operation timed out after 9002 milliseconds with 0 bytes received'
                    )
                ]
            ]
        );
        $response = $this->render([], [
            'collections' => [
                'scopelist' => '141',
                'clusterlist' => '110'
            ]
        ], []);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }
}
