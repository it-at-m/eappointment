<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Apikey as Query;
use \BO\Zmsdb\Apiquota as QuotaQuery;
use \BO\Zmsentities\Apikey as Entity;

class ApikeyTest extends Base
{
    public function testBasic()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $query->deleteEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs');
        $entity = $query->writeEntity($input);
        $this->assertEntity("\\BO\\Zmsentities\\Apikey", $entity);

        $entity->updateQuota(0);
        $entity = $query->updateEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', $entity);
        $this->assertEquals(100, $entity->quota[0]['requests']);
        $query->deleteEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs');
        $this->assertFalse($query->readEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs')->hasId());
    }

    public function testQuota()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);

        $quotaRequests = $query->readQuota('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', '/calendar/')['requests'];
        $this->assertEquals(99, $quotaRequests);

        $query->writeQuota('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', '/process/free/', 'hour', 3);
        $entity = $query->readEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs');
        $this->assertEquals(2, count($entity->quota));

        $newQuotaPos = $entity->getQuotaPositionByRoute('/process/free/');
        $entity->updateQuota($newQuotaPos);
        $query->updateQuota('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', $entity);
        $entity = $query->readEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs');
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
        $entity = $query->readEntity('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs');
        $this->assertEquals(0, count($entity->quota));
    }

    protected function getTestEntity()
    {
        return (new Entity())->createExample();
    }
}
