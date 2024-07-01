<?php

namespace BO\Zmsticketprinter\Tests;

class NotificationAmendmentTest extends Base
{
    protected $classname = "NotificationAmendment";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/711abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/',
                'response' => $this->readFixture("GET_scope_lessdata.json"),
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
            ],
        ];
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '711abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141
        ], [ ]);
        $this->assertStringContainsString('Bitte geben Sie hier Ihre Wartenummer ein:', (string) $response->getBody());
    }
}
