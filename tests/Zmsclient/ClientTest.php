<?php

namespace BO\Zmsclient\Tests;

use GuzzleHttp\Psr7\Request;
use BO\Zmsclient\Psr7\Client;

class ClientTest extends Base
{
    public function testStatus()
    {
        $request = new Request('GET', 'https://localhost/terminvereinbarung/api/2/status/');
        $response = Client::request($request);
        echo((string)$response->getBody());
    }
}
