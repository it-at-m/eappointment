<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Captcha\Controller;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Captcha\Service\CaptchaService;
use BO\Zmscitizenbackend\Core\Service\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaController extends BaseController
{
    private CaptchaService $service;
    /** @psalm-api */
    public function __construct()
    {
        $this->service = new CaptchaService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $result = $this->service->getCaptchaDetails();
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result, 200);
    }
}
