<?php

namespace BO\Zmsbackend\Tests\Apikey\Api;

use \BO\Zmsbackend\Apikey\Service\Apikey as Query;
use \BO\Zmsbackend\Tests\ExampleApikey;

class ApikeyDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ApikeyDelete";

    #[\Override]
    public function testRendering()
    {
        $input = ExampleApikey::create(static::class . '::' . $this->name());
        (new Query())->writeEntity($input);
        $response = $this->render(['key' => $input->key], [], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
