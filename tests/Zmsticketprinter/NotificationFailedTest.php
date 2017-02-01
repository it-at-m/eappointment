<?php

namespace BO\Zmsticketprinter\Tests;

class NotificationFailedTest extends Base
{
    protected $classname = "Notification";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render(
            [],
            [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'scopeId' => 141
            ],
            [ ]
        );
        $this->assertRedirect(
            $response,
            '/message/process_notification_amendment_waitingnumber_unvalid/?scopeId=141&notHome=1'
        );
    }

    public function testFailedWithException()
    {
        $this->setExpectedException('\BO\Zmsticketprinter\Exception\ScopeNotFound');
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'waitingNumber' => 2
        ], [ ]);
    }
}
