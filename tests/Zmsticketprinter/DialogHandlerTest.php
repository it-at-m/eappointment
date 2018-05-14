<?php

namespace BO\Zmsticketprinter\Tests;

class DialogHandlerTest extends Base
{
    protected $arguments = [];

    protected $parameters = ['template' => 'force_https'];

    protected $classname = "\BO\Zmsticketprinter\Helper\DialogHandler";

    public function testRendering()
    {
        $response = $this->render([], $this->parameters, []);
        $this->assertContains('Unsicheres Protokoll verwendet', (string)$response->getBody());
        $this->assertContains(
            'Die Nutzung dieser Seiten ist ausschließlich mit dem sicheren HTTPS-Protokoll gestattet.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
