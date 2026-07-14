<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

class OwnerDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OwnerDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $response = $this->render(['id' => 99], [], []); //Test Owner
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $this->expectException('\BO\Zmsbackend\Owner\Exception\OrganisationListNotEmpty');
        $this->expectExceptionCode(428);
        $this->render(['id' => 23], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $this->expectException('\BO\Zmsbackend\Owner\Exception\OwnerNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
