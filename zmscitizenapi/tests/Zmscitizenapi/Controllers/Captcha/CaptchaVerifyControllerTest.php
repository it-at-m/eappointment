<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Captcha;

use BO\Zmscitizenapi\Tests\ControllerTestCase;
use BO\Zmscitizenapi\Models\Captcha\AltchaCaptcha;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class CaptchaVerifyControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Captcha\CaptchaVerifyController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        putenv('ALTCHA_CAPTCHA_SITE_KEY=FAKE_SITE_KEY');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE=https://captcha-k.muenchen.de/api/v1/captcha/challenge');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_VERIFY=https://captcha-k.muenchen.de/api/v1/captcha/verify');
        putenv('CAPTCHA_ENABLED=1');

        \App::initialize();
    }

    public function tearDown(): void
    {
        putenv('ALTCHA_CAPTCHA_SITE_KEY=');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_VERIFY=');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE=');
        putenv('CAPTCHA_ENABLED=');

        parent::tearDown();
    }

    public function testRendering()
    {
        $parameters = [
            'payload' => base64_encode(json_encode(['challenge' => 'abcdefg0123456789']))
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $this->assertEquals(200, $response->getStatusCode());
        return $response;
    }

    public function testVerifySolutionSuccess()
    {
        $expectedResponse = [
            'meta' => ['success' => true],
            'data' => [
                'meta' => ['success' => true],
                'data' => ['valid' => true],
            ],
            'token' => 'eyJpcCI6IjE...UqUoHoUk='
        ];

        $mockResponse = new Response(200, [], json_encode($expectedResponse['data']));

        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function verifySolution(?string $payload): array
            {
                return parent::verifySolution($payload);
            }
        };

        $payload = base64_encode(json_encode(['challenge' => 'abcdefg0123456789']));
        $result = $captcha->verifySolution($payload);

        $this->assertEquals($expectedResponse['meta'], $result['meta']);
        $this->assertEquals($expectedResponse['data'], $result['data']);
        $this->assertArrayHasKey('token', $result);
        $this->assertNotEmpty($result['token']);

    }

    public function testVerifySolutionInvalidJson()
    {
        $mockResponse = new Response(200, [], 'INVALID_JSON');
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function verifySolution(?string $payload): array
            {
                return parent::verifySolution($payload);
            }
        };

        $payload = base64_encode(json_encode(['challenge' => 'abcdefg0123456789']));
        $result = $captcha->verifySolution($payload);

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Antwort vom Captcha-Service ist kein gültiges JSON', $result['meta']['error']);
        $this->assertNull($result['data']);
    }

    public function testVerifySolutionMissingPayload()
    {
        $mockResponse = new Response(400, [], json_encode([
            'meta' => [
                'success' => false,
                'error' => 'Keine Payload übergeben'
            ],
            'data' => null
        ]));
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function verifySolution(?string $payload): array
            {
                return parent::verifySolution($payload);
            }
        };

        $payload = null;
        $result = $captcha->verifySolution($payload);

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Keine Payload übergeben', $result['meta']['error']);
        $this->assertNull($result['data']);
    }

    public function testVerifySolutionException()
    {
        $mockHandler = new MockHandler([ 
            new \GuzzleHttp\Exception\ConnectException(
                'Connection refused',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            )
        ]);
        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function verifySolution(?string $payload): array
            {
                return parent::verifySolution($payload);
            }
        };

        $payload = base64_encode(json_encode(['challenge' => 'abcdefg0123456789']));
        $result = $captcha->verifySolution($payload);

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Connection refused', $result['meta']['error']);
        $this->assertNull($result['data']);
    }

}
