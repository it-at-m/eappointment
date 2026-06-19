<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

class OrganisationDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => 80], [], []); //Test Organisation
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\DepartmentListNotEmpty');
        $this->expectExceptionCode(428);
        $this->render(['id' => 74], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
