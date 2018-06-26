<?php

namespace BO\Zmsentities\Tests;

class ApikeyTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Apikey';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertContains('"key":"wMdVa5Nu1seuC\/RSJxhKl2M3yw+8zqaAilPH2Xc2IZs"', (string)$entity);
    }

    public function testCaptcha()
    {
        $entity = (new $this->entityclass())->getExample()->withCaptchaData('base64UnitTest');
        $this->assertContains('"mime":"image\/jpeg;base64"', (string)$entity);
        $this->assertTrue('base64UnitTest' === $entity->captcha->content);

        $this->assertEquals(
            'a6a9b25f8e357c9104b81e2121be67fa79d',
            $entity->getHashFromCaptcha('a2c4e6', 'r3BddFfcnCKUadpTt5uthXnmtnKHxh')
        );
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
