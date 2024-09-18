<?php

namespace BO\Zmscitizenapi\Services;

use GuzzleHttp\Exception\RequestException;
use BO\Zmscitizenapi\Application;
use Exception;

class CaptchaService
{
    public function getCaptchaDetails()
    {
        return [
            'siteKey' => Application::$FRIENDLYCAPTCHA_SITEKEY,
            'captchaEndpoint' => Application::$FRIENDLYCAPTCHA_ENDPOINT,
            'puzzle' => Application::$FRIENDLYCAPTCHA_ENDPOINT_PUZZLE,
            'captchaEnabled' => Application::$CAPTCHA_ENABLED
        ];
    }

    public function verifyCaptcha($solution)
    {
        try {
            $response = $this->httpClient->post(Application::$FRIENDLYCAPTCHA_ENDPOINT, [
                'form_params' => [
                    'secret' => Application::$FRIENDLYCAPTCHA_SECRET,
                    'solution' => $solution
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);

            return $responseBody;
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            error_log('Error verifying captcha: ' . $errorMessage);
            throw new Exception('Captcha verification failed');
        }
    }
}
