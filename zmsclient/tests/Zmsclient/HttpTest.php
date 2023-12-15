<?php

namespace BO\Zmsclient\Tests;

use BO\Zmsclient\Psr7\Response;
use BO\Zmsclient\Status;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Schema\Entity;
use Fig\Http\Message\StatusCodeInterface;

class HttpTest extends Base
{

    /**
     * @runInSeparateProcess
     */
    /*public function testBasicAuth()
    {
        $parsed = parse_url(static::$http_baseurl);
        $parsed['user']  = "_system_soap";
        $parsed['pass']  = "zmssoap";
        $uri = new \BO\Zmsclient\Psr7\Uri();
        $uri = $uri->withScheme($parsed['scheme']);
        $uri = $uri->withUserInfo($parsed['user'], $parsed['pass']);
        $uri = $uri->withHost($parsed['host']);
        $uri = (isset($parsed['path'])) ? $uri->withPath($parsed['path']) : $uri;
        $uri = (isset($parsed['port'])) ? $uri->withPort($parsed['port']) : $uri;
        static::$http_baseurl = (string)$uri;
        $this->createHttpClient(null, false);
        $userInfo = static::$http_client->getUserInfo();
        $this->assertEquals($userInfo, '_system_soap:zmssoap');
    }*/

    /**
     * @runInSeparateProcess
     */
    public function testJsonCompressLevel()
    {
        \BO\Zmsclient\HTTP::$jsonCompressLevel = 1;
        $this->createHttpClient();
        $result = static::$http_client->readGetResult('/scope/');
        $collection = $result->getCollection();
        $this->assertStringContainsString('123', $result->getIds());
    }

