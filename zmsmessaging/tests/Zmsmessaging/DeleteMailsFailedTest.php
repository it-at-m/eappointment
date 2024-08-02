<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteMailsFailedTest extends Base
{
    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/mails/',
                'parameters' => [
                    "resolveReferences" => 2, "limit" => 200, "onlyIds" => true
                ],
                'response' => $this->readFixture("GET_mails_queue_id_only.json"),
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