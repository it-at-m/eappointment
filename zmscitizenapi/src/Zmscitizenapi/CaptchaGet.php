<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\CaptchaService;

class CaptchaGet extends BaseController
{
    protected $captchaService;

    public function __construct()
    {
        $this->captchaService = new CaptchaService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $captchaDetails = $this->captchaService->getCaptchaDetails();

        return Render::withJson($response, $captchaDetails);
    }
}
