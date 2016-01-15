<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class HttpTest extends Base
{
    public function testStatus()
    {
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/status/');
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Schema\Entity);
        $this->assertTrue($result->getResponse() instanceof \Psr\Http\Message\ResponseInterface);
    }

    public function testMails()
    {
        $http = $this->createHttpClient();
        $testParameters = ['pretty' => 1];

        $entity = \BO\Zmsentities\Mail::createExample();
        $result = $http->readPostResult('/mails/', $entity, $testParameters);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
        $mailId = $entity->id;

        $result = $http->readGetResult('/mails/', $testParameters);
        $data = $result->getData();
        $this->assertTrue($data[0] instanceof \BO\Zmsentities\Mail);

        $result = $http->readDeleteResult("/mails/$mailId/", $testParameters);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
    }

    public function testHtml()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $http->readGetResult('/doc/index.html');
    }

    public function testWrongFormat()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $http->readGetResult('/doc/swagger.json');
    }

    public function testUnknownUrl()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $http->readGetResult('/unknownUri/');
    }
}
