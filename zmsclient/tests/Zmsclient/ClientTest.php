<?php

namespace BO\Zmsclient\Tests;

use BO\Zmsclient\Psr7\Client;
use BO\Zmsclient\Psr7\Request;
use BO\Zmsclient\Psr7\Uri;
use \BO\Mellon\Validator;
use BO\Zmsclient\Http;
use Fig\Http\Message\StatusCodeInterface;

class ClientTest extends Base
{
    /*public function testStatus()
    {
        $uri = new Uri(self::$http_baseurl . '/status/');
        $request = Http::createRequest('GET', $uri);
        $response = Client::readResponse($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $bodyContent = (string)$response->getBody();
        $body = Validator::value($bodyContent)->isJson();
        $this->assertFalse($body->hasFailed());
    }*/

    /*public function testStatusFailed()
    {
        $this->expectException('\BO\Zmsclient\Psr7\RequestException');
        $uri = new Uri(self::$http_baseurl . '/status/');
        $uri = $uri->withPort(4444);
        $request = Http::createRequest('GET', $uri);
        Client::readResponse($request);
    }*/
}
