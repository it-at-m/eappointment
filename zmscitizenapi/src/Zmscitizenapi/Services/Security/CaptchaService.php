<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Security;

use BO\Zmscitizenapi\Models\Captcha\FriendlyCaptcha;

class CaptchaService
{
    public function getCaptcha(): array
    {
        return $this->getCaptchaDetails()->getCaptchaDetails();
    }

    private function getCaptchaDetails(): FriendlyCaptcha
    {
        return new FriendlyCaptcha();
    }
}
