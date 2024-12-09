<?php

namespace BO\Zmscitizenapi\Services;

use GuzzleHttp\Exception\RequestException;
use \BO\Zmscitizenapi\Application;
use Exception;

class FriendlyCaptchaService implements CaptchaServiceInterface
{
    public static function getCaptchaDetails(): array
    {
        return [
            'siteKey' => Application::$CAPTCHA_SITEKEY,
            'captchaEndpoint' => Application::$CAPTCHA_ENDPOINT,
            'puzzle' => Application::$CAPTCHA_ENDPOINT_PUZZLE,
            'captchaEnabled' => Application::$CAPTCHA_ENABLED,
            'status' => 200
        ];
    }

    public static function verifyCaptcha(string $solution): bool
    {
        try {
            $response = \App::$http->post(Application::$CAPTCHA_ENDPOINT, [
                'form_params' => [
                    'secret' => Application::$CAPTCHA_SECRET,
                    'solution' => $solution
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);

            return $responseBody;
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new Exception('Captcha verification failed: ' . $errorMessage);
        }
    }
}
