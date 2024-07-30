<?php

namespace BO\Zmsmessaging\Tests;

class ExceptionsCatchTest extends Base
{
    public function testLogMailMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'limit' => 200
                    ],
                    'response' => $this->readFixture("GET_mails_queue_no_department.json"),
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/mails/1234/',
                    'response' => $this->readFixture("GET_mail.json")
                ]
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        $this->assertEquals(0, count($resultList));
        foreach (\BO\Zmsmessaging\BaseController::getLogList() as $key => $value) {
            if (strpos($value, 'PHPMailer Failure') !== false) {
                $this->assertStringContainsString('Die Adresse ist ungültig:  (From)', $value);
            } elseif (strpos($value, 'Zmsmessaging.ERROR') !== false) {
                $this->assertStringContainsString('No valid mailer', $value);
            } elseif (strpos($value, 'Exception Failure') !== false) {
                if (\App::$verify_dns_enabled) {
                    $this->assertStringContainsString('No valid email exists', $value);
                }
            } else {
                $this->assertFalse(strpos($value, 'PHPMailer Failure') !== false);
            }
        }
    }

    public function testLogNotificationMissingDepartmentMail()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_config.json"),
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
        foreach (\BO\Zmsmessaging\BaseController::getLogList() as $key => $value) {
            if (strpos($value, 'PHPMailer Failure') !== false) {
                $this->assertStringContainsString('PHPMailer Failure: Die Adresse ist ungültig:  (From)', $value);
            } else {
                $this->assertFalse(strpos($value, 'PHPMailer Failure') !== false);
            }
        }
    }

    public function testLogMailOlderThanOneHour()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'limit' => 200
                    ],
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
        foreach (\BO\Zmsmessaging\BaseController::getLogList() as $key => $value) {
            if (strpos($value, 'Zmsmessaging Failure') !== false) {
                $this->assertStringContainsString('Queue entry older than 1 hour has been removed', $value);
            } else {
                $this->assertFalse(strpos($value, 'Zmsmessaging Failure') !== false);
            }
        }
    }

    public function testLogNotificationOlderThanOneHour()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_config.json"),
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
        foreach (\BO\Zmsmessaging\BaseController::getLogList() as $key => $value) {
            if (strpos($value, 'Zmsmessaging Failure') !== false) {
                $this->assertStringContainsString('Queue entry older than 1 hour has been removed', $value);
            } else {
                $this->assertFalse(strpos($value, 'Zmsmessaging Failure') !== false);
            }
        }
    }
}