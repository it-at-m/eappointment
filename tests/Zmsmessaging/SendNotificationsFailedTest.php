<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationsFailedTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readPostResult',
                'url' => '/workstation/_system_messenger/',
                'response' => $this->readFixture("GET_workstation.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/workstation/',
                'response' => $this->readFixture("GET_workstation.json")
            ],
            [
                'function' => 'readGetResult',
                'url' => '/notification/',
                'response' => $this->readFixture("GET_queue_empty.json")
            ],
            [
                'function' => 'readDeleteResult',
                'url' => '/workstation/_system_messenger/',
                'response' => $this->readFixture("GET_mail.json")
            ]
        ];
    }

    public function testFailed()
    {
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $notification) {
            $this->assertContains('No notification entry found in Database', $notification['errorInfo']);
        }
    }
}
