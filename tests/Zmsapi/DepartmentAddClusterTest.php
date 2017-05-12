<?php

namespace BO\Zmsapi\Tests;

class DepartmentAddClusterTest extends Base
{
    protected $classname = "DepartmentAddCluster";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 72], [
            '__body' => '{
                "name": "Bürgeramt Test",
                "hint": "",
                "shortNameEnabled": true,
                "callDisplayText": ""
            }'
        ], []);
        $this->assertContains('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidCluster()
    {
        $this->setWorkstation();
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }
}
