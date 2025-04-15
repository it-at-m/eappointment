<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmscitizenapi\Models\Captcha\AltchaCaptcha;

class CaptchaService
{
    private function getCaptcha(): CaptchaInterface
    {
        return new AltchaCaptcha();
    }

    public function getCaptchaDetails(): array
    {
        return $this->getCaptcha()->getCaptchaDetails();
    }

    public function createChallenge(): array
    {
        return $this->getCaptcha()->createChallenge();
    }

    public function verifySolution(string $payload): array
    {
        return $this->getCaptcha()->verifySolution($payload);
    }
}
