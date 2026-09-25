<?php

namespace BO\Zmsbackend\Tests\Apikey\Api;

use \BO\Zmsbackend\Apikey\Service\Apikey as Query;
use \BO\Zmsbackend\Tests\ExampleApikey;

class ApikeyUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ApikeyUpdate";

    public function testRendering()
    {
        $example = ExampleApikey::create(static::class . '::' . $this->name());
        $input = json_encode($example);
        $response = $this->render([], [
            '__body' => $input
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString($example->key, (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUpdateExistingApikey()
    {
        $input = ExampleApikey::create(static::class . '::' . $this->name());
        $entity = (new Query())->writeEntity($input);

        $response = $this->render([], [
            '__body' => (string) $entity
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString($input->key, (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClientkey()
    {
        $example = ExampleApikey::create(static::class . '::' . $this->name());
        $input = json_encode($example);
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => 'default',
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString($example->key, (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClientkeyBlocked()
    {
        $this->expectException('BO\Zmsbackend\Process\Exception\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $input = json_encode(ExampleApikey::create(static::class . '::' . $this->name()));
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE',
        ], []);
    }

    public function testClientkeyInvalid()
    {
        $this->expectException('BO\Zmsbackend\Process\Exception\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $input = json_encode(ExampleApikey::create(static::class . '::' . $this->name()));
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => '_invalid',
        ], []);
    }
}
