<?php

namespace BO\Zmsticketprinter\Tests;

class Index2ButtonsTest extends Base
{

    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/organisation/scope/141/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_2.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141,c110'
            ]
        ], [ ]);
        $this->assertContains('wartebuttonbereich_zweizeilig_tief', (string) $response->getBody());
    }
}
