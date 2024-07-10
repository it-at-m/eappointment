<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailTest extends Base
{
    /*public function testSendMailQueue()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'limit' => 50
                    ],
                    'response' => $this->readFixture("GET_mails_queue.json"),
                ],
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $mail) {
            if (isset($mail['errorInfo'])) {
                echo "ERROR OCCURED: ". $mail['errorInfo'] ."\n";
            } else {
                $this->assertStringContainsString('text/html', trim($mail['mime']));
                $this->assertStringContainsString('calendar', json_encode($mail['attachments'][0]));
            }
        }
    }

    public function testSendMailQueueEmpty()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'limit' => 50
                    ],
                    'response' => $this->readFixture("GET_queue_empty.json"),
                ],
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $mail) {
            $this->assertStringContainsString('No mail entry found in Database', $mail['errorInfo']);
        }
    }

    public function testSendMailWithoutContent()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'limit' => 50
                    ],
                    'response' => $this->readFixture("GET_mails_queue_no_content.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/mails/1234/',
                    'response' => $this->readFixture("GET_mail_no_content.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json"),
                    'parameters' => ['error' => 1]
                ]
            ]
        );
        \App::$messaging = new \BO\Zmsmessaging\Mail();
        \App::$messaging->initQueueTransmission();
    }*/
}
