<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsdb\Apikey as Query;

use \BO\Zmsentities\Apikey as Entity;

class ApikeyGetTest extends Base
{
    protected $classname = "ApikeyGet";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);

        $response = $this->render(['key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'], [], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString('"route":"/calendar/"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('BO\Zmsapi\Exception\Apikey\ApiKeyNotFound');
        $this->render(['key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'], [], []);
    }
}
