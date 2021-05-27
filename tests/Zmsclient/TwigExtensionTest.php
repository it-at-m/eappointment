<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class TwigExtensionTest extends Base
{
    public function testBasic()
    {
        $mock = $this->createTwigMockup();
        $extension = new \BO\Zmsclient\TwigExtension($mock);

        foreach ($extension->getFunctions() as $twigFunction) {
            $this->assertInstanceOf('Twig_SimpleFunction', $twigFunction);
        };

        $this->assertEquals('bozmsclientExtension', $extension->getName());
        $this->assertStringContainsString('For debugging: This log contains HTTP calls.', $extension->dumpHttpLog());
    }
}
