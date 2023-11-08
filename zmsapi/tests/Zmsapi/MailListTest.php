<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class MailListTest extends Base
{
    protected $classname = "MailList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('"error":false', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
