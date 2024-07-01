<?php

namespace BO\Zmsticketprinter\Tests;

class NotificationAssignFailedTest extends Base
{
    protected $classname = "NotificationAssign";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/process/100044/57c2/',
                'response' => $this->readFixture("GET_process_100044_57c2.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '711abcdefghijklmnopqrstuvwxyz',
            ],
            'processId' => 100044,
            'authKey' => '57c2',
            'telephone' => '017123456'
        ], [ ]);
        $this->assertRedirect($response, '/message/process_notification_number_unvalid/?scopeId=141&notHome=1');
    }
}
