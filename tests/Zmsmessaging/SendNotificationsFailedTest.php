<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationsFailedTest extends Base
{
    public function testFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("GET_queue_empty.json")
                ],
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $notification) {
            $this->assertStringContainsString('No notification entry found in Database', $notification['errorInfo']);
        }
    }



    public function testLoginFailed()
    {
        $exception = new \BO\Zmsclient\Exception();
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("GET_queue_empty.json")
                ]
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Notification();
    }
}
