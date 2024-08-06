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
                        "source": "dldb",
                        "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
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
                        "source": "dldb",
                        "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
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
                        "source": "dldb",
                        "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
                    }
                ]
            }'
        ], []);

        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
        $this->assertStringNotContainsString('"date":"1464342000"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    // request with 2 slots in scope 148 so 4 slots for 2 requests
    public function testGettingAvailableSlotsFor2Requests()
    {
        var_dump('OVDE');
        \App::$now->modify('2016-05-24 15:00');
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
                        "id": 122304,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        foreach(json_decode((string)$response->getBody(), true)['data'] as $processData) {
            $this->assertEquals(4, $processData['appointments'][0]['slotCount']);
        }
        
        $this->assertEquals(19, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464337800"', (string)$response->getBody());
    }

    public function testGettingAvailableSlotsFor2Requests2Days()
    {
        var_dump('OVDE');
        \App::$now->modify('2016-05-24 15:00');
        $response = $this->render([], [
            '__body' => '{
                "firstDay": {
                    "year": 2016,
                    "month": 5,
                    "day": 27
                },
                "lastDay": {
                    "year": 2016,
                    "month": 5,
                    "day": 28
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
                        "id": 122304,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        foreach(json_decode((string)$response->getBody(), true)['data'] as $processData) {
            $this->assertEquals(4, $processData['appointments'][0]['slotCount']);
        }

        $this->assertEquals(19, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464337800"', (string)$response->getBody());
    }

    // request with 2 and 1 slots in scope 148 so 3 slots for 2 requests
    public function testGettingAvailableSlotsFor3Requests()
    {
        \App::$now->modify('2016-05-24 15:00');
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
                        "id": "120335",
                        "name": "Abmeldung einer Wohnung",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122304,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        foreach(json_decode((string)$response->getBody(), true)['data'] as $processData) {
            $this->assertEquals(3, $processData['appointments'][0]['slotCount']);
        }
        $this->assertEquals(25, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464337800"', (string)$response->getBody());
    }
    
    public function testGettingAvailableSlotsForRequestThatRequires3Slots()
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
                        "id": "120686",
                        "name": "Anmeldung einer Wohnung",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122304,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        foreach(json_decode((string)$response->getBody(), true)['data'] as $processData) {
            $this->assertEquals(3, $processData['appointments'][0]['slotCount']);
        }
        $this->assertEquals(25, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
    }

    public function testGettingAvailableSlotsFor2RequestsThatRequires6Slots()
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
                        "id": "120686",
                        "name": "Anmeldung einer Wohnung",
                        "source": "dldb"
                    },
                    {
                        "id": "120686",
                        "name": "Anmeldung einer Wohnung",
                        "source": "dldb"
                    }
                ],
                "providers": [
                    {
                        "id": 122304,
                        "source": "dldb"
                    }
                ]
            }'
        ], []);

        foreach(json_decode((string)$response->getBody(), true)['data'] as $processData) {
            $this->assertEquals(6, $processData['appointments'][0]['slotCount']);
        }
        $this->assertEquals(11, count(json_decode((string)$response->getBody(), true)['data']));
        $this->assertStringContainsString('"date":"1464340800"', (string)$response->getBody());
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
                      "source": "dldb",
                      "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
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
                        "source": "dldb",
                        "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
    }
}
