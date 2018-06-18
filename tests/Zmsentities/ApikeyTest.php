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
        $this->assertTrue("base64UnitTest" === $entity->captcha->content);
    }
}
