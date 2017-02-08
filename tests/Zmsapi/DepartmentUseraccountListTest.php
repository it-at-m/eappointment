<?php

namespace BO\Zmsapi\Tests;

class DepartmentUseraccountListTest extends Base
{
    protected $classname = "DepartmentUseraccountList";

    const DEPARTMENT_ID = 74;

    public function testRendering()
    {
        $this->setWorkstation()->useraccount->setRights('useraccount');
        $response = $this->render([static::DEPARTMENT_ID], [], ['isAssigned' => true]);
        $this->assertContains('testuser', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
