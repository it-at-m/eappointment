<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\CaptchaInterface;
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
     * Return the captcha configuration details.
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
     * Generate a JWT for captcha validation.
     *
     * @return string
     */
    public function generateToken(): string
    {
        $payload = [
            'ip' => ClientIpHelper::getClientIp(),
            'iat' => time(),
            'exp' => time() + $this->tokenExpirationSeconds,
        ];

        return JWT::encode($payload, $this->tokenSecret, 'HS256');
    }

    /**
     * Request a new captcha challenge.
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
                throw new \Exception('Error decoding the JSON response');
            }

            $challenge = $responseData['challenge'] ?? null;

            if ($challenge === null) {
                throw new \Exception('Missing challenge data');
            }

            return $challenge;
        } catch (RequestException $e) {
            return [
                'meta' => ['success' => false, 'error' => 'Request error: ' . $e->getMessage()],
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
     * Verify the captcha solution.
     *
     * @param string $payload
     * @return mixed
     * @throws \Exception
     */
    public function verifySolution(?string $payload): array
    {
        if (!$payload) {
            return [
                'meta' => ['success' => false, 'error' => 'No payload provided'],
                'data' => null,
            ];
        }

        $decodedJson = base64_decode(strtr($payload, '-_', '+/'));
        if (!$decodedJson) {
            return [
                'meta' => ['success' => false, 'error' => 'Payload could not be decoded'],
                'data' => null,
            ];
        }

        $decodedPayload = json_decode($decodedJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedPayload)) {
            return [
                'meta' => ['success' => false, 'error' => 'Invalid JSON in payload'],
                'data' => null,
            ];
        }

        try {
            $response = $this->httpClient->post($this->verifyUrl, [
                'json' => [
                    'siteKey' => $this->siteKey,
                    'siteSecret' => $this->siteSecret,
                    'clientAddress' => ClientIpHelper::getClientIp(),
                    'payload' => $decodedPayload,
                ]
            ]);

            $responseData = json_decode((string) $response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($responseData)) {
                throw new \Exception('Response from Captcha service is not valid JSON');
            }

            if (!array_key_exists('valid', $responseData)) {
                return [
                    'meta' => ['success' => false, 'error' => 'Response does not contain a "valid" field'],
                    'data' => $responseData,
                ];
            }

            if ($responseData['valid'] !== true) {
                return [
                    'meta' => ['success' => false, 'error' => 'Captcha verification failed'],
                    'data' => $responseData,
                ];
            }

            return [
                'meta' => ['success' => true],
                'data' => $responseData,
                'token' => $this->generateToken(),
            ];
        } catch (RequestException $e) {
            return [
                'meta' => ['success' => false, 'error' => 'Request error: ' . $e->getMessage()],
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
