<?php

namespace BO\Zmsticketprinter\Tests;

class ProcessByScopeWithNotificationTest extends Base
{
    protected $classname = "Process";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_notification.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/waitingnumber/ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_process_100044_57c2_with_notifications.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/queue/',
                'response' => $this->readFixture("GET_queuelist_141.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'response' => $this->readFixture("GET_config.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'scopeId' => 141,
        ], [ ]);
        $this->assertContains('Wünschen Sie eine SMS-Benachrichtigung', (string) $response->getBody());
        $this->assertContains('30 Minuten vor ihrem Aufruf?', (string) $response->getBody());
    }
}
