<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Captcha;

use BO\Zmscitizenapi\Tests\ControllerTestCase;

class CaptchaControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Captcha\CaptchaController";

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

    public function testCaptchaDetails()
    {
        $captchaEnabled = filter_var(getenv('CAPTCHA_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        $parameters = [];
        $response = $this->render([], $parameters, [], 'GET');
        $responseBody = json_decode((string)$response->getBody(), true);

        $expectedResponse = [
            'siteKey' => 'FAKE_SITE_KEY',
            'captchaChallenge' => 'https://captcha-k.muenchen.de/api/v1/captcha/challenge',
            'captchaVerify' => 'https://captcha-k.muenchen.de/api/v1/captcha/verify',
            'captchaEnabled' => true
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
