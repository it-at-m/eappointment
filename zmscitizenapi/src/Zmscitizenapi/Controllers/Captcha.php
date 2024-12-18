<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\FriendlyCaptchaService;

class Captcha extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $captchaDetails = FriendlyCaptchaService::getCaptchaDetails();
            
            // Validate captcha details structure
            if (!isset($captchaDetails['status']) || !is_int($captchaDetails['status'])) {
                throw new \RuntimeException('Invalid captcha response structure');
            }
            
            // Ensure status code is within valid HTTP range
            $statusCode = max(min($captchaDetails['status'], 599), 100);

            return $this->createJsonResponse($response, $captchaDetails, statusCode: $statusCode);
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['error' => 'Captcha verification failed', 'message' => $e->getMessage()],
                statusCode: 500
            );
        }
    }
}
