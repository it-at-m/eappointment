<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class ExceptionsCatchTest extends Base
{
    public function testLogMailMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_workstation.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation.json")
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
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/_system_messenger/',
                    'response' => $this->readFixture("GET_mail.json")
                ]
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('Zmsmessaging PHPMailer Failure: Die Adresse ist ungültig:  (setFrom)');
    }

    public function testLogNotificationMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_workstation.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation.json")
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
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/_system_messenger/',
                    'response' => $this->readFixture("GET_notification.json")
                ]
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('Zmsmessaging PHPMailer Failure: Die Adresse ist ungültig:  (setFrom)');
    }

    public function testLogMailOlderThanOneHour()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_workstation.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation.json")
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
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/_system_messenger/',
                    'response' => $this->readFixture("GET_mail.json")
                ]
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
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_workstation.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation.json")
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
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/_system_messenger/',
                    'response' => $this->readFixture("GET_notification.json")
                ]
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertTrue(0 == count($resultList));
        $this->assertLogHasWarningThatContains('Queue entry older than 1 hour has been removed');
    }
}
