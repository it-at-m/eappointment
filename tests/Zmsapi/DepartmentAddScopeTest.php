<?php

namespace BO\Zmsapi\Tests;

class DepartmentAddScopeTest extends Base
{
    protected $classname = "DepartmentAddScope";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $response = $this->render(['id' => 72], [
            '__body' => '{
                  "shortName": "Test Scope",
                  "provider": {
                      "id": 122217
                  }
              }'
        ], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"shortName":"Test Scope"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidScope()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }
}
