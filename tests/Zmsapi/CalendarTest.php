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
                      "id": 122217
                    }
                ],
                "scopes": [
                    {
                      "id": 141
                    }
                ]
            }'
        ], []);
        $this->assertContains('calendar.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setExpectedException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testInvalidFirstDay()
    {
        $this->setExpectedException('BO\Zmsapi\Exception\Calendar\InvalidFirstDay');
        $this->render([], [
            '__body' => '{
                "lastDay": {
                    "year": '. \App::$now->modify("+1 month")->format("Y") .',
                    "month": '. \App::$now->modify("+1 month")->format("n") .',
                    "day": '. \App::$now->modify("+1 month")->format("t") .'
                }
            }'
        ], []);
    }

    public function testEmptyDays()
    {
        $this->setExpectedException('BO\Zmsapi\Exception\Calendar\AppointmentsMissed');
        $this->render([], [
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
                }
            }'
        ], []);
    }
}
