<?php

namespace BO\Zmsbackend\Tests\Mail\Api;

use BO\Zmsbackend\Helper\User;

class MailListTest extends \BO\Zmsbackend\Tests\Api\Base
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
