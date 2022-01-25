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

    public function testCaptcha()
    {
        $entity = (new $this->entityclass())->getExample()->withCaptchaData('base64UnitTest');
        $this->assertStringContainsString('"mime":"image\/jpeg;base64"', (string)$entity);
        $this->assertTrue('base64UnitTest' === $entity->captcha->content);
        $hash = $entity->getHash('a2c4e6');
        $this->assertTrue($entity->isVerifiedHash('a2c4e6', $hash));
        $this->assertFalse($entity->isVerifiedHash('dummy', $hash));
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
