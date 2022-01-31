<?php

namespace BO\Zmsapi\Tests;

class ScopeListByRequestTest extends Base
{
    protected $classname = "ScopeListByRequest";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['source' => 'dldb', 'id' => 120335], [], []); //Abmeldung einer Wohnung
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('preferences', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testLessData()
    {
        $response = $this->render(['source' => 'dldb', 'id' => 120335], [], []); //Abmeldung einer Wohnung
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringNotContainsString('preferences', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsdb\Exception\Request\RequestNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'dldb', 'id' => 999], [], []);
    }
}
