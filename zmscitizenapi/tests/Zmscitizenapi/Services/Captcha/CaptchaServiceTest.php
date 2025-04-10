<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Security;

use BO\Zmscitizenapi\Services\Captcha\CaptchaService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CaptchaServiceTest extends TestCase
{
    private CaptchaService $captchaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->captchaService = new CaptchaService();
    }

    public function testGetCaptchaReturnsCaptchaDetails(): void
    {
        // Act
        $result = $this->captchaService->getCaptchaDetails();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('siteKey', $result);
        $this->assertArrayHasKey('captchaChallenge', $result);
        $this->assertArrayHasKey('captchaVerify', $result);
        $this->assertArrayHasKey('captchaEnabled', $result);
    }

    public function testGetCaptchaReturnsExpectedValues(): void
    {
        // Arrange
        $expectedSiteKey = \App::$ALTCHA_CAPTCHA_SITE_KEY;
        $expectedChallengeUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE;
        $expectedVerifyUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_VERIFY;
        $expectedEnabled = \App::$CAPTCHA_ENABLED;

        // Act
        $result = $this->captchaService->getCaptchaDetails();

        // Assert
        $this->assertEquals($expectedSiteKey, $result['siteKey']);
        $this->assertEquals($expectedChallengeUrl, $result['captchaChallenge']);
        $this->assertEquals($expectedVerifyUrl, $result['captchaVerify']);
        $this->assertEquals($expectedEnabled, $result['captchaEnabled']);
    }
}