<?php

namespace BO\Zmscalldisplay\Tests;

class IndexCustomizedClusterTest extends Base
{

    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readPostResult',
                'url' => '/calldisplay/',
                'response' => $this->readFixture("GET_calldisplay_cluster_118.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [
            'collections' => [
                'clusterlist' => '118'
            ]
        ], [ ]);
        $this->assertContains('tableLayout.multiColumns="2"', (string) $response->getBody());
        $this->assertContains('tableLayout.maxResults=10', (string) $response->getBody());
    }
}
