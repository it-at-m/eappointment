<?php

namespace BO\Zmsadmin\Tests;

class ClusterDeleteTest extends Base
{
    protected $arguments = [
        'clusterId' => 109,
        'departmentId' => 74
    ];

    protected $parameters = [];

    protected $classname = "ClusterDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/cluster/109/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/owner/?success=cluster_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
