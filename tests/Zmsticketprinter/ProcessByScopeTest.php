<?php

namespace BO\Zmsticketprinter\Tests;

class ProcessByScopeTest extends Base
{
    protected $classname = "Process";

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
                'url' => '/scope/141/waitingnumber/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_process_100044_57c2.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/queue/',
                'response' => $this->readFixture("GET_queuelist_141.json"),
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
            'scopeId' => 141,
        ], [ ]);
        $this->assertContains('Es warten', (string) $response->getBody());
        $this->assertContains('Ihre Wartenummer wird gedruckt', (string) $response->getBody());
    }
}
