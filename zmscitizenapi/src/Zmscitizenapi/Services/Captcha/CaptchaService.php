<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\CaptchaInterface;
use BO\Zmscitizenapi\Services\Captcha\CaptchaService;
use BO\Zmsentities\Schema\Entity;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CaptchaService extends Entity implements CaptchaInterface
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
/** @var int */
    private int $tokenExpirationSeconds;
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
        $this->service = 'CaptchaService';
        $this->siteKey = \App::$ALTCHA_CAPTCHA_SITE_KEY;
        $this->siteSecret = \App::$ALTCHA_CAPTCHA_SITE_SECRET;
        $this->tokenSecret = \App::$CAPTCHA_TOKEN_SECRET;
        $this->tokenExpirationSeconds = \App::$CAPTCHA_TOKEN_TTL;
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

    /**
     * Generiert einen JWT für die Captcha-Validierung. 
     *
     * @return string
     */
    public function generateToken(): string
    {
        if ($this->tokenSecret === '') {
            throw new \RuntimeException('CAPTCHA_TOKEN_SECRET must be configured');
        }

        $payload = [
            'ip' => ClientIpHelper::getClientIp(),
            'iat' => time(),
            'exp' => time() + $this->tokenExpirationSeconds,
        ];

        return JWT::encode($payload, $this->tokenSecret, 'HS256');
    }

    /**
     * Fordert eine neue Captcha-Challenge an.
     *
     * @return array
     * @throws \Exception
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
     * Überprüft die Captcha-Lösung.
     *
     * @param string $payload
     * @return mixed
     * @throws \Exception
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
