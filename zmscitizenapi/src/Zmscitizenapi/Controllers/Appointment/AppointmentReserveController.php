<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Appointment\AppointmentReserveService;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentReserveController extends BaseController
{
    private AppointmentReserveService $service;
    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->service = new AppointmentReserveService();
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $parsedBody = $request->getParsedBody();
        error_log('PARSED BODY: ' . print_r($parsedBody, true));
        $officeId   = isset($parsedBody['officeId']) ? (int)$parsedBody['officeId'] : 0;

        try {
            $thinnedScope = $this->zmsApiFacadeService->getScopeByOfficeId($officeId);
        } catch (\Throwable $e) {
            error_log('Scope not found for officeId: ' . $officeId);
            $thinnedScope = null;
        }

        $captchaActivated = $thinnedScope->captchaActivatedRequired ?? false;

        if ($captchaActivated) {
            $token = $parsedBody['captchaToken'] ?? null;

            if (!$this->tokenValidator->isCaptchaTokenValid($token)) {
                return $this->createJsonResponse($response, [
                    'meta' => ['success' => false, 'error'   => 'Ungültiges oder fehlendes Captcha-Token'],
                    'data' => null,
                ], 403);
            }
        }

        $result = $this->service->processReservation($request->getParsedBody());
        if (is_array($result) && isset($result['errors'])) {
            foreach ($result['errors'] as &$error) {
                if (isset($error['errorCode'])) {
                    $error = ErrorMessages::get($error['errorCode'], $this->language);
                }
            }
            return $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']));
        }

        return $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
