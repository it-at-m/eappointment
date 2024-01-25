<?php

namespace BO\Zmsapi\Tests;

class CalendarTest extends Base
{
    protected $classname = "CalendarGet";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "firstDay": {
                    "year": '. \App::$now->format("Y") .',
                    "month": '. \App::$now->format("n") .',
                    "day": '. \App::$now->format("j") .'
                },
                "lastDay": {
                    "year": '. \App::$now->modify("+1 month")->format("Y") .',
                    "month": '. \App::$now->modify("+1 month")->format("n") .',
                    "day": '. \App::$now->modify("+1 month")->format("t") .'
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
                ],
                "scopes": [
                    {
                      "id": 141
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('calendar.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testInvalidFirstDay()
    {
        $this->expectException('BO\Zmsapi\Exception\Calendar\InvalidFirstDay');
        $this->render([], [
            '__body' => '{
                "requests": [
                    {
                      "id": "120703",
                      "name": "Personalausweis beantragen",
                      "source": "dldb"
                    }
                ],
                "scopes": [{"id":141, "provider":{"id":122217}}],
                "lastDay": {
                    "year": '. \App::$now->modify("+1 month")->format("Y") .',
                    "month": '. \App::$now->modify("+1 month")->format("n") .',
                    "day": '. \App::$now->modify("+1 month")->format("t") .'
                }
            }'
        ], []);
    }

    public function testWithRights()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            'slotType' => 'intern',
            '__body' => '{
                "firstDay": {
                    "year": '. \App::$now->format("Y") .',
                    "month": '. \App::$now->format("n") .',
                    "day": '. \App::$now->format("j") .'
                },
                "lastDay": {
                    "year": '. \App::$now->modify("+1 month")->format("Y") .',
                    "month": '. \App::$now->modify("+1 month")->format("n") .',
                    "day": '. \App::$now->modify("+1 month")->format("t") .'
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
                ],
                "scopes": [
                    {
                      "id": 141
                    }
                ]
            }'
        ], []);
        $this->assertStringContainsString('calendar.json', (string)$response->getBody());
    }

    public function testEmptyDays()
    {
        $this->expectException('BO\Zmsapi\Exception\Calendar\AppointmentsMissed');
        $this->render([], [
            '__body' => '{
                "requests": [
                    {
                      "id": "120703",
                      "name": "Personalausweis beantragen",
                      "source": "dldb"
                    }
                ],
                "scopes": [{"id":141, "provider":{"id":122217}}],
                "firstDay": {
                    "year": '. \App::$now->modify("+3 month")->format("Y") .',
                    "month": '. \App::$now->modify("+3 month")->format("n") .',
                    "day": '. \App::$now->modify("+3 month")->format("j") .'
                },
                "lastDay": {
                    "year": '. \App::$now->modify("+4 month")->format("Y") .',
                    "month": '. \App::$now->modify("+4 month")->format("n") .',
                    "day": '. \App::$now->modify("+4 month")->format("t") .'
                }
            }'
        ], []);
    }

    public function testFillWithEmptyDays()
    {
        $response = $this->render([], [
            'fillWithEmptyDays' => '1',
            '__body' => '{
                "requests": [
                    {
                      "id": "120703",
                      "name": "Personalausweis beantragen",
                      "source": "dldb"
                    }
                ],
                "scopes": [{"id":141, "provider":{"id":122217}}],
                "firstDay": {
                    "year": '. \App::$now->modify("+3 month")->format("Y") .',
                    "month": '. \App::$now->modify("+3 month")->format("n") .',
                    "day": '. \App::$now->modify("+3 month")->format("j") .'
                },
                "lastDay": {
                    "year": '. \App::$now->modify("+4 month")->format("Y") .',
                    "month": '. \App::$now->modify("+4 month")->format("n") .',
                    "day": '. \App::$now->modify("+4 month")->format("t") .'
                }
            }'
        ], []);
        $this->assertStringContainsString('calendar.json', (string)$response->getBody());
    }
}
