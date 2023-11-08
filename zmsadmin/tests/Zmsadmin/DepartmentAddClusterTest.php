<?php

namespace BO\Zmsadmin\Tests;

class DepartmentAddClusterTest extends Base
{
    protected $arguments = [
        'clusterId' => 109,
        'departmentId' => 74
    ];

    protected $parameters = [];

    protected $classname = "DepartmentAddCluster";

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
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Cluster: Einrichtung und Administration', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSaveWithUploadImage()
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
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/department/74/cluster/',
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
        $this->assertRedirect($response, '/department/74/cluster/109/?success=cluster_created');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
