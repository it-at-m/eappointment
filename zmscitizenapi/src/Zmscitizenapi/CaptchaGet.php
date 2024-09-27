<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\CaptchaService;

class CaptchaGet extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $captchaDetails = CaptchaService::getCaptchaDetails();

        return Render::withJson($response, $captchaDetails);
    }
}
