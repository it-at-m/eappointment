<?php

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;

class AltchaCaptcha extends Entity implements CaptchaInterface
{
    /** @var string */
    public string $service;

    /** @var string */
    public string $apiKey;

    /** @var string */
    public string $verificationUrl;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = 'AltchaCaptcha';
        $this->apiKey = getenv('ALTCHA_API_KEY');
        $this->verificationUrl = getenv('ALTCHA_VERIFICATION_URL');
    }

    /**
     * Gibt die Captcha-Konfigurationsdetails zurück.
     *
     * @return array
     */
    public static function getCaptchaDetails(): array
    {
        return [
            'service' => 'AltchaCaptcha',
            'details' => [
                'apiKey' => getenv('ALTCHA_API_KEY'),
                'verificationUrl' => getenv('ALTCHA_VERIFICATION_URL'),
            ],
        ];
    }

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $solution
     * @return bool
     * @throws \Exception
     */
    public static function verifyCaptcha(string $solution): bool
    {
        $verificationUrl = getenv('ALTCHA_VERIFICATION_URL');
        $apiKey = getenv('ALTCHA_API_KEY');

        if (!$verificationUrl || !$apiKey) {
            throw new \Exception("Captcha configuration is incomplete.");
        }

        $response = file_get_contents($verificationUrl . "?solution=" . urlencode($solution) . "&apiKey=" . urlencode($apiKey));
        $responseData = json_decode($response, true);

        if (isset($responseData['valid']) && $responseData['valid']) {
            return true;
        }

        return false;
    }
}