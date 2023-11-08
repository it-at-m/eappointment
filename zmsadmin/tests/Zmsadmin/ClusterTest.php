<?php

namespace BO\Zmsadmin\Tests;

class ClusterTest extends Base
{
    protected $arguments = [
        'clusterId' => 109,
        'departmentId' => 74
    ];

    protected $parameters = [];

    protected $classname = "Cluster";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Cluster: Einrichtung und Administration', (string)$response->getBody());
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSaveWithUploadImage()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/cluster/109/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Bürgeramt Heerstraße',
            'hint' => '',
            'callDisplayText' => '',
            'uploadCallDisplayImage' => 'baer.png',
            'scopes' =>
            array(
                array(
                    'id' => '141',
                ),
            ),
            'save' => 'save',
            '__file' => array(
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'image/png',
                    13535
                )
            )
        ], [], 'POST');
        $this->assertRedirect($response, '/department/74/cluster/109/?success=cluster_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSaveWithUploadImageFailed()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Wrong Mediatype given, use gif, jpg, svg or png');
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/cluster/109/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ]
            ]
        );
        $this->render($this->arguments, [
            'name' => 'Bürgeramt Heerstraße',
            'hint' => '',
            'callDisplayText' => '',
            'uploadCallDisplayImage' => 'baer.png',
            'scopes' =>
            array(
                array(
                    'id' => '141',
                ),
            ),
            'save' => 'save',
            '__file' => array(
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'application/json',
                    13535
                )
            )
        ], [], 'POST');
    }

    public function testSaveWithDeleteImage()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/cluster/109/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/cluster/109/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Bürgeramt Heerstraße',
            'hint' => '',
            'callDisplayText' => '',
            'uploadCallDisplayImage' => 'baer.png',
            'scopes' =>
            array(
                array(
                    'id' => '141',
                ),
            ),
            'save' => 'save',
            'removeImage' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/department/74/cluster/109/?success=cluster_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
