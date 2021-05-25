<?php

namespace BO\Zmsapi\Tests;

class NotificationAddTest extends Base
{
    protected $classname = "NotificationAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('sms');
        $response = $this->render([], [
            '__body' => $this->readFixture('GetNotification.json')
        ], []);
        $this->assertStringContainsString('notification.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        return $response;
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('sms');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setRights('sms');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "id": 1234,
                "createIP": "145.15.3.10",
                "createTimestamp": 1447931596000
            }'
        ], []);
    }
}
