<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Security;

use BO\Zmscitizenapi\Models\Captcha\AltchaCaptcha;

class CaptchaService
{
    public function getCaptcha(): array
    {
        return $this->getCaptchaDetails()->getCaptchaDetails();
    }

    private function getCaptchaDetails(): AltchaCaptcha
    {
        return new AltchaCaptcha();
    }
}
