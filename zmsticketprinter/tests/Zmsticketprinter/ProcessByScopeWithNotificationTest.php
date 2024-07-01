<?php

namespace BO\Zmsticketprinter\Tests;

class ProcessByScopeWithNotificationTest extends Base
{
    protected $classname = "Process";

    protected $arguments = [ ];

    protected $parameters = [ ];


    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/ticketprinter/711abcdefghijklmnopqrstuvwxyz/',
                    'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_notification.json"),
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
                    'url' => '/scope/141/waitingnumber/711abcdefghijklmnopqrstuvwxyz/',
                    'response' => $this->readFixture("GET_process_100044_57c2_with_notifications.json"),
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
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '711abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141,
        ], [ ]);
        $this->assertStringContainsString('Wünschen Sie eine SMS-Benachrichtigung', (string) $response->getBody());
        $this->assertStringContainsString('30 Minuten vor ihrem Aufruf?', (string) $response->getBody());
    }

    public function testWithoutNotificationEnabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/ticketprinter/711abcdefghijklmnopqrstuvwxyz/',
                    'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_notification.json"),
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/ticketprinter/',
                    'response' => $this->readFixture("GET_ticketprinter.json"),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74_withoutNotificationEnabled.json"),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/waitingnumber/711abcdefghijklmnopqrstuvwxyz/',
                    'response' => $this->readFixture("GET_process_100044_57c2_with_notifications.json"),
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
        $response = $this->render([], [
            '__cookie' => [
                'Ticketprinter' => '711abcdefghijklmnopqrstuvwxyz',
            ],
            'scopeId' => 141,
        ], [ ]);
        $this->assertStringNotContainsString('Wünschen Sie eine SMS-Benachrichtigung', (string) $response->getBody());
        $this->assertStringNotContainsString('30 Minuten vor ihrem Aufruf?', (string) $response->getBody());
    }
}
