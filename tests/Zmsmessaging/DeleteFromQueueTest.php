<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteFromQueueTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/mails/',
                'response' => $this->readFixture("GET_mails_queue.json"),
            ],
            [
                'function' => 'readDeleteResult',
                'url' => '/mails/1234/',
                'response' => $this->readFixture("GET_mail.json"),
            ]
        ];
    }

    public function testDeleteMailFromQueue()
    {
        $entity = (new \BO\Zmsentities\Mail())->getExample();
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $mail = \App::$messaging->deleteFromQueue($entity);
        $this->assertEquals(1234, $mail['id']);
    }
}
