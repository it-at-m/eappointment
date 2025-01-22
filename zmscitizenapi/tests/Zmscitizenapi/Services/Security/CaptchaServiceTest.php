<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Security;

use BO\Zmscitizenapi\Services\Security\CaptchaService;
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
        $result = $this->captchaService->getCaptcha();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('siteKey', $result);
        $this->assertArrayHasKey('captchaEndpoint', $result);
        $this->assertArrayHasKey('puzzle', $result);
        $this->assertArrayHasKey('captchaEnabled', $result);
    }

    public function testGetCaptchaReturnsExpectedValues(): void
    {
        // Arrange
        $expectedSiteKey = \App::$FRIENDLY_CAPTCHA_SITE_KEY;
        $expectedEndpoint = \App::$FRIENDLY_CAPTCHA_ENDPOINT;
        $expectedPuzzle = \App::$FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE;
        $expectedEnabled = \App::$CAPTCHA_ENABLED;

        // Act
        $result = $this->captchaService->getCaptcha();

        // Assert
        $this->assertEquals($expectedSiteKey, $result['siteKey']);
        $this->assertEquals($expectedEndpoint, $result['captchaEndpoint']);
        $this->assertEquals($expectedPuzzle, $result['puzzle']);
        $this->assertEquals($expectedEnabled, $result['captchaEnabled']);
    }
}