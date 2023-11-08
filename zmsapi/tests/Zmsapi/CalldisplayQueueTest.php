<?php

namespace BO\Zmsapi\Tests;

class CalldisplayQueueTest extends Base
{
    protected $classname = "CalldisplayQueue";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "scopes": [
                    {
                      "id": 140
                    },
                    {
                      "id": 141
                    },
                    {
                      "id": 142
                    },
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
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        //$this->dumpProfiler();
    }

    public function testEmpty()
    {
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
