<?php

namespace BO\Zmsbackend\Tests\Apikey\Api;

use \BO\Zmsbackend\Apikey\Service\Apikey as Query;
use \BO\Zmsbackend\Tests\ExampleApikey;

class ApikeyGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ApikeyGet";

    public function testRendering()
    {
        $input = ExampleApikey::create(static::class);
        (new Query())->writeEntity($input);

        $response = $this->render(['key' => $input->key], [], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString('"route":"/calendar/"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('BO\Zmsbackend\Apikey\Exception\ApiKeyNotFound');
        $this->render(['key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'], [], []);
    }
}
