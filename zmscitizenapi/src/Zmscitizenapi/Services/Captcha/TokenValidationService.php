<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Utils\ClientIpHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class TokenValidationService
{
    public const TOKEN_VALID = 'valid';
    public const TOKEN_MISSING = 'missing';
    public const TOKEN_INVALID = 'invalid';
    public const TOKEN_EXPIRED = 'expired';
    private string $captchaTokenSecret;

    public function __construct()
    {
        $this->captchaTokenSecret = \App::$CAPTCHA_TOKEN_SECRET;
    }

    public function validateCaptchaToken(?string $token): string
    {
        if (empty($token)) {
            return self::TOKEN_MISSING;
        }

        try {
            $payload = (array) JWT::decode($token, new Key($this->captchaTokenSecret, 'HS256'));
            if (empty($payload['ip']) || $payload['ip'] !== ClientIpHelper::getClientIp()) {
                return self::TOKEN_INVALID;
            }

            return self::TOKEN_VALID;
        } catch (ExpiredException $e) {
            return self::TOKEN_EXPIRED;
        } catch (\Exception $e) {
            return self::TOKEN_INVALID;
        }
    }
}