    public function testStatus()
    {
        $result = static::$http_client->readGetResult('/status/');
        $this->assertTrue($result->isStatus(200));
        $response = new Response();
        $status = $result->getEntity();
        $response = Status::testStatus($response, $status);
        $this->assertTrue($status instanceof Entity);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $result->getResponse());
        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $result->getRequest());

        $status['mail']['oldestSeconds'] = 400;
        $status['notification']['oldestSeconds'] = 400;
        $status['database']['logbin'] = 'OFF';
        $status['database']['clusterStatus'] = 'OFF';
        $status['database']['locks'] = 11;
        $status['database']['threads'] = 31;
        $status['database']['nodeConnections'] = 51;
        $status['processes']['lastCalculate'] = (new \DateTimeImmutable())->modify('- 4 hour')->format('Y-m-d H:i:s');
        $status['sources']['dldb']['last'] = (new \DateTimeImmutable())->modify('- 3 hour')->format('Y-m-d H:i:s');

        $response = new Response();
        $response = Status::testStatus($response, $status);
        $this->assertStringContainsString('Oldest mail with age in seconds: 400s', (string)$response->getBody());
        $this->assertStringContainsString('Oldest sms with age in seconds: 400s', (string)$response->getBody());
        $this->assertStringContainsString(
            'DB connection without replication log detected',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'DB connection is not part of a galera cluster',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('High amount of DB-Locks: 11', (string)$response->getBody());
        $this->assertStringContainsString('High amount of DB-Threads: 31', (string)$response->getBody());
        $this->assertStringContainsString(
            'DB connected thread over 50% of available connections',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('Last DLDB Import is more then 2 hours ago', (string)$response->getBody());
        $this->assertStringContainsString('slot calculation is 14400 seconds old', (string)$response->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testStatusServerError()
    {
        $result = static::$http_client->readGetResult('/status/');
        $response = new Response();
        $status = $result->getEntity();

        $status['sources']['dldb']['last'] = (new \DateTimeImmutable())->modify('- 6 hour')->format('Y-m-d H:i:s');
        $response = Status::testStatus($response, $status);

        $this->assertStringContainsString(
            'CRIT - Last DLDB Import is more then 4 hours ago',
            (string)$response->getBody()
        );
        self::assertSame(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testStatusOk()
    {
        $result = static::$http_client->readGetResult('/status/', ['includeProcessStats' => 0]);
        $response = new Response();
        $status = $result->getEntity();
        $status['mail']['oldestSeconds'] = 0;
        $status['notification']['oldestSeconds'] = 0;
        $status['database']['logbin'] = 'ON';
        $status['database']['clusterStatus'] = 'ON';
        $status['database']['locks'] = 0;
        $status['database']['threads'] = 0;
        $status['database']['nodeConnections'] = 0;
        $status['processes']['lastCalculate'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $status['sources']['dldb']['last'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        
        $response = Status::testStatus($response, $status);
        $this->assertEquals(
            'OK - DB=0% Threads=0 Locks=0', 
            (string)$response->getBody()
        );
    }

    public function testStatusFailed()
    {
        $closure = function () {
            throw new \Exception('Status failed');
        };
        $response = new Response();
        $response = Status::testStatus($response, $closure);
        $this->assertStringContainsString('Status failed', (string)$response->getBody());
    }

    public function testCollection()
    {
        $result = static::$http_client->readGetResult('/scope/');
        $collection = $result->getCollection();
        $this->assertStringContainsString('123', $result->getIds());
        $this->assertTrue($collection instanceof \BO\Zmsentities\Collection\Base);
    }

    public function testMails()
    {
        $now = new \DateTimeImmutable('2016-04-01 08:00');
        $entity = Mail::createExample();
        $confirmedProcess = static::$http_client->readGetResult('/scope/141/process/'. $now->format('Y-m-d') .'/')
            ->getCollection()
            ->toQueueList($now)
            ->withStatus(['confirmed'])
            ->toProcessList()
            ->getFirst();

        /** @var Mail $entity */
        $entity->process = static::$http_client
            ->readGetResult(
                '/process/'. $confirmedProcess->getId() .'/'. $confirmedProcess->getAuthKey() .'/',
                ['resolveReferences' => 0]
            )->getEntity();
            
        $result = static::$http_client->readPostResult('/mails/', $entity, ['resolveReferences' => 0]);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof Mail);
        $mailId = $entity->id;

        $result = static::$http_client->readGetResult('/mails/');
        $data = $result->getData();
        $this->assertTrue($data[0] instanceof Mail);

        $result = static::$http_client->readDeleteResult("/mails/$mailId/", []);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof Mail);
        $this->writeTestLogout(static::$http_client);
    }

    public function testHtml()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $result = static::$http_client->readGetResult('/doc/index.html');
        $result->getEntity();
    }

    public function testToken()
    {
        $this->createHttpClient(null, false);
        $result = static::$http_client->readGetResult('/config/', null, 'a9b215f1-e460-490c-8a0b-6d42c274d5e4');
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Config);
    }

    public function testMeta()
    {
        $result = static::$http_client->readGetResult('/config/');
        $this->assertTrue($result->getMeta() instanceof \BO\Zmsentities\Metaresult);
    }

    public function testTokenFailed()
    {
        $this->createHttpClient(null, false);
        static::$http_client->setUserInfo('noauth', 'noauth');
        $this->expectException('\BO\Zmsclient\Exception');
        $result = static::$http_client->readGetResult('/config/');
        $result->getEntity();
    }

    public function testWrongFormat()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $result = static::$http_client->readGetResult('/doc/swagger.json');
        $result->getEntity();
    }

    public function testUnknownUrl()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $result = static::$http_client->readGetResult('/unknownUri/');
        $result->getEntity();
    }

    public function testDeadlock()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $result = static::$http_client->readGetResult('/status/deadlock/');
        $result->getEntity();
    }

    /**
     * @runInSeparateProcess
     */
    public function testWithApiKey()
    {
        \BO\Zmsclient\Auth::removeKey();
        $this->createHttpClient(null, false);
        static::$http_client->setApiKey('unittest');
        $result = static::$http_client->readGetResult('/provider/dldb/122217/scopes/');
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Scope);
        $this->assertStringContainsString('unittest', $result->getResponse()->getHeaderline('x-api-key'));
    }

     /**
     * @runInSeparateProcess
     */
    public function testWithWorkflowKey()
    {
        $this->createHttpClient(null, false);
        static::$http_client->setApiKey('unittest');
        static::$http_client->setWorkflowKey('unittest');
        $result = static::$http_client->readGetResult('/process/status/free/');
        $collection = $result->getCollection();
        $this->assertStringContainsString('unittest', $result->getResponse()->getHeaderline('x-workflow-key'));
        $this->assertStringContainsString('unittest', $result->getResponse()->getHeaderline('x-api-key'));
    }
}
