<?php

namespace BO\Zmsticketprinter\Tests;

class HomeUrlTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
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
            ]
        );
        $request = static::createBasicRequest('GET', '/');
        \BO\Zmsclient\Ticketprinter::setHomeUrl("", $request);
        $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141,l[http://www.berlin.de/|Portal berlin.de]',
                'home' => 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/'
            ]
        ], [ ]);

        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        $this->assertEquals($homeUrl, 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/');
    }

    public function testRedirectToSingleScopePage()
    {
        $this->setApiCalls(
            [
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
                    'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_home.json"),
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
                    'response' => $this->readFixture("GET_config.json"),
                ]
            ]
        );
        $request = static::createBasicRequest('GET', '/');
        \BO\Zmsclient\Ticketprinter::setHomeUrl("", $request);
        $response = $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71abcdefghijklmnopqrstuvwxyz',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141',
                'home' => 'http://service.berlin.de/home/'
            ]
        ], [ ]);
        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        $queryString = urlencode('ticketprinter[home]') . '=' . urlencode($homeUrl);
        $this->assertRedirect($response, '/scope/141/?'. $queryString);
    }
}
