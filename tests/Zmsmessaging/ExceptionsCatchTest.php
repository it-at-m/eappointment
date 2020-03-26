<?php

namespace BO\Zmsmessaging\Tests;

class ExceptionsCatchTest extends Base
{
    public function testLogMailMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'setUserInfo',
                    'parameters' => [
                        '_system_messenger',
                        'zmsmessaging'
                    ]
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("GET_mails_queue_no_mail.json"),
                ],
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('PHPMailer Failure: Die Adresse ist ungültig:  (setFrom)');
    }

    public function testLogNotificationMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'setUserInfo',
                    'parameters' => [
                        '_system_messenger',
                        'zmsmessaging'
                    ]
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("GET_notifications_queue_no_mail.json"),
                ],
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('PHPMailer Failure: Die Adresse ist ungültig:  (setFrom)');
    }

    public function testLogMailOlderThanOneHour()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'setUserInfo',
                    'parameters' => [
                        '_system_messenger',
                        'zmsmessaging'
                    ]
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("GET_mails_queue_old.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/mails/1234/',
                    'response' => $this->readFixture("GET_mail.json")
                ],
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('Queue entry older than 1 hour has been removed');
    }

    public function testLogNotificationOlderThanOneHour()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'setUserInfo',
                    'parameters' => [
                        '_system_messenger',
                        'zmsmessaging'
                    ]
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("GET_notifications_queue_old.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/notification/1234/',
                    'response' => $this->readFixture("GET_notification.json")
                ],
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('Queue entry older than 1 hour has been removed');
    }
}
