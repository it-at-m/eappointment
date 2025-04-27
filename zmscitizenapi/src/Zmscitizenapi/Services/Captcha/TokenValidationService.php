<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;

class TokenValidationService
{
    private string $captchaTokenSecret;

    public function __construct()
    {
        $this->captchaTokenSecret = \App::$CAPTCHA_TOKEN_SECRET;
    }

    public function isCaptchaTokenValid(?string $token): bool
    {
        if (!$token || !str_contains($token, '.')) {
            return false;
        }

        [$base64Payload, $base64Signature] = explode('.', $token, 2);

        $decoded = base64_decode($base64Payload, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);
        if (!is_array($payload)) {
            return false;
        }

        $expectedSig = base64_encode(hash_hmac('sha256', $base64Payload, $this->captchaTokenSecret, true));
        if (!hash_equals($expectedSig, $base64Signature)) {
            return false;
        }

        if (empty($payload['exp']) || time() > $payload['exp']) {
            return false; // abgelaufen
        }

        if (empty($payload['ip']) || $payload['ip'] !== ClientIpHelper::getClientIp()) {
            return false; // IP stimmt nicht überein
        }

        return true;
    }
}
