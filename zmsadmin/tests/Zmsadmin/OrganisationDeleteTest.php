<?php

namespace BO\Zmsadmin\Tests;

class OrganisationDeleteTest extends Base
{
    protected $arguments = [
        'id' => 71
    ];

    protected $parameters = [];

    protected $classname = "OrganisationDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/organisation/71/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/owner/?success=organisation_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
