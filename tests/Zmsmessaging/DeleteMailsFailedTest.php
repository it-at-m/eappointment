<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class DeleteMailsFailedTest extends Base
{
    protected function getApiCalls()
    {
        return [
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
        \BO\Zmsmessaging\Transmission::deleteEntityFromQueue($entity);
    }
}
