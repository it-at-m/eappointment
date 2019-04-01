<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class HttpTest extends Base
{
    public function testStatus()
    {
        $result = static::$http_client->readGetResult('/status/');
        $response = new \BO\Zmsclient\Psr7\Response();
        $status = $result->getEntity();
        $response = \BO\Zmsclient\Status::testStatus($response, $status);
        $this->assertTrue($status instanceof \BO\Zmsentities\Schema\Entity);
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

        $response = \BO\Zmsclient\Status::testStatus($response, $status);
        $this->assertContains('Oldest mail with age in seconds: 400s', (string)$response->getBody());
        $this->assertContains('Oldest sms with age in seconds: 400s', (string)$response->getBody());
        $this->assertContains('DB connection without replication log detected', (string)$response->getBody());
        $this->assertContains('DB connection is not part of a galera cluster', (string)$response->getBody());
        $this->assertContains('High amount of DB-Locks: 11', (string)$response->getBody());
        $this->assertContains('High amount of DB-Threads: 31', (string)$response->getBody());
        $this->assertContains('DB connected thread over 50% of available connections', (string)$response->getBody());
        $this->assertContains('Last DLDB Import is more then 2 hours ago', (string)$response->getBody());
        $this->assertContains('slot calculation is 14400 seconds old', (string)$response->getBody());

        $status['sources']['dldb']['last'] = (new \DateTimeImmutable())->modify('- 6 hour')->format('Y-m-d H:i:s');
        $response = \BO\Zmsclient\Status::testStatus($response, $status);
        $this->assertContains('Last DLDB Import is more then 4 hours ago', (string)$response->getBody());
    }

    public function testStatusFailed()
    {
        $closure = function () {
            throw new \Exception('Status failed');
        };
        $response = new \BO\Zmsclient\Psr7\Response();
        $response = \BO\Zmsclient\Status::testStatus($response, $closure);
        $this->assertContains('Status failed', (string)$response->getBody());
    }

    public function testCollection()
    {
        $now = new \DateTimeImmutable();
        $calendar = new \BO\Zmsentities\Calendar();
        $calendar->setFirstDayTime($now);
        $calendar->setLastDayTime($now);
        $calendar->addScope("141");
        $result = static::$http_client->readGetResult('/scope/');
        $collection = $result->getCollection();
        $this->assertContains('141', $result->getIds());
        $this->assertTrue($collection instanceof \BO\Zmsentities\Collection\Base);
    }

    public function testMails()
    {
        $now = (new \DateTimeImmutable('2016-04-04'));
        $entity = \BO\Zmsentities\Mail::createExample();
        
        $confirmedProcess = static::$http_client->readGetResult('/scope/141/process/'. $now->format('Y-m-d') .'/')
            ->getCollection()
            ->toQueueList($now)
            ->withStatus(['confirmed'])
            ->toProcessList()
            ->getFirst();
        $entity->process = static::$http_client
            ->readGetResult(
                '/process/'. $confirmedProcess->getId() .'/'. $confirmedProcess->getAuthKey() .'/',
                ['resolveReferences' => 0]
            )->getEntity();
            
        $result = static::$http_client->readPostResult('/mails/', $entity, ['resolveReferences' => 0]);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
        $mailId = $entity->id;

        $result = static::$http_client->readGetResult('/mails/');
        $data = $result->getData();
        $this->assertTrue($data[0] instanceof \BO\Zmsentities\Mail);

        $result = static::$http_client->readDeleteResult("/mails/$mailId/", ['resolveReferences' => 0]);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);
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
        $result = static::$http_client->readGetResult('/config/', null, 'a9b215f1-e460-490c-8a0b-6d42c274d5e4');
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Config);
    }

    public function testMeta()
    {
        $result = static::$http_client->readGetResult('/config/', null, 'a9b215f1-e460-490c-8a0b-6d42c274d5e4');
        $this->assertTrue($result->getMeta() instanceof \BO\Zmsentities\Metaresult);

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
}
