<?php

namespace BO\Zmsticketprinter\Tests;

class MessageTest extends Base
{
    protected $classname = "Message";

    protected $arguments = [ ];

    protected $parameters = [ ];

    #[\Override]
    protected function getApiCalls(): array
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
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
        ];
    }

    #[\Override]
    public function testRendering()
    {
        $response = $this->render([
          'status' => 'process_success',
        ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141
        ], [ ]);
        $this->assertStringContainsString('Wartenummernausdruck erfolgt!', (string) $response->getBody());
    }
}
