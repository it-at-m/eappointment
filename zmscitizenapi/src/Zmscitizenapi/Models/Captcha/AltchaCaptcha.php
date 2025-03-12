<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;
use GuzzleHttp\Exception\RequestException;

class AltchaCaptcha extends Entity implements CaptchaInterface
{
    public static $schema = "citizenapi/captcha/altchaCaptcha.json";
/** @var string */
    public string $service;
/** @var string */
    public string $siteKey;
/** @var string */
    public string $siteSecret;
/** @var string */
    public string $challengeUrl;
/** @var string */
    public string $verifyUrl;
/**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = 'AltchaCaptcha';
        $this->siteKey = \App::$ALTCHA_CAPTCHA_SITE_KEY;
        $this->siteSecret = \App::$ALTCHA_CAPTCHA_SITE_SECRET;
        $this->challengeUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE;
        $this->verifyUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_VERIFY;
        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new \InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    /**
     * Gibt die Captcha-Konfigurationsdetails zurück.
     *
     * @return array
     */
    public function getCaptchaDetails(): array
    {
        return [
            'siteKey' => $this->siteKey,
            'captchaChallenge' => $this->challengeUrl,
            'captchaVerify' => $this->verifyUrl,
            'captchaEnabled' => \App::$CAPTCHA_ENABLED
        ];
    }

    // /**
    //  * Fordert ein neues Captcha an.
    //  *
    //  * @param string $clientIp
    //  * @return array|null
    //  */
    // public function requestCaptcha(string $clientIp): ?array
    // {
    //     try {
    //         $response = \App::$http->post($this->challengeUrl, [
    //             'json' => ['clientIpAddress' => $clientIp]
    //         ]);

    //         $responseBody = json_decode((string)$response->getBody(), true);
    //         if (json_last_error() !== JSON_ERROR_NONE || empty($responseBody)) {
    //             return null;
    //         }

    //         return $responseBody;
    //     } catch (RequestException $e) {
    //         return null;
    //     }
    // }

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $payload
     * @return bool
     */
    public function verifyCaptcha(string $payload): bool
    {
        try {
            $response = \App::$http->post($this->verifyUrl, [
                'json' => [
                    'siteKey' => $this->siteKey,
                    'siteSecret' => $this->siteSecret,
                    'payload' => $payload
                ]
            ]);

            $responseBody = json_decode((string)$response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($responseBody['success'])) {
                return false;
            }

            return $responseBody['success'] === true;
        } catch (RequestException $e) {
            return false;
        }
    }
}
