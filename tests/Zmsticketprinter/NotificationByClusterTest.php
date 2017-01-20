<?php

namespace BO\Zmsticketprinter\Tests;

class NotificationByClusterTest extends Base
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/process/queue/2/cluster/110/',
                'response' => $this->readFixture("GET_process_with_waitingnumber.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/organisation/scope/141/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'clusterId' => 110,
            'waitingNumber' => 2
        ], [ ]);
        $this->assertContains('Bitte geben Sie hier<br/> Ihre Handynummer ein', (string) $response->getBody());
    }
}
