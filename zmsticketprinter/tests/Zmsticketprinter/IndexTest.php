<?php

namespace BO\Zmsticketprinter\Tests;

class IndexTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls($this->getValidButtonListApiCalls());
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141,l[http://www.berlin.de/|Portal berlin.de]'
            ]
        ], [ ]);
        $this->assertStringContainsString('Bürgeramt Hohenzollerndamm', (string) $response->getBody());
        $this->assertStringContainsString('Portal berlin.de', (string) $response->getBody());
    }

    public function testSkipsMissingScopeWhenOthersExist()
    {
        $exception = new \BO\Zmsclient\Exception(
            'API-Error: Zu den angegebenen Daten konnte kein Standort gefunden werden.'
        );
        $exception->template = 'BO\\Zmsbackend\\Scope\\Exception\\ScopeNotFound';
        $this->setApiCalls(
            array_merge(
                [
                    [
                        'function' => 'readGetResult',
                        'url' => '/scope/999/organisation/',
                        'parameters' => ['resolveReferences' => 2],
                        'exception' => $exception,
                    ],
                ],
                $this->getValidButtonListApiCalls()
            )
        );
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's999,s141,l[http://www.berlin.de/|Portal berlin.de]'
            ]
        ], [ ]);
        $this->assertStringContainsString('Bürgeramt Hohenzollerndamm', (string) $response->getBody());
        $this->assertStringContainsString('Portal berlin.de', (string) $response->getBody());
    }

    public function testMissingScopeOnlyStillFails()
    {
        $exception = new \BO\Zmsclient\Exception(
            'API-Error: Zu den angegebenen Daten konnte kein Standort gefunden werden.'
        );
        $exception->template = 'BO\\Zmsbackend\\Scope\\Exception\\ScopeNotFound';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/999/organisation/',
                    'parameters' => ['resolveReferences' => 2],
                    'exception' => $exception,
                ],
            ]
        );
        $this->expectException('\BO\Zmsticketprinter\Exception\OrganisationNotFound');
        $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's999'
            ]
        ], [ ]);
    }

    protected function getValidButtonListApiCalls(): array
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
            ]
        ];
    }
}
