<?php

namespace BO\Zmsstatistic\Tests;

class DialogHandlerTest extends Base
{
    protected $arguments = [];

    protected $parameters = ['template' => 'force_https'];

    protected $classname = "\BO\Zmsstatistic\Helper\DialogHandler";

    public function testRendering()
    {
        $response = $this->render([], $this->parameters, []);
        $this->assertStringContainsString('Unsicheres Protokoll verwendet', (string)$response->getBody());
        $this->assertStringContainsString(
            'Die Nutzung dieser Seiten ist ausschließlich mit dem sicheren HTTPS-Protokoll gestattet.',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('data-action-ok', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
