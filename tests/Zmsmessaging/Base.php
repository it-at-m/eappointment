<?php

namespace BO\Zmsmessaging\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;

abstract class Base extends \BO\Zmsmessaging\PhpUnit\Base
{
    protected function setWorkstation()
    {
        User::$workstation = new Workstation([
            'id' => 5118,
            'useraccount' => new Useraccount([
                'id' => '_system_messenger',
            ])
        ]);
        return User::$workstation;
    }

    protected function getResponse($content = '', $status = 200)
    {
        $response = new \BO\Zmsclient\Psr7\Response();
        $response->withStatus($status);
        $response->getBody()->write($content);
        return $response;
    }

    protected function getRequest($method = "GET", $uri = '')
    {
        $request = new \BO\Zmsclient\Psr7\Request($method, new \BO\Zmsclient\Psr7\Uri($uri));
        return $request;
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
