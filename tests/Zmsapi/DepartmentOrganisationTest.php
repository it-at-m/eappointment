<?php

namespace BO\Zmsapi\Tests;

class DepartmentOrganisationTest extends Base
{
    protected $classname = "DepartmentOrganisation";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(72);
        $response = $this->render([72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertContains('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingDepartment()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $response = $this->render([72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertContains('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingLogin()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $response = $this->render([72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertContains('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
