<?php

namespace BO\Zmscitizenapi\Tests;

use \BO\Zmscitizenapi\Application;

class CaptchaGetTest extends Base
{
    protected $classname = "CaptchaGet";

    public function setUp(): void
    {
        parent::setUp();

        putenv('CAPTCHA_SITEKEY=FAKE_SITE_KEY');
        putenv('CAPTCHA_ENDPOINT=https://api.friendlycaptcha.com/api/v1/siteverify');
        putenv('CAPTCHA_ENDPOINT_PUZZLE=https://api.friendlycaptcha.com/api/v1/puzzle');
        putenv('CAPTCHA_ENABLED=1');

        Application::initialize();
    }

    public function tearDown(): void
    {
        putenv('CAPTCHA_SITEKEY=');
        putenv('CAPTCHA_ENDPOINT=');
        putenv('CAPTCHA_ENDPOINT_PUZZLE=');
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
            'captchaEnabled' => true,
            'status' => 200
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
