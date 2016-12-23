<?php

namespace BO\Zmsclient\Tests;

abstract class Base extends \PHPUnit_Framework_TestCase
{

    /**
     * @var String $http_baseurl URL to test lib against
     */
    public static $http_baseurl = null;

    protected $sessionName = 'Zmsappointment';
    protected $sessionAttribute = 'session';

    /**
     * @param \BO\Zmsclient\Psr7\ClientInterface $mockup Add a mockup if necessary
     *
     * @return \BO\Zmsclient\Http
     */
    protected function createHttpClient($mockup = null)
    {
        $http = new \BO\Zmsclient\Http($this::$http_baseurl, $mockup);
        return $http;
    }

    protected function createSession()
    {
        $http = $this->createHttpClient();
        return new \BO\Zmsclient\SessionHandler($http);
    }
}
