<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationTest extends Base
{
    public function testSendNotificationQueue()
    {
        $this->setApiCalls([
            [
                'function' => 'setUserInfo',
                'parameters' => [
                    '_system_messenger',
                    'zmsmessaging'
                ]
            ],
            [
                'function' => 'readGetResult',
                'url' => '/notification/',
                'response' => $this->readFixture("GET_notifications_queue.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/log/process/123456/',
                'response' => $this->readFixture("POST_log.json")
            ],
        ]);
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $notification) {
            if (isset($notification['errorInfo'])) {
                echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
            } else {
                $this->assertContains('Content-Transfer-Encoding: base64', trim($notification['mime']));
                $this->assertContains(
                    'sms=0123456789@example.com',
                    json_encode($notification['recipients'])
                );
            }
        }
    }
}
