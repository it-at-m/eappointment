<?php

namespace BO\Zmsmessaging\Tests;

use \Prophecy\Argument;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    protected $namespace = '\\BO\\Zmsmessaging\\';

    protected $apiCalls = array ();

    protected function getApiMockup()
    {
        $mock = $this->prophesize('BO\Zmsclient\Http');
        foreach ($this->getApiCalls() as $options) {
            $parameters = isset($options['parameters']) ? $options['parameters'] : null;
            $function = $options['function'];
            if ($function == 'readGetResult' || $function == 'readDeleteResult') {
                $function = $mock->__call(
                    $function,
                    [
                        $options['url'],
                        $parameters
                    ]
                );
            } elseif ($function == 'readPostResult') {
                $function = $mock->__call(
                    $function,
                    [
                        $options['url'],
                        Argument::type('\BO\Zmsentities\Schema\Entity'),
                        $parameters
                    ]
                );
            }
            $function->shouldBeCalled()
            ->willReturn(
                new \BO\Zmsclient\Result(
                    $this->getResponse($options['response']),
                    $this->getRequest()
                )
            );
        }
        $api = $mock->reveal();
        return $api;
    }

    protected function getResponse($content = '', $status = 200)
    {
        $response = new \BO\Zmsclient\Psr7\Response();
        $response->withStatus($status);
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * Overwrite this function if api calls definition needs function calls
     */
    protected function getApiCalls()
    {
        return $this->apiCalls;
    }

    protected function getRequest($method = "GET", $uri = '')
    {
        $request = new \BO\Zmsclient\Psr7\Request();
        return $request
            ->withMethod($method)
            ->withUri(new \BO\Zmsclient\Psr7\Uri($uri));
    }

    public function setUp()
    {
        \App::$http = $this->getApiMockup();
    }


    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/fixtures/' . $filename;
        if (! is_readable($path) || ! is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }
}
