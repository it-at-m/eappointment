<?php

namespace BO\Zmsapi\Tests;

class ProcessFreeTest extends Base
{
    protected $classname = "ProcessFree";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "firstDay": {
                    "year": 2016,
                    "month": 5,
                    "day": 27
                },
                "requests": [
                    {
                        "id": "120703",
                        "name": "Personalausweis beantragen",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122217
                    }
                ]
            }'
        ], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testFreeProcessListEmpty()
    {
        $response = $this->render([], [
            '__body' => '{
                "requests": [
                    {
                      "id": "120703",
                      "name": "Personalausweis beantragen",
                      "source": "dldb"
                    }
                ],
                "providers": [
                    {
                      "id": 122217
                    }
                ]
            }'
        ], []);
        $this->assertContains('"data":{}', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
