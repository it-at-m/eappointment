<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Captcha;

use BO\Zmscitizenapi\Tests\ControllerTestCase;
use BO\Zmscitizenapi\Models\Captcha\AltchaCaptcha;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class CaptchaChallengeControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Captcha\CaptchaChallengeController";

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

    public function testCreateChallengeSuccess()
    {
        $expectedChallenge = [
            'meta' => ['success' => true],
            'data' => [
                'algorithm' => 'SHA-256',
                'challenge' => 'abcdefg0123456789',
                'maxnumber' => 1000,
                'salt' => '0123456789',
                'signature' => 'abcdefg0123456789',
                'signature' => 'abcdefg0123456789',
            ]
        ];

        $mockResponse = new Response(200, [], json_encode(['challenge' => $expectedChallenge]));

        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function createChallenge(): array
            {
                return parent::createChallenge();
            }
        };

        $result = $captcha->createChallenge();

        $this->assertEquals($expectedChallenge, $result);
    }

    public function testCreateChallengeInvalidJson()
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

            public function createChallenge(): array
            {
                return parent::createChallenge();
            }
        };

        $result = $captcha->createChallenge();

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Fehler beim Dekodieren', $result['meta']['error']);
        $this->assertNull($result['data']);
    }

    public function testCreateChallengeMissingChallenge()
    {
        $mockResponse = new Response(200, [], json_encode(['no_challenge_here' => true]));
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $captcha = new class($mockClient) extends AltchaCaptcha {
            public function __construct($client)
            {
                parent::__construct();
                $this->httpClient = $client;
            }

            public function createChallenge(): array
            {
                return parent::createChallenge();
            }
        };

        $result = $captcha->createChallenge();

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Challenge-Daten fehlen', $result['meta']['error']);
        $this->assertNull($result['data']);
    }

    public function testCreateChallengeException()
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

            public function createChallenge(): array
            {
                return parent::createChallenge();
            }
        };

        $result = $captcha->createChallenge();

        $this->assertFalse($result['meta']['success']);
        $this->assertStringContainsString('Connection refused', $result['meta']['error']);
        $this->assertNull($result['data']);
    }
}
