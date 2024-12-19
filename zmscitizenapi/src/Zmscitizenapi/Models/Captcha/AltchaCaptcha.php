<?php

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AltchaCaptcha extends Entity implements CaptchaInterface
{
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
    public function __construct(Client $httpClient = null)
    {
        $this->service = 'AltchaCaptcha';
        $this->siteKey = Application::$ALTCHA_CAPTCHA_SITE_KEY;
        $this->apiUrl = Application::$ALTCHA_CAPTCHA_ENDPOINT;
        $this->secretKey = Application::$ALTCHA_CAPTCHA_SECRET_KEY;
        $this->puzzle = Application::$ALTCHA_CAPTCHA_ENDPOINT_PUZZLE;
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
            'captchaEnabled' => Application::$CAPTCHA_ENABLED,
            'status' => 200
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
            $response = $this->httpClient->post($this->apiUrl, [
                'form_params' => [
                    'secret' => $this->secretKey,
                    'solution' => $solution
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($responseBody['valid'])) {
                return false;
            }

            return $responseBody['valid'] === true;
        } catch (RequestException $e) {
            return false;
        }
    }
}