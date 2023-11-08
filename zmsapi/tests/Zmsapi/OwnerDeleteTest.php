<?php

namespace BO\Zmsapi\Tests;

class OwnerDeleteTest extends Base
{
    protected $classname = "OwnerDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => 99], [], []); //Test Owner
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsdb\Exception\Owner\OrganisationListNotEmpty');
        $this->expectExceptionCode(428);
        $this->render(['id' => 23], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsapi\Exception\Owner\OwnerNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
