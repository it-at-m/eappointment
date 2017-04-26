<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteMailsFailedTest extends Base
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
                'url' => '/mails/1234/',
                'response' => $this->readFixture("GET_mail_failed.json"),
            ]
        ];
    }

    public function testDeleteException()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $entity = (new \BO\Zmsentities\Mail())->getExample();
        \App::$messaging = new \BO\Zmsmessaging\Mail();
        \App::$messaging->deleteEntityFromQueue($entity);
    }
}
