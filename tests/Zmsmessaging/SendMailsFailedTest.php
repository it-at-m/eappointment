<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendMailsFailedTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/mails/',
                'response' => $this->readFixture("GET_queue_empty.json"),
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
