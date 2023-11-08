<?php

namespace BO\Slim\Tests;

use BO\Slim\Request;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

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

    public function testGetBasePath()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/');
        $serverParams = [
            'REQUEST_URI' => '/admin/account/',
            'SCRIPT_NAME' => '/admin/index.php',
        ];
        $request = new Request('GET', $uri, new Headers([]), [], $serverParams, new Stream(fopen('php://temp', 'wb+')));

        self::assertSame('/admin', $request->getBasePath());
    }

    public function testGetBaseUrl()
    {
        $uri = new Uri('https', 'localhost', 80, '/admin/account/');
        $serverParams = [
            'REQUEST_URI' => '/admin/account/',
            'SCRIPT_NAME' => '/admin/index.php',
        ];
        $request = new Request('GET', $uri, new Headers([]), [], $serverParams, new Stream(fopen('php://temp', 'wb+')));

        self::assertSame('https://localhost:80/admin', $request->getBaseUrl());
    }
}
