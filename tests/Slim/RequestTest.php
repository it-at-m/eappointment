<?php

namespace BO\Slim\Tests;

class RequestTest extends Base
{

    public function testRendering()
    {
        $request = $this->getRequest('GET', '/unittest/123/', $this->sessionData);
        $this->assertEquals('/unittest/123/', $request->getUri()->getPath());
        $this->assertStringContainsString('GET', (string)$request->getMethod());
        $this->assertEquals('unittest', $request->getAttribute('session')->get('source', 'entry'));
        $this->assertEquals('100005', $request->getAttribute('session')->get('process', 'basket'));
        $this->assertEquals('reserved', $request->getAttribute('session')->get('status'));
    }

    public function testWithoutSessionData()
    {
        $request = $this->getRequest('GET', '/unittest/123/');
        $this->assertEquals('/unittest/123/', $request->getUri()->getPath());
        $this->assertStringContainsString('GET', (string)$request->getMethod());
    }

    public function testCookie()
    {
        $method = 'GET';
        $request = $this->getRequest($method, '/unittest/123/', $this->sessionData);
        $request = $this->setRequestParameters($request, [
            '__cookie' => [
                'unittest' => 'activated',
            ]
        ], $method);
        $this->assertEquals('activated', $request->getCookieParam('unittest'));
    }

    public function testUserInfo()
    {
        $method = 'GET';
        $request = $this->getRequest($method, '/unittest/123/', $this->sessionData);
        $request = $this->setRequestParameters($request, [
            '__userinfo' => [
                'username' => 'unit',
                'password' => 'test'
            ]
        ], $method);
        $this->assertEquals('unit:test', $request->getUri()->getUserInfo());
    }

    public function testFile()
    {
        $method = 'GET';
        $request = $this->getRequest($method, '/unittest/123/', $this->sessionData);
        $request = $this->setRequestParameters($request, [
            '__file' => ['./lang/de.json']
        ], $method);
        $this->assertEquals('./lang/de.json', $request->getUploadedFiles()[0]);
    }
}
