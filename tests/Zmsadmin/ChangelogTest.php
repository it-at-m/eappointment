<?php

namespace BO\Zmsadmin\Tests;

class ChangelogTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Changelog";

    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Changelog', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
