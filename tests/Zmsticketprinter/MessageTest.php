<?php

namespace BO\Zmsticketprinter\Tests;

class MessageTest extends Base
{
    protected $classname = "Message";

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
                'url' => '/ticketprinter/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
          'status' => 'process_success',
        ], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'scopeId' => 141
        ], [ ]);
        $this->assertStringContainsString('Wartenummernausdruck erfolgt!', (string) $response->getBody());
    }
}
