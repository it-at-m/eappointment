<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // Retrieve captcha details from the Application class
        $captchaDetails = [
            'siteKey' => Application::$FRIENDLYCAPTCHA_SITEKEY,
            'captchaEndpoint' => Application::$FRIENDLYCAPTCHA_ENDPOINT,
            'puzzle' => Application::$FRIENDLYCAPTCHA_ENDPOINT_PUZZLE,
            'captchaEnabled' => Application::$CAPTCHA_ENABLED
        ];

        // Return the captcha details as JSON
        return Render::withJson($response, $captchaDetails);
    }
}
