<?php

namespace BO\Zmsentities\Tests;

class ApikeyTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Apikey';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertEquals('default', $entity->getApiClient()['shortname']);
        $this->assertEquals('hDUWCqKHuVbV2Yj9Dgc8hYwfAgJs3aTM', $entity->getApiClient()['clientKey']);
    }

    public function testSetApiclient()
    {
        $entity = (new $this->entityclass())->getExample();
        $apiclient = (new \BO\Zmsentities\Apiclient)->getExample();
        $entity->setApiClient($apiclient);
        $this->assertEquals('example', $entity->getApiClient()['shortname']);
        $this->assertEquals('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', $entity->getApiClient()['clientKey']);
    }

    public function testQuota()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addQuota('/unittest/', 'hour');
        $position = $entity->getQuotaPositionByRoute('/unittest/');
        $this->assertEquals(1, $position);
        $this->assertEquals(1, $entity->quota[$position]['requests']);

        $entity->updateQuota($position);
        $this->assertEquals(2, $entity->quota[$position]['requests']);
    }
}
