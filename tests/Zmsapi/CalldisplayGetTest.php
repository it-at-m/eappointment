<?php

namespace BO\Zmsapi\Tests;

class CalldisplayGetTest extends Base
{
    protected $classname = "CalldisplayGet";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "scopes": [
                    {
                      "id": 143
                    }
                ],
                "clusters": [
                    {
                      "id": 109
                    }
                ],
                "organisation": {
                    "id": 123
                },
                "contact": {
                    "name": "Bürgeramt"
                }
            }'
        ], []);
        $this->assertStringContainsString('calldisplay.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testClusterNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "clusters": [
                    {
                      "id": 999
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "scopes": [
                    {
                      "id": 999
                    }
                ]
            }'
        ], []);
    }

    public function testNotFoundClusterOrScopeLists()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Calldisplay\ScopeAndClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "organisation": {
                    "id": 123
                },
                "contact": {
                    "name": "Bürgeramt"
                }
            }'
        ], []);
    }
}
