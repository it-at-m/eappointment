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
                'url' => '/ticketprinter/71abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/department/',
                'response' => $this->readFixture("GET_department_74.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/waitingnumber/71abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_process_100044_57c2.json"),
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
            ]
            // TODO: Remove unused config request - https://github.com/it-at-m/eappointment/issues/1807
            /*,
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'parameters' => [],
                'xtoken' => 'secure-token',
                'response' => $this->readFixture("GET_config.json"),
            ]*/
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141,
        ], [ ]);
        $this->assertStringContainsString('Es warten', (string) $response->getBody());
        $this->assertStringContainsString('Ihre Wartenummer wird gedruckt', (string) $response->getBody());
    }
}
