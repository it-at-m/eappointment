<?php

namespace BO\Slim\Tests;

class PostTest extends Base
{

    protected $classname = "Post";
    protected $arguments = [ ];

    protected $parameters = [
        '__header' => array(
            'X-Api-Key' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs'
        ),
        '__body' => '{
            "message": "this is a post message"
        }'
    ];

    protected $sessionData = [ ];

    public function testRendering()
    {
        $response = $this->render([], $this->parameters, [], 'POST');
        $this->assertStringContainsString('POST test title', (string) $response->getBody());
        $this->assertStringContainsString('this is a post message', (string) $response->getBody());
    }
}
