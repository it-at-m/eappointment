<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmsentities\Schema\Entity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AltchaCaptcha extends Entity implements CaptchaInterface
{
    public static $schema = "citizenapi/captcha/altchaCaptcha.json";
/** @var string */
    public string $service;
/** @var string */
    private string $siteKey;
/** @var string */
    private string $siteSecret;
/** @var string */
    private string $tokenSecret;
/** @var string */
    public string $challengeUrl;
/** @var string */
    public string $verifyUrl;
/** @var Client */
    protected Client $httpClient;
/**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = 'AltchaCaptcha';
        $this->siteKey = \App::$ALTCHA_CAPTCHA_SITE_KEY;
        $this->siteSecret = \App::$ALTCHA_CAPTCHA_SITE_SECRET;
        $this->tokenSecret = \App::$CAPTCHA_TOKEN_SECRET;
        $this->challengeUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE;
        $this->verifyUrl = \App::$ALTCHA_CAPTCHA_ENDPOINT_VERIFY;
        $this->httpClient = new Client(['verify' => false]);
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

    private function generateToken(): string
    {
        $payload = [
            'ip' => ClientIpHelper::getClientIp(),
            'iat' => time(),
            'exp' => time() + 300, // 5 Minuten gültig
        ];

        $json = json_encode($payload);
        $base64Payload = base64_encode($json);
        $signature = hash_hmac('sha256', $base64Payload, $this->tokenSecret, true);
        $base64Signature = base64_encode($signature);

        $token = $base64Payload . '.' . $base64Signature;
        // error_log('TOKEN: ' . print_r($token, true));

        return $token;
    }

    /**
     * Ruft den externen Altcha-Challenge-Endpunkt auf.
     *
     * @return array
     */
    public function createChallenge(): array
    {
        try {
            $response = $this->httpClient->post($this->challengeUrl, [
                'json' => [
                    'siteKey' => $this->siteKey,
                    'siteSecret' => $this->siteSecret,
                    'clientAddress' => ClientIpHelper::getClientIp(),
                ]
            ]);

            $responseData = json_decode((string) $response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Fehler beim Dekodieren der JSON-Antwort');
            }

            $challenge = $responseData['challenge'] ?? null;

            if ($challenge === null) {
                throw new \Exception('Challenge-Daten fehlen in der Antwort');
            }

            return $challenge;
        } catch (RequestException $e) {
            return [
                'meta' => ['success' => false, 'error' => 'Request-Fehler: ' . $e->getMessage()],
                'data' => null,
            ];
        } catch (\Throwable $e) {
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
        if (!$decodedJson) {
            return [
                'meta' => ['success' => false, 'error' => 'Payload konnte nicht dekodiert werden'],
                'data' => null,
            ];
        }

        $decodedPayload = json_decode($decodedJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'meta' => ['success' => false, 'error' => 'Ungültiges JSON in Payload'],
                'data' => null,
            ];
        }

        try {
            $response = $this->httpClient->post($this->verifyUrl, [
                'json' => [
                    'siteKey' => $this->siteKey,
                    'siteSecret' => $this->siteSecret,
                    'payload' => $decodedPayload,
                ]
            ]);

            $responseData = json_decode((string) $response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Antwort vom Captcha-Service ist kein gültiges JSON');
            }

            return [
                'meta' => ['success' => true],
                'data' => $responseData,
                'token' => $this->generateToken(),
            ];
        } catch (RequestException $e) {
            return [
                'meta' => ['success' => false, 'error' => 'Request-Fehler: ' . $e->getMessage()],
                'data' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'meta' => ['success' => false, 'error' => $e->getMessage()],
                'data' => null,
            ];
        }
    }
}
