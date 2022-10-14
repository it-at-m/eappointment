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
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"status":"free"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGroupDataGreaterProcessListCount()
    {
        $response = $this->render([], [
            'groupData' => 3,
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
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGroupDataLessProcessListCount()
    {
        $response = $this->render([], [
            'groupData' => 1,
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
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464342000"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testGetting6AvailableSlots()
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
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        $this->assertEquals(6, count(json_decode((string)$response->getBody(), true)['data']));
    }

    public function testGettingAvailableSlotsFor2Requests()
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
                    },
                    {
                        "id": "120703",
                        "name": "Personalausweis beantragen",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        $this->assertEquals(4, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464341400"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464342000"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464342600"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464338400"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464343200"', (string)$response->getBody());
    }

    public function testGettingAvailableSlotsForRequestThatRequires2Slots()
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
                        "id": "1120703",
                        "name": "Personalausweis beantragen",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        $this->assertEquals(4, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464341400"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464342000"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464342600"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464338400"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464343200"', (string)$response->getBody());
    }

    public function testGettingAvailableSlotsFor2RequestsThatRequires2Slots()
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
                        "id": "1120703",
                        "name": "Personalausweis beantragen x2",
                        "source": "dldb"
                    },
                    {
                        "id": "1120703",
                        "name": "Personalausweis beantragen x2",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        $this->assertEquals(2, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464341400"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464342000"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464342600"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464338400"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464343200"', (string)$response->getBody());
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
                      "id": 122217,
                      "source": "dldb"
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('"data":[]', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithRights()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            'slotType' => 'intern',
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
                        "id": 122217,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
    }
}
