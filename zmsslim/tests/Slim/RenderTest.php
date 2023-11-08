<?php

namespace BO\Slim\Tests;

class RenderTest extends Base
{

    public function testHtml()
    {
        \BO\Slim\Render::$response = $this->getResponse("Test Response");
        $response = \BO\Slim\Render::html('unittest.twig');
        $this->assertStringContainsString('Test Response', (string)$response->getBody());
    }

    public function testJson()
    {
        \BO\Slim\Render::$response = $this->getResponse();
        $response = \BO\Slim\Render::json('{"unit":"test}');
        $this->assertStringContainsString('{\"unit\":\"test}', (string)$response->getBody());
    }

    public function testXml()
    {
        \BO\Slim\Render::$response = $this->getResponse();
        $response = \BO\Slim\Render::xml('<unit><test>works</test></unit>');
        $this->assertStringContainsString('<unit><test>works</test></unit>', (string)$response->getBody());
    }

    public function testLastModified()
    {
        \BO\Slim\Render::$response = $this->getResponse();
        $noCacheResponse = \BO\Slim\Render::lastModified(\App::$now->getTimestamp(), 300);
        $this->assertStringContainsString('max-age=300', (string)$noCacheResponse->getHeaderLine("Cache-Control"));
        $this->assertStringContainsString(
            'Fri, 01 Apr 2016 06:00:00 GMT',
            (string)$noCacheResponse->getHeaderLine("last-modified")
        );
    }

    public function testRedirect()
    {
        $response = \BO\Slim\Render::redirect('getroute', ['id' => 123], ['test' => 456]);
        $this->assertRedirect($response, '/unittest/123/?test=456');
    }
}
