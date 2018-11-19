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
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('"route":"/calendar/"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $response = $this->render(['key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'], [], []);
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('Not found', (string)$response->getBody());
        $this->assertTrue(404 == $response->getStatusCode());
    }
}
