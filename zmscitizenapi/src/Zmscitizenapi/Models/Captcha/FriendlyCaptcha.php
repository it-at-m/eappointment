<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;
use GuzzleHttp\Exception\RequestException;

class FriendlyCaptcha extends Entity implements CaptchaInterface
{
    public static $schema = "citizenapi/captcha/friendlyCaptcha.json";
/** @var string */
    public string $service;
/** @var string */
    public string $siteKey;
/** @var string */
    public string $apiUrl;
/** @var string */
    public string $secretKey;
/** @var string */
    public string $puzzle;
/**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = 'FriendlyCaptcha';
        $this->siteKey = \App::$FRIENDLY_CAPTCHA_SITE_KEY;
        $this->apiUrl = \App::$FRIENDLY_CAPTCHA_ENDPOINT;
        $this->secretKey = \App::$FRIENDLY_CAPTCHA_SECRET_KEY;
        $this->puzzle = \App::$FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE;
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
            'captchaEndpoint' => $this->apiUrl,
            'puzzle' => $this->puzzle,
            'captchaEnabled' => \App::$CAPTCHA_ENABLED
        ];
    }

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $solution
     * @return bool
     * @throws \Exception
     */
    public function verifyCaptcha(string $solution): bool
    {
        try {
            $response = \App::$http->post($this->apiUrl, [
                'form_params' => [
                    'secret' => $this->secretKey,
                    'solution' => $solution
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
