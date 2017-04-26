<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailsFailedTest extends Base
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
                'url' => '/mails/',
                'response' => $this->readFixture("GET_queue_empty.json"),
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
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $resultList = \App::$messaging->startMailTransmission();
        foreach ($resultList as $mail) {
            $this->assertContains('No mail entry found in Database', $mail['errorInfo']);
        }
    }
}
