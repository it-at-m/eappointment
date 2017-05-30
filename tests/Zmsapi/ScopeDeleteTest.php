<?php

namespace BO\Zmsapi\Tests;

class ScopeDeleteTest extends Base
{
    protected $classname = "ScopeDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $response = $this->render(['id' => 615], [], []); //Ordnungsamt Charlottenburg
        $this->assertContains('Ordnungsamt Charlottenburg-Wilmersdorf ', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
