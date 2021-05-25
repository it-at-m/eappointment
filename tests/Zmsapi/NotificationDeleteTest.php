<?php

namespace BO\Zmsapi\Tests;

class NotificationDeleteTest extends Base
{
    protected $classname = "NotificationDelete";

    public function testRendering()
    {
        $jsonString = (string)(new NotificationAddTest)->testRendering()->getBody();
        $entity = json_decode($jsonString)->data;
        $entity = new \BO\Zmsentities\Notification($entity);
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => $entity->id], [], []);
        $this->assertStringContainsString('notification.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsapi\Exception\Notification\NotificationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }
}
