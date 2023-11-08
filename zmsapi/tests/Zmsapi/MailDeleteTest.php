<?php

namespace BO\Zmsapi\Tests;

class MailDeleteTest extends Base
{
    protected $classname = "MailDelete";

    public function testRendering()
    {
        $jsonString = (string)(new MailAddTest)->testRendering()->getBody();
        $message = json_decode($jsonString, true);
        $entity = new \BO\Zmsentities\Mail($message['data']);
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => $entity->id], [], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsapi\Exception\Mail\MailNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 0], [], []);
    }
}
