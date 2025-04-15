<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Captcha;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Captcha\CaptchaService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaVerifyController extends BaseController
{
    private CaptchaService $service;
    public function __construct()
    {
        $this->service = new CaptchaService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest', $this->language)['statusCode']
            );
        }

        $data = $request->getParsedBody();
        $payload = $data['payload'] ?? null;
        error_log('PAYLOAD: ' . print_r($payload, true));
        $result = $this->service->verifySolution($payload);
        error_log('RESULT: ' . print_r($result, true));
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result, 200);
    }
}
