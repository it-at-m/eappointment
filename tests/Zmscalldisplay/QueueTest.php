<?php

namespace BO\Zmscalldisplay\Tests;

class QueueTest extends Base
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
                'function' => 'readPostResult',
                'url' => '/calldisplay/queue/',
                'response' => $this->readFixture("GET_queue.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '141',#
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
        $this->assertContains('Terminkunde', (string) $response->getBody());
        $this->assertContains('31316', (string) $response->getBody());
    }
}
