<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\FriendlyCaptchaService;

class CaptchaGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $captchaDetails = FriendlyCaptchaService::getCaptchaDetails();

        return $this->createJsonResponse($response, $captchaDetails, statusCode: $captchaDetails['status']);
    }
}
