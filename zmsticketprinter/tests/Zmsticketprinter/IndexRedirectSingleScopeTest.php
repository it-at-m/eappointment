<?php

namespace BO\Zmsticketprinter\Tests;

class IndexRedirectSingleScopeTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_78.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/78abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/department/',
                'response' => $this->readFixture("GET_department_74.json"),
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
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '78abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's312'
            ]
        ], [ ]);
        $this->assertRedirect($response, '/scope/312/');
    }
}
