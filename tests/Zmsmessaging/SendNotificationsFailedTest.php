<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationsFailedTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/notification/',
                'response' => $this->readFixture("GET_queue_empty.json"),
            ]
        ];
    }

    public function testFailed()
    {
        \App::$messaging = new \BO\Zmsmessaging\SendQueue('notification');
        $resultList = \App::$messaging->startNotificationTransmission();
        foreach ($resultList as $notification) {
            $this->assertContains('No notification entry found in Database', $notification['errorInfo']);
        }
    }
}
