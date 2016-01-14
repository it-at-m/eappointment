<?php

namespace BO\Zmsclient\Tests;

abstract class Base extends \PHPUnit_Framework_TestCase
{

    /**
     * @param \BO\Zmsclient\Psr7\ClientInterface $mockup Add a mockup if necessary
     *
     * @return \BO\Zmsclient\Http
     */
    protected function createHttpClient($mockup = null)
    {
        $http = new \BO\Zmsclient\Http('https://localhost/terminvereinbarung/api/2', $mockup);
        return $http;
    }
}
