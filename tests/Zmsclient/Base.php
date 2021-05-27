<?php

namespace BO\Zmsclient\Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{

    /**
     * @var String $http_baseurl URL to test lib against
     */
    public static $http_baseurl = null;

    protected static $http_client = null;

    public function setUp(): void
    {
        $this->createHttpClient();
    }

    public function tearDown(): void
    {
        $this->writeTestLogout();
        static::$http_client = null;
    }

    public function createHttpClient($mockup = null, $withUser = true)
    {
        static::$http_client = new \BO\Zmsclient\Http($this::$http_baseurl, $mockup);
        if ($withUser) {
            static::$http_client->setUserInfo('_system_soap', 'zmssoap');
        }
    }

    protected function writeTestLogout()
    {
        static::$http_client->readDeleteResult('/workstation/login/_system_soap/');
    }

    protected function createSession()
    {
        return new \BO\Zmsclient\SessionHandler(static::$http_client);
    }

    protected function createTwigMockup()
    {
        return new \Twig_Environment($this->getMockBuilder('Twig_LoaderInterface')->getMock());
    }
}
