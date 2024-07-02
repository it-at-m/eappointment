<?php

namespace BO\Zmsticketprinter\Tests;

class NotificationByScopeTest extends Base
{
    protected $classname = "Notification";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/queue/2/',
                'response' => $this->readFixture("GET_process_with_waitingnumber.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141,
            'waitingNumber' => 2
        ], [ ]);
        $this->assertStringContainsString('Bitte geben Sie hier<br/> Ihre Handynummer ein', (string) $response->getBody());
    }
}
