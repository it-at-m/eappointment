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
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUpdateExistingApikey()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);

        $response = $this->render([], [
            '__body' => $entity
        ], []);
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
