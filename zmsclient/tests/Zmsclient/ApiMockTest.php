<?php

namespace BO\Zmsclient\Tests;

use BO\Zmsclient\Psr7\Request;
use BO\Zmsclient\Psr7\Uri;

/**
 * @runTestsInSeparateProcesses
 *
 */
class ApiMockTest extends \BO\Zmsclient\PhpUnit\Base
{

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("status.json")
                ]
            ]
        );

        $result = \App::$http->readGetResult('/status/');
        $response = new \BO\Zmsclient\Psr7\Response();
        $response->getBody()->write($this->readFixture("status.json"));
        $response = $response->withHeader('Content-Type', 'application/json');
        $this->assertStringContainsString('status.json', (string)$response->getBody());
        $this->assertStringContainsString('database', (string)$response->getBody());
    }

    public function testFunctionMock()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'setApiKey',
                    'parameters' => 'unittest'
                ]
            ]
        );

        \App::$http->setApiKey('unittest');
    }

    public function testException()
    {
        $this->expectException('\BO\Zmsclient\Exception\ApiFailed');
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'exception' => new \BO\Zmsclient\Exception\ApiFailed
                ]
            ]
        );

        $result = \App::$http->readGetResult('/status/');
    }
 
    public function testWithGraphQL()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'parameters' => [
                        'gql' => '{version}'
                    ],
                    'response' => $this->readFixture("status.json")
                ]
            ]
        );

        $result = \App::$http->readGetResult('/status/', ['gql' => '{version}']);
        $response = $result->getResponse();
        $this->assertStringContainsString('"major":"2"', (string)$response->getBody());
        $this->assertStringNotContainsString('database', (string)$response->getBody());
    }

    public function testPost()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("status.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("status.json")
                ]
            ]
        );

        $entity = new \BO\Zmsentities\Status(json_decode($this->readFixture("status.json")));
        $result = \App::$http->readPostResult('/status/', $entity);
        $response = $result->getResponse();
        $this->assertStringContainsString('status.json', (string)$response->getBody());
        $this->assertStringContainsString('database', (string)$response->getBody());

        $result = \App::$http->readDeleteResult('/status/');
        $response = $result->getResponse();
        $this->assertStringContainsString('status.json', (string)$response->getBody());
    }

    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/../mockup/config/' . $filename;
        if (! is_readable($path) || ! is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }
}
