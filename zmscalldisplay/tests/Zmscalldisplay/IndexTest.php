<?php

namespace BO\Zmscalldisplay\Tests;

use BO\Slim\Helper as SlimHelper;

class IndexTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/',
                    'response' => $this->readFixture("GET_calldisplay.json")
                ]
            ]
        );
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '141',
                'clusterlist' => '110'
            ]
        ], [ ]);
        $this->assertStringContainsString('Bürgeramt', (string) $response->getBody());

    }

    public function testWithHash()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/',
                    'response' => $this->readFixture("GET_calldisplay.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ]
            ]
        );
        $hash = SlimHelper::hashQueryParameters(
            'webcalldisplay',
            [
                'collections' => [
                    'scopelist' => '141',
                    'clusterlist' => '110'
                ],
                'queue' => NULL
            ],
            [   
                'collections',
                'queue'
            ]
        );
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '141',
                'clusterlist' => '110'
            ],
            'qrcode' => 1
        ], [ ]);
        $this->assertStringContainsString(
            'Bürgeramt', 
            (string) $response->getBody()
        );    }
}
