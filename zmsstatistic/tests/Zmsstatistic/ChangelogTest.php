<?php

namespace BO\Zmsstatistic\Tests;

class ChangelogTest extends Base
{
    protected $classname = "Changelog";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'parameters' => [],
                    'xtoken' => 'secure-token',
                    'response' => $this->readFixture("GET_config.json")
                ],
            ]
        );
        $response = $this->render([ ], ['__uri' => '/changelog'], [ ]);
        $this->assertStringContainsString('Changelog', (string) $response->getBody());
        $this->assertStringContainsString('https://it-at-m.github.io/eappointment/overview/changelog.html', (string) $response->getBody());
    }
} 
