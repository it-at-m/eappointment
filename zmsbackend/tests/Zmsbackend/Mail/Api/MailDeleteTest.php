<?php

namespace BO\Zmsbackend\Tests\Mail\Api;

class MailDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "MailDelete";

    public function testRendering()
    {
        $jsonString = (string)(new \BO\Zmsbackend\Tests\Mail\Api\MailAddTest('dummyTest'))->testRendering()->getBody();
        $message = json_decode($jsonString, true);
        $entity = new \BO\Zmsentities\Mail($message['data']);
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $response = $this->render(['id' => $entity->id], [], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->expectException('\BO\Zmsbackend\Mail\Exception\MailNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 0], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('\\BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 1], [], []);
    }
}
