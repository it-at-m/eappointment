<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class TokenValidationService
{
    private string $captchaTokenSecret;

    public function __construct()
    {
        $this->captchaTokenSecret = \App::$CAPTCHA_TOKEN_SECRET;
    }

    public function isCaptchaTokenValid(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $payload = (array) JWT::decode($token, new Key($this->captchaTokenSecret, 'HS256'));

            if (empty($payload['ip']) || $payload['ip'] !== ClientIpHelper::getClientIp()) {
                return false; // IP stimmt nicht überein
            }

            return true;
        } catch (ExpiredException $e) {
            // abgelaufen
            return false;
        } catch (\Exception $e) {
            // Invalid token, invalid signature, etc.
            return false;
        }
    }
}
