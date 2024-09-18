<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Application;

class CaptchaService
{
    public function getCaptchaDetails()
    {
        return [
            'siteKey' => Application::$FRIENDLYCAPTCHA_SITEKEY,
            'captchaEndpoint' => Application::$FRIENDLYCAPTCHA_ENDPOINT,
            'puzzle' => Application::$FRIENDLYCAPTCHA_ENDPOINT_PUZZLE,
            'captchaEnabled' => Application::$CAPTCHA_ENABLED
        ];
    }
}
