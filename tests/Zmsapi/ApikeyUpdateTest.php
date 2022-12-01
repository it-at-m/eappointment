<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Apikey as Entity;

use \BO\Zmsdb\Apikey as Query;

class ApikeyUpdateTest extends Base
{
    protected $classname = "ApikeyUpdate";

    public function testRendering()
    {
        $input = json_encode((new Entity)->createExample());
        $response = $this->render([], [
            '__body' => $input
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUpdateExistingApikey()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);

        $response = $this->render([], [
            '__body' => (string) $entity
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClientkey()
    {
        $input = json_encode((new Entity)->createExample());
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => 'default',
        ], []);
        $this->assertStringContainsString('apikey.json', (string)$response->getBody());
        $this->assertStringContainsString('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClientkeyBlocked()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $input = json_encode((new Entity)->createExample());
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE',
        ], []);
    }

    public function testClientkeyInvalid()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $input = json_encode((new Entity)->createExample());
        $response = $this->render([], [
            '__body' => $input,
            'clientkey' => '_invalid',
        ], []);
    }
}
