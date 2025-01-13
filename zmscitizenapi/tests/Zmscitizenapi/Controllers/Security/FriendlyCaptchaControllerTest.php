<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Security;

use BO\Zmscitizenapi\Tests\ControllerTestCase;

use BO\Zmscitizenapi\Models\Captcha\FriendlyCaptcha;

class FriendlyCaptchaControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Security\CaptchaController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        putenv('FRIENDLY_CAPTCHA_SITE_KEY=FAKE_SITE_KEY');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT=https://api.friendlycaptcha.com/api/v1/siteverify');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE=https://api.friendlycaptcha.com/api/v1/puzzle');
        putenv('CAPTCHA_ENABLED=1');

        \App::initialize();
    }

    public function tearDown(): void
    {
        putenv('FRIENDLY_CAPTCHA_SITEKEY=');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT=');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE=');
        putenv('CAPTCHA_ENABLED=');

        parent::tearDown();
    }

    public function testCaptchaDetails()
    {
        $captchaEnabled = filter_var(getenv('CAPTCHA_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        $parameters = [];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $expectedResponse = [
            'siteKey' => 'FAKE_SITE_KEY',
            'captchaEndpoint' => 'https://api.friendlycaptcha.com/api/v1/siteverify',
            'puzzle' => 'https://api.friendlycaptcha.com/api/v1/puzzle',
            'captchaEnabled' => true
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testVerifyCaptchaSuccess()
    {
        // Mock the HTTP client to return a successful response
        $mockResponse = new \GuzzleHttp\Psr7\Response(200, [], json_encode(['success' => true]));
        \App::$http = new \GuzzleHttp\Client(['handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([$mockResponse]))]);
    
        $captcha = new FriendlyCaptcha();
        $result = $captcha->verifyCaptcha('valid_solution');
        $this->assertTrue($result);
    }
    
    public function testVerifyCaptchaFailure()
    {
        // Mock the HTTP client to return a failure response
        $mockResponse = new \GuzzleHttp\Psr7\Response(200, [], json_encode(['success' => false]));
        \App::$http = new \GuzzleHttp\Client(['handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([$mockResponse]))]);
    
        $captcha = new FriendlyCaptcha();
        $result = $captcha->verifyCaptcha('invalid_solution');
        $this->assertFalse($result);
    }
    
    public function testVerifyCaptchaException()
    {
        // Mock the HTTP client to throw an exception
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Exception\RequestException('Error Communicating with Server', new \GuzzleHttp\Psr7\Request('POST', 'test'))
        ]);
        \App::$http = new \GuzzleHttp\Client(['handler' => \GuzzleHttp\HandlerStack::create($mockHandler)]);
    
        $captcha = new FriendlyCaptcha();
        $result = $captcha->verifyCaptcha('exception_solution');
        $this->assertFalse($result);
    }

}
