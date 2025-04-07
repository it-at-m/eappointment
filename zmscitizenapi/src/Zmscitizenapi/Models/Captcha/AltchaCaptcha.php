<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
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

    /**
     * Ruft den externen Altcha-Challenge-Endpunkt auf.
     *
     * @return array
     */
    public function createChallenge(): array
    {
        $url = $this->challengeUrl;
        $data = [
            'siteKey' => $this->siteKey,
            'siteSecret' => $this->siteSecret,
            'clientAddress' => ClientIpHelper::getClientIp(),
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => json_encode($data),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];

        $context = stream_context_create($options);

        try {
            $result = file_get_contents($url, false, $context);

            if ($result === false) {
                throw new Exception('Anfrage fehlgeschlagen');
            }

            $responseData = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Fehler beim Dekodieren der JSON-Antwort');
            }

            $challenge = $responseData['challenge'] ?? null;

            if ($challenge === null) {
                throw new Exception('Challenge-Daten fehlen in der Antwort');
            }

            return $challenge;
        } catch (Exception $e) {
            return [
                'meta' => ['success' => false, 'error' => $e->getMessage()],
                'data' => null,
            ];
        }
    }

    /**
     * Führt die Verifikation der Challenge-Lösung durch.
     *
     * @param string $payload
     * @return array
     */
    public function verifySolution(?string $payload): array
    {
        if (!$payload) {
            return [
                'meta' => ['success' => false, 'error' => 'Keine Payload übergeben'],
                'data' => null,
            ];
        }

        $decodedJson = base64_decode(strtr($payload, '-_', '+/'));
        error_log('DECODED JSON: ' . print_r($decodedJson, true));

        if (!$decodedJson) {
            return [
                'meta' => ['success' => false, 'error' => 'Payload konnte nicht dekodiert werden'],
                'data' => null,
            ];
        }

        $decodedPayload = json_decode($decodedJson, true);
        error_log('DECODED PAYLOAD: ' . print_r($decodedPayload, true));

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'meta' => ['success' => false, 'error' => 'Ungültiges JSON in Payload'],
                'data' => null,
            ];
        }

        $requestBody = [
            'siteKey' => $this->siteKey,
            'siteSecret' => $this->siteSecret,
            'payload' => $decodedPayload,
        ];
        error_log('REQUEST BODY: ' . print_r($requestBody, true));

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => json_encode($requestBody),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];

        $context = stream_context_create($options);

        try {
            $url = $this->verifyUrl;
            $result = file_get_contents($url, false, $context);

            if ($result === false) {
                throw new Exception('Anfrage an den Captcha-Service fehlgeschlagen');
            }

            error_log('RESULT: ' . print_r($result, true));

            $responseData = json_decode($result, true);
            error_log('RESPONSE DATA: ' . print_r($responseData, true));

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Antwort vom Captcha-Service ist kein gültiges JSON');
            }

            return [
                'meta' => ['success' => true],
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            return [
                'meta' => ['success' => false, 'error' => $e->getMessage()],
                'data' => null,
            ];
        }
    }
}
