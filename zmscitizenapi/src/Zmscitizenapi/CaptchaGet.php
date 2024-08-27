<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // Retrieve captcha details from environment variables
        $captchaDetails = [
            'siteKey' => getenv('FRIENDLYCAPTCHA_SITEKEY'),
            'captchaEndpoint' => getenv('FRIENDLYCAPTCHA_ENDPOINT'),
            'puzzle' => getenv('FRIENDLYCAPTCHA_ENDPOINT_PUZZLE'),
            'captchaEnabled' => getenv('CAPTCHA_ENABLED') === "1"
        ];

        // Return the captcha details as JSON
        return Render::withJson($response, $captchaDetails);
    }
}
