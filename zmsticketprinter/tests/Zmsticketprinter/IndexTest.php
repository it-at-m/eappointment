<?php

namespace BO\Zmsticketprinter\Tests;

class IndexTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_multi.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/department/',
                'response' => $this->readFixture("GET_department_74.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'parameters' => [],
                'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                'response' => $this->readFixture("GET_config.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141,l[http://www.berlin.de/|Portal berlin.de]'
            ]
        ], [ ]);
        $this->assertStringContainsString('fordern Sie eine Wartenummer', (string) $response->getBody());
        $this->assertStringContainsString('Bürgeramt Hohenzollerndamm', (string) $response->getBody());
        $this->assertStringContainsString('Portal berlin.de', (string) $response->getBody());
    }
}
