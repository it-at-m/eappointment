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
        $input = (new Query())->readEntity('79cc69c11550f5558a0c0da3f6a055cd53c');
        $response = $this->render([], [
            '__body' => $input
        ], []);
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('79cc69c11550f5558a0c0da3f6a055cd53c', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
