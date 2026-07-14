<?php

namespace BO\Zmsbackend\Tests\Apikey\Api;

use \BO\Zmsbackend\Apikey\Service\Apikey as Query;

use \BO\Zmsentities\Apikey as Entity;

class ApikeyDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ApikeyDelete";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $response = $this->render(['key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'], [], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
