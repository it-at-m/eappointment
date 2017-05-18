<?php

namespace BO\Zmsapi\Tests;

class IndexTest extends Base
{
    protected $classname = "Index";

    public function testRendering()
    {
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('swagger.json', (string) $response->getBody());
    }
}
