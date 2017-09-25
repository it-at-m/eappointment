<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailTest extends Base
{
    public function testSendMailQueue()
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
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("GET_mails_queue.json"),
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/_system_messenger/',
                    'response' => $this->readFixture("GET_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/log/process/123456/',
                    'response' => $this->readFixture("POST_log.json")
                ]
            ]
        );

        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $mail) {
            if (isset($mail['errorInfo'])) {
                echo "ERROR OCCURED: ". $mail['errorInfo'] ."\n";
            } else {
                $this->assertContains('text/html', trim($mail['mime']));
                $this->assertContains('calendar', json_encode($mail['attachments'][0]));
            }
        }
    }

    public function testSendMailQueueEmpty()
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
                    'function' => 'readGetResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("GET_queue_empty.json"),
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
        foreach ($resultList as $mail) {
            $this->assertContains('No mail entry found in Database', $mail['errorInfo']);
        }
    }
}
