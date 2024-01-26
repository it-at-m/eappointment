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
                    "displayName": "B\u00fcrgeramt Heerstra\u00dfe",
                    "$ref": "/provider/122217/"
                },
                "status": {
                    "queue": {
                        "ghostWorkstationCount": 4
                    }
                }
            }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"ghostWorkstationCount":4', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
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
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => $this->readFixture('GetScope_lessData.json')
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": "999",
                "shortName": "",
                "provider": {
                    "id": "122217",
                    "displayName": "B\u00fcrgeramt Heerstra\u00dfe",
                    "$ref": "/provider/122217/"
                }
            }'
        ], []);
    }
}
