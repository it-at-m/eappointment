<?php

namespace BO\Slim\Tests;

class RequestTest extends Base
{

    public function testRendering()
    {
        $request = $this->getRequest('GET', '/unittest/123/', $this->sessionData);
        $this->assertEquals('/unittest/123/', $request->getUri()->getPath());
        $this->assertStringContainsString('GET', (string)$request->getMethod());
    }

    public function testWithoutSessionData()
    {
        $request = $this->getRequest('GET', '/unittest/123/');
        $this->assertEquals('/unittest/123/', $request->getUri()->getPath());
        $this->assertStringContainsString('GET', (string)$request->getMethod());
    }

}
