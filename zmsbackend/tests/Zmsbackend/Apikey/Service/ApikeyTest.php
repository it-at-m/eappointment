<?php

namespace BO\Zmsbackend\Tests\Apikey\Service;

use \BO\Zmsbackend\Apikey\Service\Apikey as Query;
use \BO\Zmsbackend\Apiquota\Service\Apiquota as QuotaQuery;
use \BO\Zmsbackend\Tests\ExampleApikey;

class ApikeyTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $query->deleteEntity($input->key);
        $entity = $query->writeEntity($input);
        $this->assertEntity("\\BO\\Zmsentities\\Apikey", $entity);

        $entity->updateQuota(0);
        $entity = $query->updateEntity($input->key, $entity);
        $this->assertEquals(100, $entity->quota[0]['requests']);
        $query->deleteEntity($input->key);
        $this->assertFalse($query->readEntity($input->key)->hasId());
    }

    public function testQuota()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);

        $quotaRequests = $query->readQuota($input->key, '/calendar/')['requests'];
        $this->assertEquals(99, $quotaRequests);

        $query->writeQuota($input->key, '/process/free/', 'hour', 3);
        $entity = $query->readEntity($input->key);
        $this->assertEquals(2, count($entity->quota));

        $newQuotaPos = $entity->getQuotaPositionByRoute('/process/free/');
        $entity->updateQuota($newQuotaPos);
        $query->updateQuota($input->key, $entity);
        $entity = $query->readEntity($input->key);
        $this->assertEquals(4, $entity->quota[$newQuotaPos]['requests']);
    }

    public function testQuotaExpired()
    {
        $now = (new \DateTimeImmutable())->modify('+ 1 Hour');
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);
        $expiredQuota = $query->readExpiredQuotaListByPeriod($now);
        $this->assertTrue($now->getTimestamp() === ($expiredQuota[0]['ts'] + 3600)); //expired 1 hour

        $query->writeDeletedQuota($entity->quota[0]['quotaid']);
        $entity = $query->readEntity($input->key);
        $this->assertEquals(0, count($entity->quota));
    }

    protected function getTestEntity()
    {
        return ExampleApikey::create(static::class . '::' . $this->name());
    }
}
