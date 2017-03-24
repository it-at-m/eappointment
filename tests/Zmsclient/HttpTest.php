<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class HttpTest extends Base
{
    public function testStatus()
    {
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/status/');
        $response = new \BO\Zmsclient\Psr7\Response();
        $entity = $result->getEntity();
        $response = \BO\Zmsclient\Status::testStatus($response, $entity);
        $this->assertTrue($entity instanceof \BO\Zmsentities\Schema\Entity);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $result->getResponse());
        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $result->getRequest());
    }

    public function testScopes()
    {
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/scope/');
        $ids = $result->getIds();
        $collection = $result->getCollection();
        $this->assertTrue($collection instanceof \BO\Zmsentities\Collection\Base);
        $this->assertContains('140,141,142', $ids);
    }

    public function testMails()
    {
        $this->writeTestLogin();
        $http = $this->createHttpClient();
        $entity = \BO\Zmsentities\Mail::createExample();
        $entity->process = $http->readGetResult('/process/82252/12a2/')->getEntity();
        $result = $http->readPostResult('/mails/', $entity, array('resolveReferences' => 0));
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
        $mailId = $entity->id;

        $result = $http->readGetResult('/mails/', array('resolveReferences' => 0));
        $data = $result->getData();
        $this->assertTrue($data[0] instanceof \BO\Zmsentities\Mail);

        $result = $http->readDeleteResult("/mails/$mailId/", array('resolveReferences' => 0));
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
        $this->writeTestLogout();
    }

    public function testHtml()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/doc/index.html');
        $result->getEntity();
    }

    public function testToken()
    {
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/config/', null, 'a9b215f1-e460-490c-8a0b-6d42c274d5e4');
        $result->getEntity();
    }

    public function testTokenFailed()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/config/');
        $result->getEntity();
    }

    public function testWrongFormat()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/doc/swagger.json');
        $result->getEntity();
    }

    public function testUnknownUrl()
    {
        $this->setExpectedException('\BO\Zmsclient\Exception');
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/unknownUri/');
        $result->getEntity();
    }

    protected function writeTestLogin()
    {
        $http = $this->createHttpClient();
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => 'berlinonline',
            'password' => '1palme1'
        ));
        $workstation = $http->readPostResult('/workstation/'. $userAccount->id .'/', $userAccount)->getEntity();
        if (isset($workstation->authkey)) {
            \BO\Zmsclient\Auth::setKey($workstation->authkey);
            $this->assertEquals($workstation->authkey, \BO\Zmsclient\Auth::getKey());
        }
        return $workstation;
    }

    protected function writeTestLogout()
    {
        $http = $this->createHttpClient();
        $http->readDeleteResult('/workstation/berlinonline/')->getEntity();
    }
}
