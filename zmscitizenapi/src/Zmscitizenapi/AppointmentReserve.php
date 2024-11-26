<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Helper\UtilityHelper;
use BO\Zmscitizenapi\Services\CaptchaService;
use BO\Zmscitizenapi\Services\MapperService;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentReserve extends BaseController
{


    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;

        $body = $request->getParsedBody();

        $officeId = $body['officeId'] ?? null;
        $serviceIds = $body['serviceId'] ?? [];
        $serviceCounts = $body['serviceCount'] ?? [1];
        $captchaSolution = $body['captchaSolution'] ?? null;
        $timestamp = $body['timestamp'] ?? null;


        $errors = ValidationService::validatePostAppointmentReserve($officeId, $serviceIds, $serviceCounts, $captchaSolution, $timestamp);
        if (!empty($errors['errors'])) {
            return $this->createJsonResponse($response, 
            $errors, 400);
        }

        try {
            $providerScope = ZmsApiFacadeService::getScopeByOfficeId($officeId);
            $captchaRequired = Application::$CAPTCHA_ENABLED === true && $providerScope['captchaActivatedRequired'] === "1";

            if ($captchaRequired) {
                $captchaVerificationResult = CaptchaService::verifyCaptcha($captchaSolution);
                if (!$captchaVerificationResult['success']) {
                    return $this->createJsonResponse($response, [
                        'errorCode' => 'captchaVerificationFailed',
                        'errorMessage' => 'Captcha verification failed'
                    ], 400);
                }
            }

            $serviceValidationResult = ValidationService::validateServiceLocationCombination($officeId, $serviceIds);
            if ($serviceValidationResult['status'] !== 200) {
                return $this->createJsonResponse($response, $serviceValidationResult, 400);
            }

            $freeAppointments = ZmsApiFacadeService::getFreeAppointments([
                'officeId' => $officeId,
                'serviceIds' => $serviceIds,
                'serviceCounts' => $serviceCounts,
                'date' => UtilityHelper::getInternalDateFromTimestamp($timestamp)
            ]);

            $selectedProcess = array_filter($freeAppointments, function ($process) use ($timestamp) {
                if (!isset($process['appointments']) || !is_array($process['appointments'])) {
                    return false;
                }
                return in_array($timestamp, array_column($process['appointments'], 'date'));
            });

            $errors = ValidationService::validateGetProcessNotFound($selectedProcess);
            if (!empty($errors['errors'])) {
                return $this->createJsonResponse($response, 
                $errors, 404);
            }

            $selectedProcess = array_values($selectedProcess)[0];
            $selectedProcess['clients'] = [
                [
                    'email' => 'default@example.com'
                ]
            ];

            $reservedProcess = ZmsApiFacadeService::reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);

            if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
                $scopeIds = [$reservedProcess->scope->id];
                $scopesData = ZmsApiFacadeService::getScopeByIds($scopeIds);

                if ($scopesData['status'] === 200 && isset($scopesData['scopes']['scopes']) && !empty($scopesData['scopes']['scopes'])) {
                    $reservedProcess->scope = MapperService::mapScope($scopesData['scopes']['scopes'][0]);
                }
            }

            $thinnedProcessData = UtilityHelper::getThinnedProcessData($reservedProcess);
            $thinnedProcessData = array_merge($thinnedProcessData, ['officeId' => $officeId]);

            return $this->createJsonResponse($response, $thinnedProcessData, 200);

        } catch (\Exception $e) {
            throw $e;
        }
    }

}
