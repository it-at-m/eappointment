<?php

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;

class FriendlyCaptcha extends Entity implements CaptchaInterface
{
    /** @var string */
    public string $service;

    /** @var string */
    public string $siteKey;

    /** @var string */
    public string $apiUrl;

    /** @var string */
    public string $secretKey;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = 'FriendlyCaptcha';
        $this->siteKey = getenv('FRIENDLY_CAPTCHA_SITE_KEY');
        $this->apiUrl = getenv('FRIENDLY_CAPTCHA_API_URL');
        $this->secretKey = getenv('FRIENDLY_CAPTCHA_SECRET_KEY');
    }

    /**
     * Gibt die Captcha-Konfigurationsdetails zurück.
     *
     * @return array
     */
    public static function getCaptchaDetails(): array
    {
        return [
            'service' => 'FriendlyCaptcha',
            'details' => [
                'siteKey' => getenv('FRIENDLY_CAPTCHA_SITE_KEY'),
                'apiUrl' => getenv('FRIENDLY_CAPTCHA_API_URL'),
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
        $apiUrl = getenv('FRIENDLY_CAPTCHA_API_URL');
        $secretKey = getenv('FRIENDLY_CAPTCHA_SECRET_KEY');

        if (!$apiUrl || !$secretKey) {
            throw new \Exception("Captcha configuration is incomplete.");
        }

        $response = file_get_contents($apiUrl . "?solution=" . urlencode($solution) . "&secret=" . urlencode($secretKey));
        $responseData = json_decode($response, true);

        if (isset($responseData['success']) && $responseData['success']) {
            return true;
        }

        return false;
    }
}