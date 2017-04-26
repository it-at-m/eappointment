<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailTest extends Base
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
                'response' => $this->readFixture("GET_mails_queue.json"),
            ],
            [
                'function' => 'readDeleteResult',
                'url' => '/workstation/_system_messenger/',
                'response' => $this->readFixture("GET_mail.json")
            ]
        ];
    }

    public function testSendMailQueue()
    {
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
}
