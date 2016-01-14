<?php

namespace BO\Zmsclient\Tests;

use BO\Zmsclient\Psr7\Client;
use BO\Zmsclient\Psr7\Request;
use BO\Zmsclient\Psr7\Uri;
use \BO\Mellon\Validator;

class ClientTest extends Base
{
    public function testStatus()
    {
        $uri = new Uri('https://localhost/terminvereinbarung/api/2/status/');
        $request = new Request('GET', $uri);
        $response = Client::readResponse($request);
        $bodyContent = (string)$response->getBody();
        $body = Validator::value($bodyContent)->isJson();
        $this->assertFalse($body->hasFailed());
    }
}
