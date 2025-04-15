<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Availability;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Availability\AvailableDaysListService;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableDaysListController extends BaseController
{
    private AvailableDaysListService $service;
    private TokenValidationService $tokenValidator;

    public function __construct()
    {
        $this->service = new AvailableDaysListService();
        $this->tokenValidator = new TokenValidationService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }
        error_log('QUERY-PARAMS: ' . print_r($request->getQueryParams(), true));
        $queryParams = $request->getQueryParams();
        $officeId = (int)($queryParams['officeId'] ?? 0);

        $thinnedScope = ZmsApiFacadeService::getScopeByOfficeId($officeId);
        $captchaActivated = $thinnedScope->captchaActivatedRequired;
        error_log('CAPTCHA ACTIVATED? ' . print_r($captchaActivated, true));

        if ($captchaActivated) {
            $token = $queryParams['captchaToken'] ?? null;

            if (!$this->tokenValidator->isCaptchaTokenValid($token)) {
                return $this->createJsonResponse($response, [
                    'meta' => ['success' => false, 'error' => 'Ungültiges oder fehlendes Captcha-Token'],
                    'data' => null,
                ], 403);
            }
        }

        $result = $this->service->getAvailableDaysList($request->getQueryParams());
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
