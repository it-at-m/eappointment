<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Models\Captcha\FriendlyCaptcha;

class Captcha extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $captcha = new FriendlyCaptcha();
            $captchaDetails = $captcha->getCaptchaDetails();

            return $this->createJsonResponse($response, $captchaDetails, statusCode: 200);
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['error' => 'Captcha verification failed', 'message' => $e->getMessage()],
                statusCode: 500
            );
        }
    }
}
