<?php

namespace BO\Zmsstatistic\Tests;

class ChangelogTest extends Base
{
    protected $classname = "Changelog";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $response = $this->render([ ], ['__uri' => '/changelog'], [ ]);
        $this->assertStringContainsString('Changelog', (string) $response->getBody());
    }
}
