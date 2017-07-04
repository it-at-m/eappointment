<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteFromQueueTest extends Base
{
    protected function getApiCalls()
    {
        return [
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
                'url' => '/mails/1234/',
                'response' => $this->readFixture("GET_mail.json"),
            ],
        ];
    }

    public function testDeleteMailFromQueue()
    {
        $entity = (new \BO\Zmsentities\Mail())->getExample();
        \App::$messaging = new \BO\Zmsmessaging\Mail();
        $this->assertTrue(\App::$messaging->deleteEntityFromQueue($entity));
    }
}
