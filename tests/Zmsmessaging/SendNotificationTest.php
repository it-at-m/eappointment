<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationTest extends Base
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
                'response' => $this->readFixture("GET_notifications_queue.json")
            ],
            [
                'function' => 'readDeleteResult',
                'url' => '/notification/1234/',
                'response' => $this->readFixture("GET_notification.json")
            ]
        ];
    }

    public function testSendNotificationQueue()
    {
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $notification) {
            if (isset($notification['errorInfo'])) {
                echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
            } else {
                $this->assertContains('Content-Transfer-Encoding: base64', trim($notification['mime']));
                $this->assertContains('sms=0123456789@example.com', json_encode($notification['recipients']));
            }
        }
    }
}
