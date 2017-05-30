<?php

namespace BO\Zmsapi\Tests;

class CounterGhostWorkstationTest extends Base
{
    protected $classname = "CounterGhostWorkstation";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->scope['id'] = 146; //ghostworkstation count 3
        $response = $this->render([], [
            '__body' => '{
                "id": "146",
                "shortName": "",
                "provider": {
                    "id": "122217",
                    "$ref": "/provider/122217/"
                }
            }'
        ], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"ghostWorkstationCount":"3",', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{}'
        ], []);
    }

    public function testNoAccess()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNoAccess');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => $this->readFixture('GetScope_lessData.json')
        ], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": "999",
                "shortName": "",
                "provider": {
                    "id": "122217",
                    "$ref": "/provider/122217/"
                }
            }'
        ], []);
    }
}
