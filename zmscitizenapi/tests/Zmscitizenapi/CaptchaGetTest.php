<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\CaptchaGet;

class CaptchaGetTest extends Base
{
    protected $classname = "CaptchaGet";

    public function setUp(): void
    {
        parent::setUp();

        putenv('FRIENDLYCAPTCHA_SITEKEY=FAKE_SITE_KEY');
        putenv('FRIENDLYCAPTCHA_ENDPOINT=https://api.friendlycaptcha.com/api/v1/siteverify');
        putenv('FRIENDLYCAPTCHA_ENDPOINT_PUZZLE=https://api.friendlycaptcha.com/api/v1/puzzle');
        putenv('CAPTCHA_ENABLED=1');

        Application::initialize();
    }

    public function tearDown(): void
    {
        putenv('FRIENDLYCAPTCHA_SITEKEY=');
        putenv('FRIENDLYCAPTCHA_ENDPOINT=');
        putenv('FRIENDLYCAPTCHA_ENDPOINT_PUZZLE=');
        putenv('CAPTCHA_ENABLED=');
        
        parent::tearDown();
    }

    public function testCaptchaDetails()
    {
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
