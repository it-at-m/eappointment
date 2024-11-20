<?php

namespace BO\Zmsticketprinter\Tests;

class CustomizedTemplateTest extends Base
{
    protected $classname = "TicketprinterByScope";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/organisation/',
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
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_615.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/department/',
                'response' => $this->readFixture("GET_department_127.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/queue/',
                'response' => $this->readFixture("GET_queuelist_312.json"), //Bürgeramt 1 in Köpenick
            ],
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'parameters' => [],
                'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                'response' => $this->readFixture("GET_config.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
            'scopeId' => 615
        ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ]
        ], [ ]);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertStringContainsString('Ordnungsamt Charlottenburg-Wilmersdorf', (string) $response->getBody());
    }

    public function testTemplateNotFound()
    {
        $this->expectException('\BO\Zmsticketprinter\Exception\TemplateNotFound');
        $this->expectExceptionCode(404);
        $this->render([
            'scopeId' => 615
        ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'template' => 'notfound'
        ], [ ]);
    }
}
