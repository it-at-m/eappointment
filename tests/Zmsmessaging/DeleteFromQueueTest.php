<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteFromQueueTest extends Base
{
    protected function getApiCalls()
    {
        return [
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
        $this->assertTrue(\BO\Zmsmessaging\Transmission::deleteEntityFromQueue($entity));
    }
}
