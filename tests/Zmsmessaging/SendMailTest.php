<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/mails/',
                'response' => $this->readFixture("GET_mails_queue.json"),
            ]
        ];
    }

    public function testSendMailQueue()
    {
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $resultList = \App::$messaging->startMailTransmission();
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
